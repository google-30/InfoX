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

    public function addAction()
    {
        $id = $this->_getParam("id"); 
        //echo "id=$id";
        //$loginForm = new Synrgic_Forms_Login();
        //$this->view->workerform = $loginForm;
        $uploadform = new Synrgic_Forms_Upload();
        $this->view->workerform = $uploadform;
    }

    public function editAction()
    {
        $id = $this->_getParam("id"); 
        //echo "id=$id";
        $form = new Synrgic_Forms_Worker();

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {

                // success - do something with the uploaded file
                $uploadedData = $form->getValues();
                $fullFilePath = $form->file->getFileName();

                Zend_Debug::dump($uploadedData, '$uploadedData');
                Zend_Debug::dump($fullFilePath, '$fullFilePath');

                // was a file uploaded?
                if($form->file->isUploaded()) {
                    //$form->file->receive(); // move to the specified place
                    $location = $form->file->getFileName();
                    Zend_Debug::dump($location, '$location');
                }

                echo "done";
                exit;

            } else {
                $form->populate($formData);
            }
        }

        $this->view->workerform = $form;
    }
    
    public function submitAction()
    {
       if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {

                // success - do something with the uploaded file
                $uploadedData = $form->getValues();
                $fullFilePath = $form->file->getFileName();

                Zend_Debug::dump($uploadedData, '$uploadedData');
                Zend_Debug::dump($fullFilePath, '$fullFilePath');

                echo "done";
                exit;

            } else {
                $form->populate($formData);
            }
        }       

    }

}
