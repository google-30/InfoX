<?php

define('UPLOAD_SW', APPLICATION_PATH. '/data/uploads/archives/softwares/');
define('UPLOAD_Doc', APPLICATION_PATH. '/data/uploads/archives/documents/');
class Archive_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_archive;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_archive = $this->_em->getRepository('Synrgic\Infox\Archive');
    }

    public function indexAction()
    {
        $softwares = $this->_archive->findBy(array("type"=>"sw"));
        $this->view->softwares = $softwares;
        $documents = $this->_archive->findBy(array("type"=>"doc"));
        $this->view->documents = $documents;
    }
}
