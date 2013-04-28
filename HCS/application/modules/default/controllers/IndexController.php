<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */


class IndexController extends Zend_Controller_Action
{

    private $_session;

    public function init()
    {
	$this->_session = Zend_Registry::get(SYNRGIC_SESSION);
	if(! isset($this->_session->language)){
//	    $this->_redirect("/welcome/");
	$this->_redirect("/management/");	
	}
    }

    public function indexAction()
    {
	$settings = Zend_Registry::get('settings');
	$settings = $settings->get('Slideshow');
	$this->view->items=$settings;

	$user = Zend_Auth::getInstance()->getIdentity();

	$language = $this->_session->language;

	//XXX TODO Make Dynamic - 20121207 - benjsc 
	$this->view->content = file_get_contents(APPLICATION_PATH .
						 "/../public/example/mainpage.$language.html");
	$this->view->content=preg_replace('/%NAME%/',$user->name,$this->view->content);
	$this->view->content=preg_replace('/%ROOM%/',$this->_session->room->getName(),$this->view->content);
    }

    public function languageAction()
    {
	$em = Zend_Registry::get('em');
	$form = new Synrgic_Models_Form();

	$languageRepo = $em->getRepository('\Synrgic\Language');
	$languageList = $languageRepo->getLocaleToNameList();

	$field = $form->addSelectField('language',true, array(
						'onchange'=>'this.form.submit()',
						'_multioptions'=>$languageList));
	$form->setAction('/welcome/index/set');

	// If there is a guest associated with the room, use their preferred
	// language by default
	$element = $form->getElement('language');
	$element->setValue($this->_session->language);
	$element->setAttrib("data-theme", "d");
	$this->view->form = $form;
    }

}

?>
