<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    /**
     * Define a number of global defines in the system. This helps
     * by preventing any mistyped strings
     */
    protected function _initDefines()
    {
	define('SYNRGIC_SESSION','Synrgic');
	define('SYNRGIC_LOCALE_DEFAULT', 'en_US');
	define('DEVICE_PERSIST_COOKIE','SynrgicHCS'); // XXX This should be configurable per hotel
	define('DOCTRINE_LIB_PATH', APPLICATION_PATH.'/../library/Doctrine/lib');
	define('DOCTRINE_COMMON_LIB_PATH', DOCTRINE_LIB_PATH.'/vendor/doctrine-common/lib');
	define('DOCTRINE_DBAL_LIB_PATH', DOCTRINE_LIB_PATH.'/vendor/doctrine-dbal/lib');

    }

    /**
     * Global session used by the application
     */
    protected function _initSession()
    {
    	//ensure doctrine tool never trapped here
    	if(!defined('IGNORE_INI_SESSION') || IGNORE_INI_SESSION !== 'yes') 
    	{
    		$this->bootstrap('doctrine');
    		//XXXX dtliu, Zend_Session_SaveHandler_DbTable not work with objects
    		//to test can open this comment, then observe database table: session
    		 
	    	//get doctrine configuration
	    	$options = $this->getOption('doctrine');
	    	$adapter = $options['driver'];
	    	$config = array(
	    			'username'  => $options['user'],
	    			'password'  => $options['password'],
	    			'dbname'    => $options['dbname'],
	    			'host'      => $options['host'],
	    		);
	    	$db = Zend_Db::factory($adapter, $config);
	    	Zend_Db_Table_Abstract::setDefaultAdapter($db);
	    	$config = array(
	    			'name' => 'session',
	    			'primary' => array(
	    					'session_id',
	    					'save_path',
	    					'name',
	    				),
	    			'primaryAssignment' => array (
	    					'sessionId',
	    					'sessionSavePath',
	    					'sessionName',
	    				),
	    			'modifiedColumn' => 'modified',
	    			'lifetimeColumn' => 'lifetime',
	    			'dataColumn' => 'session_data',
	    		);
	    	Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));

    		// Guarrenty the session has been started
	    	Zend_Session::start();
	    	$session = new Zend_Session_Namespace(SYNRGIC_SESSION);
	    	Zend_Registry::set(SYNRGIC_SESSION, $session);
	    }
    }

    /**
     * Create the initial translation object. The actual setting of a language
     * takes place in the LanguageInit Plugin. We initialised the default
     * locale here just incase.
     */
    protected function _initTranslations()
    {
        $this->bootstrap('settings');
       
        // Try and use the locale as defined with the browser, if not
    	// fall back to the default locale
    	try {
    	    $locale = new Zend_Locale(Zend_Locale::BROWSER);
    	} catch (Zend_Locale_Exception $exception) {
    	    try {
    	        $settings = Zend_Registry::get('settings');
    	        $locale = new Zend_Locale($settings->General->Language->Value);
    	    }
    	    catch(Exception $e) {
    	        $locale = new Zend_Locale(SYNRGIC_LOCALE_DEFAULT);
    	    }
    	}
    	
        // Default Locale search order: 
        // 1. system settings 
    	// 2. en_US 
    	// 
    	// The Language Plugin may override this based on user/session settings
    	try {
    	    $settings = Zend_Registry::get('settings');
    	    $locale = new Zend_Locale($settings->General->Language->Value);
    	} catch(Exception $e) {
    		$locale = new Zend_Locale("en_US");
    	}
    
        Zend_Registry::set('Zend_Locale', $locale );
        
    	$options = $this->getOption('translate');
    	$options['lang'] = $locale->getLanguage();
        $synrgicTranslate = Synrgic_Models_Translate::getInstance($options);
        Zend_Registry::set('Synrgic_Translate', $synrgicTranslate);
        
        //Zend_Translate set is postponed to Language plugin according to the final locale
        //Zend_Registry::set('Zend_Translate',$synrgicTranslate->getTranslate());
    }

    /**
     * Authentication defaults and the Access control list system for the application
     */
    protected function _initAuthentication()
    {
	$auth = Zend_Auth::getInstance();
	$auth->setStorage(new Zend_Auth_Storage_Session());

	$acl = new Zend_Acl();
	
	Synrgic_Models_AclBuilder::buildAcl($acl);

	Zend_Registry::set('acl', $acl);
    }

    /**
     * Doctrine and Doctrine entity manager init
     */
    protected function _initDoctrine() {
	$this->bootstrap('defines'); // make sure Defines there 
	// include and register Doctrine's class loader
	require_once(DOCTRINE_COMMON_LIB_PATH.'/Doctrine/Common/ClassLoader.php');
	$classLoader = new \Doctrine\Common\ClassLoader( 'Doctrine\Common', DOCTRINE_COMMON_LIB_PATH);
	$classLoader->register();

	$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\DBAL', DOCTRINE_DBAL_LIB_PATH);
	$classLoader->register();

	$classLoader = new \Doctrine\Common\ClassLoader('Doctrine\ORM', DOCTRINE_LIB_PATH);
	$classLoader->register();

	// get doctrine configuration
	$options = $this->getOption('doctrine');

	// create the Doctrine configuration
	$config = new \Doctrine\ORM\Configuration();

	// set the proxy dir and set some options
	$config->setProxyDir($options['proxiesPath']);
	$config->setProxyNamespace('Synrgic\Proxies');
	\Doctrine\ORM\Proxy\Autoloader::register($options['proxiesPath'],'Synrgic\Proxies');

	// Set arraycache during development but as per
	// the doctrine manual, this is not recommended during
	// production.
	if( APPLICATION_ENV == 'development' ) {
	    $cache = new \Doctrine\Common\Cache\ArrayCache;
	    $config->setAutoGenerateProxyClasses(true);
	} else {
	    $cache = new \Doctrine\Common\Cache\ApcCache;
	    $config->setAutoGenerateProxyClasses(false);
	}

	$config->setMetadataCacheImpl($cache);
	$config->setQueryCacheImpl($cache);
	$config->setResultCacheImpl($cache);

	// choosing the driver for our database schema
	// we'll use annotations
	$driver = $config->newDefaultAnnotationDriver(
	    $options['entitiesPath']
	    );
	$config->setMetadataDriverImpl($driver);

	// now create the entity manager and use the connection
	// settings we defined in our application.ini
	$conn = array(
	    'driver'    => $options['driver'],
	    'user'      => $options['user'],
	    'password'  => $options['password'],
	    'dbname'    => $options['dbname'],
	    'host'      => $options['host'],
	    'charset'   => $options['charset'],
	    );

	$entityManager = \Doctrine\ORM\EntityManager::create($conn, $config);

	// push the entity manager into our registry for later use
	Zend_Registry::set("em", $entityManager);
	
	Zend_Registry::set("cachedriver", $cache);

	// register entity namespace
	// make sure namespace work
	// Synrgic\
	$classLoader = new \Doctrine\Common\ClassLoader('Synrgic', $options['entitiesPath']);
	$classLoader->register();
    
	return $entityManager;
    }

    /**
     * Init the Layout with default details
     */
    protected function _initLayoutHelper()
    {
	Zend_Layout::startMvc();
	$layout = Zend_Layout::getMvcInstance();
	$layout->displayAdverts=true;
    }

    /**
     * Init settings manager
     */
    protected function _initSettings()
    {
	// Settings rely on doctrine
	$this->bootstrap('doctrine');

	$settings = new Synrgic_Models_Settings();

	Zend_Registry::set('settings',$settings);

    }

    /**
     * Init Action Helpers
     */
    protected function _initActionHelpers()
    {
    	if(!defined('IGNORE_INI_SESSION') || IGNORE_INI_SESSION !== 'yes') 
    	{
			// Reliant on session and front controller
			$this->bootstrap('session');
			$this->bootstrap('FrontController');
			$helper=new Synrgic_Helper_URIHolder(Zend_Registry::get(SYNRGIC_SESSION));
			Zend_Controller_Action_HelperBroker::addHelper($helper);
		
			$view = new Zend_View();
			$view->doctype('XHTML1_TRANSITIONAL');
			$view->setEncoding('UTF-8');
			$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
			$view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
			$viewRenderer->setView($view);
			Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
    	}
    }
    
    public function _initGrid() {       
		$this->bootstrap('FrontController');
        require_once(APPLICATION_PATH.'/../library/Grid/Helper/Autoloader.php');
	    //$modules = array('adverts', 'bill', 'gambling', 'information', 'local', 'management', 'noticeboard', 'room', 'service', 'archive');
	$modules = array('archive');
		
	    $prefixes = array();
	    foreach($modules as $m) {
	        $prefixes[APPLICATION_PATH.'/modules/'.$m.'/views/helpers'] = 'GridHelper_';
	    }
	    Grid_Helper_Autoloader::registerPath($prefixes);
        
	    Zend_Loader_Autoloader::getInstance()->registerNamespace("Grid_");
	    $fc = $this->getResource('frontController');
        $fc->registerPlugin(new Synrgic_Plugin_ViewSetup());
    }
    
    public function _initTimezone() {
	    $this->bootstrap('settings');
	    try {
	        $settings = Zend_Registry::get('settings');
	        $timezone = $settings->General->Timezone->Value;
	        date_default_timezone_set($timezone);
	    }
	    catch(Exception $e) {
	        //let 'php' system set it for us
	    }    
    }
    
    public function _initNavigation() {
        $this->bootstrap('authentication');
        $config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/sitemap-mgmt.xml');
        $navigationmgmt = new Zend_Navigation($config);
        Zend_Registry::set('navigation', $navigationmgmt);
        $config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/sitemap-guest.xml');
        $navigation = new Zend_Navigation($config);
        Zend_Registry::set('navigation-guest', $navigation);
        $auth = Zend_Auth::getInstance();
	if($auth->hasIdentity()){
	    $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
	    $view = $viewRenderer->view;
	    $acl = Zend_Registry::get('acl');
	    $user = $auth->getIdentity();
	    $view->navigation()->setAcl($acl)
		->setRole($user->role);

/*
            $page = $navigation->findByLabel("Services");
            $this->addServices($page);

            $page = $navigationmgmt->findByLabel("Manage Services");
            $this->addServices($page,true);
*/
	}
        
    }
    
    public function _initMail()
    {
    	// get hotel email configuration
    	$options = $this->getOption('hotelemail');

        try {
            // TODO: replace with proper address
            $config = array(
                'auth' => $options['auth'],
                'username' => $options['user'],
                'password' => $options['password'],
            );

            $mailTransport = new Zend_Mail_Transport_Smtp('matrix.synrgicresearch.com', $config);
            Zend_Mail::setDefaultTransport($mailTransport);
        } catch (Zend_Exception $e){
            //Do something with exception

        }
    }


    private function addServices($parentPage, $isManagement=false)
    {
        $resource = $isManagement ? "management:services" : "service:index";
        $uri = $isManagement ? "/management/services/index/serviceid/" : "/service/index/index/id/";
        $func = $isManagement? 'getAllTopCatalogs' : 'getTopCatalogs';

        $em = Zend_Registry::get('em');
        $servicesRepo = $em->getRepository('\Synrgic\Service\Catalog');
        $services = $servicesRepo->$func();
        foreach($services as $service) {
            $page = new Zend_Navigation_Page_Uri();
            $label = $service->getName();
            if( $service->getIs_display() == false){
                $label .= ' *';
            }
            $page->setLabel($label);
            $page->setResource($resource);
            $page->setPrivilege("view");
            $page->setUri($uri .  $service->getId());
            if( $service->getIcon() != null){
                $page->set('icon',\Synrgic\MediaRepository::getUri($service->getIcon()));
            }  
            $parentPage->addPage($page);
        }
    }
}

