<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Welcome_IndexController extends Zend_Controller_Action
{
    private $_em;
    private $_settings;
    private $_session;

    public function init()
    {
	$this->_em = Zend_Registry::get('em');
	$this->_settings = Zend_Registry::get('settings');
	$this->_session = Zend_Registry::get(SYNRGIC_SESSION);
    }

    public function indexAction()
    {
	$this->view->logo = $this->_settings->General->Logo->value;
	$this->view->form = $this->getForm();
    }

	public function startAction()
    {
	 return $this->_redirect('/');
    }

    public function setAction()
    {
		if(!$this->getRequest()->isPost()){
	    	return $this->_forward('index');
		}

	$form = $this->getForm();
	$form->isValid($_POST);
	$value = $form->getValues();

	// We save the language selected the Synrgic namespace session
	// This will be used later in the default applications bootstrap to set 
	// the locale across all applications
	$this->_session->language = $value['language'];
	if(isset($value['return']))
		return $this->_redirect($value['return']);
	else
		return $this->_redirect('/');
	}

    public function getForm()
    {
	$last = new Zend_Form_Element_Hidden('return');
	$last->setValue($this->_helper->URIHolder->getURI());
	$last->setValue('/welcome');
	$form = new Synrgic_Models_Form();

	$languageRepo = $this->_em->getRepository('\Synrgic\Language');
	$languageList = $languageRepo->getLocaleToNameList();

	$form->addSelectField('language',true, array('onchange'=>'this.form.submit()','_multioptions'=>$languageList))
	    //->addSubmitButton('Start',$this->view->translate('Start'))
		->addCancelButton('start',$this->view->translate('Start'))
		->addElement($last)
	    //->setFormTemplate('/index/_form.phtml')
	    ->setAction('/welcome/index/set');

	// If there is a guest associated with the room, use their preferred
	// language by default
	if(isset( $this->_session->guest )){
	    $element = $form->getElement('language');
	    $element->setValue($this->_session->guest->getPreferredLanguage()->getLocale());
	    $element->setAttrib("data-theme", "d");
		if(!isset($this->_session->language))
		$this->_session->language = $this->_session->guest->getPreferredLanguage()->getLocale();
	}

	// If there is a guest associated with the room, use their preferred
	// language by default
	if(isset( $this->_session->language )){
		$element = $form->getElement('language');
		$element->setValue($this->_session->language);
		$element->setAttrib("data-theme", "d");
	}else{
		$this->_session->language = 'en_US';
	}
	return $form;
    }
}
