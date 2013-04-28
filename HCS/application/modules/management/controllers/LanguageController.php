<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/

class Management_LanguageController extends Zend_Controller_Action
{
    private $em = null;
    private $loc = null;
    private $lang = null;

    public function init()
    {
        $this->em = Zend_Registry::get('em');
        $this->loc = Zend_Registry::get('Zend_Locale');
        $this->lang = $this->loc->getLanguage();
    }

    public function indexAction()
    {
        $this->_forward('add');
    }
    
    public function addAction() {
        $req = $this->getRequest();
        $form = $this->getForm()->setMethod('post');
        if($req->getPost() && $form->isValid($req->getPost())) {
            $obj = new \Synrgic\Language();
            $obj->locale = $form->getValue('language');
            $obj->name = $this->_getLanguageName($obj->locale); //name is redundant to the entity; zf done already for us 
            $obj->fromArray($form->getValues(true));
            $this->em->persist($obj);
            $this->em->flush();
            $this->_helper->getHelper('Redirector')->gotoSimple($action);
        }
        else {
            $this->view->data = $this->_getSupportedLanguageList();
            $this->view->form = $form;
        }
    }
    
    public function deleteAction() {
        $req = $this->getRequest();
        $id = $req->getParam('id');
        if($id>0) {
            $lang = $this->em->getReference('\Synrgic\Language', $id);
            $this->em->remove($lang);
            $this->em->flush();
            $this->_helper->getHelper('Redirector')->gotoSimple($action);
        }
    }
    
    private function _getLanguageList() {
        //notes (dtliu)- latest i18n: zh-CN => zh_Hans zh-TW => zh_Hant (@see ISO639)
        //no country (region) there; how smart tech here.
        //$loc = Zend_Registry::get('Zend_Locale');
        return Zend_Locale::getTranslationList('language', $this->lang);
    }
    
    private function _getLanguageName($code) {
        return Zend_Locale::getTranslation($code, 'language', $this->lang);
    }
    
    private function _getSupportedLanguageList() {
        $repo = $this->em->getRepository('\Synrgic\Language');
        $lang = Zend_Registry::get('Zend_Locale')->getLanguage();
        $langs = $repo->findAll();
        foreach($langs as $l) {
	        //follows the language standard translation not our owns
	        //@see _getLanguageName 
            $l->name = $this->_getLanguageName($l->locale);
        }
        return $langs;
    }
    
    private function getForm()
    {
        $form = new Synrgic_Models_Form();
        $languages = $this->_getLanguageList();
        $form->addSelectField('language',true,array('_multioptions'=>$languages))
             ->setMethod('post')
             ->addSubmitButton('submit', 'Add to Supported List')
             ->setFormTemplate('language/_form.phtml');
        $form->getElement('language')->setValue($this->lang);
        return $form;
    }
}
