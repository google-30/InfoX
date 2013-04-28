<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_RoomServiceController extends Zend_Controller_Action
{

    private $em;
    private $role;
    private $userId;
    public function init ()
    {
    	$this->em = Zend_Registry::get('em');
    	$this->session = Zend_Registry::get(SYNRGIC_SESSION);
    	
    	$auth=Zend_Auth::getInstance();
    	$user=$auth->getIdentity();
    	$this->role=$user->getRole(); 	
    	$this->userId=$user->getId();
    	
    	//Zend_Debug::dump($this->userId) ;
    
    }

    public function indexAction ()
    {
        // $contentArray=$this->em->createQuery('SELECT l.fid FROM
        // \Synrgic\Service\Catalog l ') ->getResult();
        // print_r($contentArray);
        
    	$currentUser = $this->em->getRepository ( '\Synrgic\User' )->findOneBy ( array ('id' => $this->userId ) );
    	
    	$provider=$currentUser->getProvider();
    	
    	//Zend_Debug::dump($provider) ;

    	if(($provider<>NULL)&&($this->role=='staff'))
    	{
    		$provider_id=$provider->getId();
    		$this->_redirect ( "management/detailstate/index/providerId/$provider_id" );
    	}
    	else if(($provider==NULL)&&($this->role!='admin'))
    	{
    		$this->_redirect ( "management" );
    	}
    	else{
    		
    	}
        
    }
}
