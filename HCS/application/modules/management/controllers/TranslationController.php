<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_TranslationController extends Zend_Controller_Action
{
    private $path = null;
    public function init()
    {
        $this->path = APPLICATION_PATH."/data/languages";
    }

    public function indexAction()
    {
        $this->_forward('upload');
    }
    
    public function uploadAction() {
        $req = $this->getRequest();
        $form = $this->getForm()->setMethod('post');
        if($req->getPost()) {
            $this->_uploadTranslationResources();
            $this->_helper->getHelper('Redirector')->gotoSimple('index');
        }
        else {
            $this->view->data = $this->_getTranslationResources();
            $this->view->form = $form;
        }
    }
    
    public function downloadAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $name = $this->_getParam('res');
        $ext = $this->_getParam('ext');
        $basename = $name.'.'.$ext;
        $file = $this->path.'/'.$basename;
        header('Content-Type: application/'.$ext.'; charset=UTF-8');
        header('Content-Disposition: attachment; filename="'.$basename.'"');
        header('Pragma:public');
        header('Content-Transfer-Encoding:binary');
        header('Content-Length:'.filesize($file)+3);
        
        ob_clean();
        readfile($file);
        //echo pack("CCC", 0xef, 0xbb, 0xbf); //UTF-8 BOM
        
        exit; // i really really don't want zf bother me here
    }
    
    private function _uploadTranslationResources() {
        $target = $this->path;
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($target);
        $adapter->addFilter('Rename', $target);
        $adapter->addFilter('Rename', array('target'=>$target, 'overwrite'=>true));
        if($adapter->isValid()) {
            foreach($adapter->getFileInfo() as $file=>$info) {
                if($adapter->isUploaded($file)) {
                    try {
                        $this->_backup($info['name']);
                        $adapter->addFilter('Rename', $info, $file);
                        $adapter->receive($file);
                        list(,$locale) = explode('_', $info['name'], 2);
                        $tr = Zend_Registry::get('Synrgic_Translate');
                        $tr->refreshCache($locale);
                    }
                    catch(Exception $e) {
                    }
                }
            }
        }
    }
    
    private function _backup($filename) {
        //simple policy now
        $file = $this->path.'/'.$filename;
        if(file_exists($file)) {
            rename($file, $file.'.'.time());
        }
    }
    
    // lang, file, ext
    private function _getTranslationResources() {
        $resources = array();
        if($handle = opendir($this->path)) {
            
            while(false !== ($file = readdir($handle))) {
                if($file !== '.' && $file !== '..') {
                    $pathinfo = pathinfo($file);
                    if(strtolower($pathinfo['extension']) === 'csv') {
                        //<name>_<lang>.csv ; <name>_<lang>_<region>.CSV ....
                        $filename = $pathinfo['filename'];
                        list(,$lang)=explode('_', $filename, 2);
                        $resources[] = array('lang'=>$lang, 'file'=>$filename, 'ext'=>$pathinfo['extension']);
                    }
                }
            }
            closedir($handle);
        }
        usort($resources, array($this, '_sort'));
        return $resources; 
    }
    
    private function _sort($a, $b) {
        return strcmp($a['lang'], $b['lang']);
    }
    
    private function getForm()
    {
        $form = new Synrgic_Models_Form();
        $form->addFileField('path', false, array('size'=>55, 'extension'=>'csv'))
             ->addSubmitButton('upload','Upload')
             ->setFormTemplate('translation/_form.phtml');
        $form->getElement('path')->setAttrib('size', 50);
        return $form;
    }
}
