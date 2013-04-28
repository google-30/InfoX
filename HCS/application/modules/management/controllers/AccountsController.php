<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_AccountsController extends Zend_Controller_Action
{
    const EDIT=1;
    private $em = null;

    public function init()
    {
		$this->em = Zend_Registry::get('em');
    }

    public function indexAction()
    {
	$this->forward('list');
    }

    public function listAction()
    {
        $data = $this->em->getRepository('\Synrgic\User')->findAll();
        $this->view->data = $data;
    }

    public function addAction()
    {
        $req = $this->getRequest();
        $form = $this->getForm()->setMethod('post');
        if($req->getPost() && $form->isValid($req->getPost())) {
			$values = $form->getValues();
			$pwhelper = new Synrgic_Models_PasswordHelper();
            $user = new \Synrgic\User();
			$existing = $this->em->getRepository('\Synrgic\User')->findOneByUsername($values['username']);

			if( isset($existing)){
				$this->view->form = $form;
				$this->_helper->flashMessenger->addMessage('Username already exists');
				$this->_redirect('/management/accounts/add');
			} else {
				
				$values['provider'] = $this->em->getRepository('\Synrgic\Service\Provider')->findOneById( $values['provider']);
				$values['preferredLanguage'] = $this->em->getRepository('\Synrgic\Language')->findOneByLocale( $values['preferredLanguage']);
				$user->fromArray($values);


				// See what the user wanted to do with the password
				if( $form->getValue('passwordType')=='auto'){
					$password = $pwhelper->generatePassword();
					$passwordConfirm = $password;
				} else {
				    $password = $form->getValue('password');
				    $passwordConfirm = $form->getValue('passwordConfirm');
				}
				
				if( $this->_setPasswordForUser($user, $password,
							      $passwordConfirm) == false){
				    $this->_helper->flashMessenger->addMessage('Passwords don\'t match');
				} else {
				    $this->_helper->flashMessenger->addMessage('Password successfully Set');
				    $this->forward('index','accounts','management');
				}
			}
        }
        else {
            $this->view->form = $form;
        } 
    }

    public function editAction()
    {
	$req = $this->getRequest();
	$form = $this->getForm(self::EDIT);
	$id = $this->_getParam('id');
	$user = $this->em->getReference('\Synrgic\User', $id);
	if($req->getPost() && $form->isValid($req->getPost())) {
	    $values=$form->getValues();
	    
	    $values['provider'] = $this->em->getRepository('\Synrgic\Service\Provider')->findOneById( $values['provider']);
	    $values['preferredLanguage'] = $this->em->getRepository('\Synrgic\Language')->findOneByLocale( $values['preferredLanguage']);
	    $user->fromArray($values);
	    $this->em->persist($user);
	    $this->em->flush();
	    $this->_helper->flashMessenger->addMessage('User' . $user->name .  'added');
	    $this->_forward('index');
	}
	else {
	    $values = $user->toArray();
	        
	    $values['provider']=$values['provider']->getId();
	    $values['preferredLanguage']=$values['preferredLanguage']->getLocale();
	    $form->populate($values);
	    $this->view->form = $form;
	} 
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');
        if($id>0) {
            $user = $this->em->getReference('\Synrgic\User', $id);
            $this->em->remove($user);
            $this->em->flush();
			$this->_helper->flashMessenger->addMessage('User removed');
            $this->_forward('index');
        }
        else {
            $this->_forward('index');
        }
    }

    public function resetpasswdAction()
    {
        $req = $this->getRequest();
        $form = $this->getResetPasswordForm();
	if( $req->getPost() &&  $form->isValid($req->getPost())){
	    $id = $this->_getParam('id');
	    $user = $this->em->getReference('\Synrgic\User', $id);
	    if( $this->_setPasswordForUser($user, $form->getValue('password'),
					  $form->getValue('passwordConfirm')) == false){
		$this->_helper->flashMessenger->addMessage('Passwords don\'t match');
	    } else {
		$this->_helper->flashMessenger->addMessage('Password successfully Set');
		$this->redirect('/management/accounts/');
	    }
	}

	$this->view->form =$form;
    }

    private function getResetPasswordForm()
    {
	$form = new Synrgic_Models_Form();
	$field = $form->addField('passwordConfirm','Password', true);
	$form->addField('password','Password', true);
	$form->addSubmitButton();
	$form->addCancelButton();
	$form->setFormTemplate("/accounts/_passwordResetForm.phtml");
	$form->setMethod("POST");
	return $form;
    }

    private function getForm($mode = null)
    {
	$form = new Synrgic_Models_Form();


	if( $mode != self::EDIT ){	
	    $list=array('auto'=>'Generated', 'entered'=>'User Entered');
	    $form->addSelectField('passwordType',true,array('_multioptions'=>$list));
	    $form->addField('passwordConfirm','Password', false);
	    $form->addField('password','Password', false);
	    $form->addTextField('username',true);
	} else {
	    $form->addTextField('username',true,array('disabled'));
	}

	$form
	    ->addTextField('name', true)
	    ->addTextField('email', true)
	    ->addField('disabled','Checkbox',true)
	    ->addSubmitButton()
	    ->addCancelButton()
	    ->setFormTemplate('/accounts/_form.phtml');


	
	
	$languageList = $this->em->getRepository('\Synrgic\Language')->getLocaleToNameList();        

	$form->addSelectField('preferredLanguage',true,
			      array('_multioptions'=>$languageList));
	$providers=$this->em->getRepository ( '\Synrgic\Service\Provider' )->getProviderNameList();
	$form->addSelectField('provider',true,array('_multioptions'=>$providers));
	// XXX/TODO: Determine if roles are manageble in the database
	// If so convert roles to a database table and add permissions
	// to the database as well. This would also cause
	// Bootstrap to be updated as well - benjsc 20121107
	$roles = array('staff' => 'General Staff Member','admin' => 'Administrator');
	$form->addSelectField('role',true, array('_multioptions'=>$roles));

        return $form;
    }

    public function cancelAction()
    {
		$this->_forward('index');
    }

    /**
     * Used to set the password for a user. 
     *
     * @return true if user updated,false if given passwords don't match
     */
    private function _setPasswordForUser($user,$password,$confirmation)
    {
	if( $password != $confirmation)
	    return false;

	// encrypt the password
	$pwhelper = new Synrgic_Models_PasswordHelper();
	$cryptedString = $pwhelper->cryptPassword($password);
	$user->setPassword($cryptedString);
	$this->em->persist($user);
	$this->em->flush();
	return true;
    }

}
