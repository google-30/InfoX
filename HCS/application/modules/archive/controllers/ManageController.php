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

    public function addAction()
    {
            
    }

    public function editAction()
    {
        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $archive = $this->_archive->findOneBy(array("id"=>$id));
        $this->view->archive = $archive;
    }

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
 
        $requests = $this->getRequest()->getPost();
        if(0)
        {
            var_dump($requests);
            return;
        }        
       
        //$this->_redirect("archive/manage");
    }
}
