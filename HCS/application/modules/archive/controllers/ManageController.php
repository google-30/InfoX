<?php

define('UPLOAD_WORKER', APPLICATION_PATH. '/data/uploads/workers/images/');
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

    }
}
