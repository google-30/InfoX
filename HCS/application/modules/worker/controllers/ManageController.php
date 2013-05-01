<?php

class Worker_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_worker;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
    }

    public function indexAction()
    {
        $workers = $this->_worker->findAll();
        $this->view->workers = $workers;
    }

    public function editAction()
    {
        $id = $this->_getParam("id"); 
        //echo "id=$id";
    }
    
}
