<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_AuthController extends Zend_Controller_Action
{
    private $em;


    public function preDispatch()
    {
	/* 
	 * Save the URI that the user visited before 
	 * going to this page as this might have been an authentication
	 * request for an action already being performed
	 */
	$this->_helper->URIHolder->save();
    }	

    public function init()
    {
	$this->em = Zend_Registry::get('em');
        $this->_helper->layout->setLayout('auth');
    }

    public function indexAction()
    {
	$this->view->form = $this->getForm();
    }

    public function loginAction()
    {
	$request = $this->getRequest();
	$form = $this->getForm();

	if($request->isPost() && $form->isValid($request->getPost())){

	    $username = $form->getValue('username');
	    $password = $form->getValue('password');	

	    $authAdapter = new Synrgic_Auth_Adapter($this->em,'\Synrgic\User');
	    $authAdapter->setIdentityColumn('username');
	    $authAdapter->setCredentialColumn('password');
	    $authAdapter->setIdentity($username);
	    $authAdapter->setCredential($password);  

	    $auth = Zend_Auth::getInstance();  
	    $result = $auth->authenticate($authAdapter);

	    // is the user a valid one?  
	    if($result->isValid())  {  
		// get all info about this user from the account table  
		// ommit only the password, we don't need that  
		$userInfo = $authAdapter->getResultObject(null, 'password');  

		// the default storage is a session with namespace Zend_Auth  
		$authStorage = $auth->getStorage();  
		$authStorage->write($userInfo);  
	    } else {
		$this->_helper->flashMessenger->addMessage('Username/password incorrect');
	    }
	    $this->_redirect($this->_request->getPost('return'));
	} else {
	    $this->_redirect('/management/auth/');  
	}
    }

    public function logoutAction()  
    {  
	// clear everything - session is cleared also!  
	Zend_Auth::getInstance()->clearIdentity();  
	Zend_Session::forgetMe();
	Zend_Session::destroy();
	$this->_redirect('/management/');
    }  	

    protected function getForm()
    {	
	$form = new Synrgic_Models_Form();
	$username = new Zend_Form_Element_Text('username');  
	$username->setLabel('Username/用户名:')  
	    ->setRequired(true);  

	$password = new Zend_Form_Element_Password('password');  
	$password->setLabel('Password/密码:')  
	    ->setRequired(true);  

	$last = new Zend_Form_Element_Hidden('return');
	$last->setValue($this->_helper->URIHolder->getURI());

	$form->setAction('/management/auth/login')  
	    ->setMethod('post')  
	    ->addElement($username)  
	    ->addElement($password)  
	    ->addElement($last)
	    ->addSubmitButton('Login','Login/登录');

	return $form;
    }

}

