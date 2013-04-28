<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_LocalController extends Zend_Controller_Action
{
    private $em = null;

    public function init()
    {
        $this->em = Zend_Registry::get('em');
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('savesetting', 'json');
        $ajaxContext->initContext();
    }

    public function indexAction()
    {
        $form = $this->_getForm()->setMethod('post');
        $req = $this->getRequest();
        if($req->getPost() && $form->isValid($req->getPost())) {
            $this->_updateSettings($form);
            $this->gotodash();
        }
        else {
            $this->view->form = $form;
        }
    }

    public function cancelAction() {
        $this->gotodash();
    }

    public function getForm($settings)
    {
        $form = new Synrgic_Models_Form();
        foreach($settings as $setting) {
            $subForm = new Zend_Form_SubForm();
            $subForm->addTextField('name', true);
            $form->addSubForm($subForm);
        }
    }

    public function savesettingAction() {
        $id = $this->_getParam('id', 0);
        $desc = trim(trim($this->_getParam('description', '')),"\xc2\xa0");
        $value = trim(trim($this->_getParam('value', '')), "\xc2\xa0");

        if($id === 0) {
            // add a new setting
            $name = trim(trim($this->_getParam('name', '')), "\xc2\xa0");
            $section = trim(trim($this->_getParam('section','')), "\xc2\xa0");
            $setting = new \Synrgic\Setting();
            $setting->description = $desc;
            $setting->value = $value;
            $setting->name = $name;
            $setting->section = $section;
        }
        else {
            // update the setting
            $setting = $this->em->getReference('\Synrgic\Setting', $id);
            //$setting->description = $desc;
            $setting->value = $value;
        }
        $this->em->persist($setting);
        $this->em->flush();
        $this->_helper->json($setting->getId());
    }

    private function _getForm() {
        $settings = Zend_Registry::get('settings')->LocalAttractions;
        $nbzoom = $settings->nearbyzoom->Value;
        $nbdistance = $settings->nearbydistance->Value;
        $centeraddress = $settings->centeraddress->Value;
        $cityzoom = $settings->cityzoom->Value;

        $form = new Synrgic_Models_Form();

        $form->addTextField('nearbyzoom',true)
        ->addTextField('nearbydistance',true)
        ->addTextField('centeraddress',true)
        ->addTextField('cityzoom',true);

        $form->getElement('nearbyzoom')->setValue($nbzoom);
        $form->getElement('nearbydistance')->setValue($nbdistance);
        $form->getElement('centeraddress')->setValue($centeraddress);
        $form->getElement('cityzoom')->setValue($cityzoom);
        $form->addSubmitButton('save', 'Save Changes')
        ->addCancelButton()
        ->setFormTemplate('local/_local-form.phtml');
        $form->setAttrib('enctype', 'multipart/form-data');

        return $form;
    }

    private function _updateSettings($form) {
        $post = $form->getValues(true);
        $settings = Zend_Registry::get('settings')->LocalAttractions;
        $this->_updateSetting($settings->nearbyzoom->Id, $post['nearbyzoom']);
        $this->_updateSetting($settings->nearbydistance->Id, $post['nearbydistance']);
        $this->_updateSetting($settings->centeraddress->Id, $post['centeraddress']);
        $this->_updateSetting($settings->cityzoom->Id, $post['cityzoom']);

        $this->em->flush();
    }

    private function _updateSetting($id, $value) {
        $setting = $this->em->getReference('\Synrgic\Setting', $id);
        $setting->value = $value;
        $this->em->persist($setting);
    }

    private function gotodash()
    {
        $this->_helper->redirector->gotoSimple("index", "dashboard", "management");
    }

}

