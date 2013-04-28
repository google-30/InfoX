<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Synrgic_Plugin_Language extends Zend_Controller_Plugin_Abstract
{
	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$session = new Zend_Session_Namespace(SYNRGIC_SESSION);
		$locale = Zend_Registry::get('Zend_Locale');
		//$translate = Zend_Registry::get('Zend_Translate');
		$auth = Zend_Auth::getInstance();
		$user = $auth->getIdentity();
		$acl = Zend_Registry::get('acl');

		/**
		 * Language Settings follow the following proceedure.
		 *
		 * 1. Use language parameter if specified
		 * 2. Else if role > guest, use preferred language
		 * 3. Else use session defined language if exists
		 * 4. Else use guest predefined language
		 * 5. Drop back to default
		 */
		if( $request->getParam('language',null)){
		    $lang = $request->getParam('language');
		}else if ( $acl->isAllowed($user->role,'management:dashboard','view') ){  
		    $lang = $user->getPreferredLanguage()->getLocale();
		} else if (isset($session->language)){
		    $lang = $session->language;
		} else  {
		    $lang = $user->getPreferredLanguage()->getLocale();
		}

		//if(isset($lang) && Zend_Locale::isLocale($lang) && $translate->isAvailable($lang)){
		if(isset($lang) && Zend_Locale::isLocale($lang)) {
			//$translate->setLocale($lang);
			$locale->setLocale($lang);
		} else {
			$locale->setLocale(SYNRGIC_LOCALE_DEFAULT);
			$lang = SYNRGIC_LOCALE_DEFAULT; 
			//$translate->setLocale(SYNRGIC_LOCALE_DEFAULT);
		}
		
		//finally we have selected the language. it's ready to set translations
		$synrgicTr = Zend_Registry::get('Synrgic_Translate');
		Zend_Registry::set('Zend_Translate', $synrgicTr->getTranslate(array('lang'=>$lang)));
	}

}

