<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */
require_once 'Zend/Translate.php';

class Management_ProviderController extends Zend_Controller_Action {
	private $em;
	private $lang;
	
	public function init() {
		/* Initialize action controller here */
		$this->em = Zend_Registry::get ( 'em' );

		$auth=Zend_Auth::getInstance();
		$user=$auth->getIdentity();
		$this->lang = $user->getpreferredLanguage()->getlocale();
		if ($this->lang==NULL){
			$this->lang='en_US';
		}
	}
	
	public function indexAction() {
		$req = $this->getRequest ();
		
		
		$data = $this->em->getRepository ( '\Synrgic\Service\Provider' )->findAll();				
		$this->view->data = $data;
				
	}
	
	public function deleteAction() {
		$req = $this->getRequest ();
		$providerId = $req->getParam ( 'id' );	
		$provider = $this->em->getReference ( '\Synrgic\Service\Provider', "$providerId" );
		
		if(isset($provider)){
			if(count($provider->getservices()))
			{
				$this->displayMsg('This Provider has services,You can not delete it',$provider->getName());
				$this->_redirect ("/management/provider/index");
			}
			else{
				$this->displayMsg('Delete provider successfully',$provider->getName());
				$this->em->remove ( $provider );
				$this->em->flush ();
				$this->_redirect ("/management/provider/index");
			}			
		}
		else{
			$this->_redirect ("/management/provider/index");			
		}

	}
	
	public function addAction() {
		$req = $this->getRequest ();
		
		if ($req->getPost ()) {		
			$name=$req->getParam ( 'providerName' );
			
			if(!$this->isExist($name))
			{
				$provider = new \Synrgic\Service\Provider ();
				$provider->setName ( $name );
				$provider->setRemark ( $req->getParam ( 'remark' ) );
				
				$this->em->persist ( $provider );
				$this->em->flush ();
				$this->displayMsg('Add provider successfully',$name);
			}
			$this->_redirect ( 'management/provider/index' );
		}
	}
	
	public function editAction() {
		$req = $this->getRequest ();
		
		if ($req->getPost ()) {
			$name=$req->getParam ( 'providerName' );
			if(!$this->isExist($name))
			{
				$id = $this->_getParam ( 'id' );
				$provider = $this->em->getReference ( '\Synrgic\Service\Provider', $id );
				
				$provider->setName ($name);
				$provider->setRemark ( $req->getParam ( 'remark' ) );
					
				$this->em->persist ( $provider );
				$this->em->flush ();
				
				$this->displayMsg('Edit provider successfully',$name);
			}
			
			$this->_redirect ( "management/provider/index" );
		} else {
			$id = $this->_getParam ( 'id' );
			$provider = $this->em->getReference ( '\Synrgic\Service\Provider', "$id" );

			$this->view->data = $provider;		
		}
	}
	
	public function displayMsg($msg,$name) {
		$message=$this->view->translate($msg);
		$translateName=$this->view->translate('name');
		$this->_helper->flashMessenger->addMessage( $message.':'.$translateName.'='.$name);
	}
	public function isExist($name) {
		$existing = $this->em->getRepository('\Synrgic\Service\Provider')->findOneByName($name);
		if( isset($existing)){
			$this->displayMsg('Provider is exist',$existing->getName());
			return '1';
		}
		else{
			return '0';
		}
	}
}

