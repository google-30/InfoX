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
       
        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "0");
        $title = $this->getParam("title1");
        $remark = $this->getParam("remark");
        $path = $this->getParam("file", "");
        $type = $this->getParam("type", "doc");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Archive(); 
        }
        else
        {
            $data = $this->_archive->findOneBy(array("id"=>$id));
        }
 
        $data->setUpdate(new Datetime("now"));       
        $data->setTitle($title);
        $data->setRemark($remark);
        $data->setType($type);
        $data->setPath($path);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        //$this->_redirect("archive/manage");
    }
}
