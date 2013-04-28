<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_SettingsController extends Zend_Controller_Action
{
    const LOGO_PATH = "/images/logos/";
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
    }

    public function allAction()
    {
	$settings = Zend_Registry::get('settings');

	$this->view->data = $settings;
    }

    public function generalAction()
    {
	    $form = $this->_getGeneralForm()->setMethod('post');
	    $req = $this->getRequest();
	    if($req->getPost() && $form->isValid($req->getPost())) {
	       $this->_updateGeneral($form);
           $this->_helper->redirector->gotoSimple('general');
	    }
	    else {
	        $this->view->form = $form;
	    }
    }
    
    public function cancelAction() {
        $this->_helper->redirector->gotoSimple('general');
    }

    public function sectionAction() {
        $settings = Zend_Registry::get('settings');
        $section = $this->_getParam('s', 'Styling');
        $settings = $settings->getSection($section);
        
        $this->view->section = $section;
        $this->view->data = $settings;
    }


    public function getForm($settings)
    {
    	$form = new Synrgic_Models_Form();
    	foreach($settings as $setting){
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
    
    private function _getGeneralForm() {
        $general = Zend_Registry::get('settings')->General;
        $logo = $general->Logo->Value;
        $timezone = $general->Timezone->Value;
        $lang = $general->Language->Value;
        $dfmt = $general->DateFormat->Value;
        $tfmt = $general->TimeFormat->Value;
	    $name = $general->HotelName->Value;
        $currency = $general->Currency->Value;
        $address = $general->HotelAddress->Value;   
    
        $form = new Synrgic_Models_Form();
        
        $form->addFileField('logo_file', false)
	         ->addTextField('hotelName',true)
             ->addTextField('hoteladdress',true)
	         ->addTextField('currency', true)
             ->addSelectField('timezone', false, array('_multioptions'=>$this->_getTimezoneOptions()))
             ->addSelectField('language', false, array('_multioptions'=>$this->_getLanguageOptions()));
        $fdate = $form->createField(
                    'dateFormat', 'Radio', true, 
                    array(
                        '_multioptions'=>$this->_getDateFormatOptions(),
                        'separator'=>''
                    )
                 );
        $ftime = $form->createField(
                    'timeFormat', 'Radio', true,
                    array(
                        '_multioptions'=>$this->_getTimeFormatOptions(),
                        'separator'=>''
                    )
                 );
        $fdate->setValue($dfmt);
        $ftime->setValue($tfmt);
        $form->getElement('timezone')->setValue($timezone);
        $form->getElement('language')->setValue($lang);
        $form->getElement('logo_file')->setAttrib('size', 32);
        $form->getElement('hotelName')->setValue($name);
        $form->getElement('hoteladdress')->setValue($address);
        $form->getElement('currency')->setValue($currency);
        $form->addSubmitButton('save', 'Save Changes')
             ->addCancelButton()
             ->setFormTemplate('settings/_general-form.phtml', array('logo'=>$logo));
        $form->setAttrib('enctype', 'multipart/form-data');

        return $form;
    }
    
    private function _getTimezoneOptions() {
        $locale = Zend_Registry::get('Zend_Locale');
        $timezones = array();
        foreach($locale->getTranslationList('TerritoryToTimezone') as $tz=>$v) {
            $tzoffset = $this->_getTimezoneOffset($tz);
            if($tzoffset !== null) {
                $timezones[$tz] = $tzoffset;
            }            
        }
        return $timezones;
    }

    private function _getTimezoneOffset($tz) {
        try {
            $timezone = new DateTimeZone($tz);
            $now = new DateTime("now", $timezone);
            $offset = $timezone->getOffset($now);
            $h = $offset/3600;
            $m = ($offset - $h*3600)/60;
            $sign = $offset<0?'':'+';
            return sprintf("%s (GMT%s%s:%s)", $tz, $sign, $h, $m==0?'00':$m);
        }
        catch(Exception $e) {
            //bad timezone
            return null;
        }
    }
    
    private function _getLanguageOptions() {
        $locale = Zend_Registry::get('Zend_Locale');        
        $list = $locale->getTranslationList('language');
        $langs = array();
        foreach($list as $lang=>$x) {
            try {
                $desc = Zend_Locale::getTranslation($lang, 'language', $lang);
                if(!empty($lang) && !empty($desc)) {
                    $langs[$lang] = $desc;
                }
            }
            catch(Exception $e) {
                //just skip
            }
        }
        return $langs;
    }
    
    private function _getDateFormatOptions() {
        $now = new DateTime("now");
        // F d, Y: December 24, 2012
        // Y/m/d: 2012/12/24
        // m/d/Y: 12/24/2012
        // d/m/Y: 24/12/2012
        $formats = array("F d, Y", "Y/m/d", "m/d/Y", "d/m/Y");
        $options = array();
        foreach($formats as $f) {
            $options[$f] = $now->format($f);
        }
        return $options;
    }
    
    private function _getTimeFormatOptions() {
        $now = new DateTime("now");
        // 9:43 am g:i a
        // 9:43 AM g:i A
        // 09:43   H:i
        $formats = array("g:i a", "g:i A", "H:i");
        $options = array();
        foreach($formats as $f) {
            $options[$f] = $now->format($f);
        }
        return $options;
    }
    
    private function _updateGeneral($form) {
        $post = $form->getValues(true);
        $general = Zend_Registry::get('settings')->General;
        $this->_updateSetting($general->Timezone->Id, $post['timezone']);
        $this->_updateSetting($general->DateFormat->Id, $post['dateFormat']);
        $this->_updateSetting($general->TimeFormat->Id, $post['timeFormat']);
        $this->_updateSetting($general->Language->Id, $post['language']);
        $this->_updateSetting($general->HotelName->Id, $post['hotelName']);
        $this->_updateSetting($general->Currency->Id, $post['currency']);
        $this->_updateSetting($general->HotelAddress->Id, $post['hoteladdress']);
        if(isset($post['logo_file']) && !empty($post['logo_file'])) {
            try {
                $adapter = new Zend_File_Transfer_Adapter_Http();
                $target = APPLICATION_PATH.'/data/uploads/logos';
                $adapter->setDestination($target);
                $adapter->addFilter('Rename', $target);
                $adapter->addFilter('Rename', array('target'=>$target, 'overwrite'=>true));
                if($adapter->isValid()) {
                    foreach($adapter->getFileInfo() as $file=>$info) {
                        if($adapter->isUploaded($file)) {
                            $adapter->addFilter('Rename', $target."/".$info['name'], $file);
                            $adapter->receive($file);
                            $this->_updateSetting($general->Logo->Id, self::LOGO_PATH.$info['name']);
                        }
                    }
                }
            }
            catch(Exception $e) {}
        }
        $this->em->flush();
    }
    
    private function _updateSetting($id, $value) {
    	$setting = $this->em->getReference('\Synrgic\Setting', $id);
   	    $setting->value = $value;
   	    $this->em->persist($setting);
    }
}
