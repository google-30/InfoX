<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class AuthController extends Zend_Controller_Action
{
	var $session;

    public function init()
    {
		$this->session = Zend_Registry::get(SYNRGIC_SESSION);
    }

    public function indexAction()
    {
		if(isset($this->session->pin)){
			$this->_helper->layout->setLayout('single');
			$this->view->form = $this->getForm('/auth/unlock');
			$this->renderScript('auth/lock.phtml');
		} 
		$this->view->form = $this->getForm('/auth/lock');
    }

	public function unlockAction()
	{
		$request = $this->getRequest();
		$this->view->form = $this->getForm('/auth/lock');

		if( $request->isPost() && $this->view->form->isValid($request->getPost())){
			$pin = $this->view->form->getValue('pin');
			if( $this->session->pin == $pin ){
				unset($this->session->pin);
				$this->_redirect("/");
			}
		} 
		$this->_helper->flashMessenger->addMessage('Invalid Pin');
		$this->_redirect("/auth/");
	}

	public function lockAction()
	{
		$request = $this->getRequest();
		$this->view->form = $this->getForm('/auth/lock');

		if( $request->isPost() && $this->view->form->isValid($request->getPost())){
			$pin = $this->view->form->getValue('pin');
			$this->session->pin = $pin;
		} else {
			$this->_redirect("/auth/");
		}
		$this->_redirect("/auth/");
	}

	private function getForm($action)
	{
		$password = new Zend_Form_Element_Password('pin');
		$password->setLabel('pin:') ;
	 	$password->setRequired(true);  

		$form = new Zend_Form();
		$form->addElement($password);
		$form->addElement('submit',$action=="/auth/lock"?"Lock":"Unlock");
		$form->setMethod('post')  ;
		$form->setAction($action);

		return $form;
	}
}

