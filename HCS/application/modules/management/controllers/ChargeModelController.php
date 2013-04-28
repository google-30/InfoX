<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/*-
 * An interface to charge model management 
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 14/02/2013
 * history:
 *   -
 */

class Management_ChargeModelController extends Zend_Controller_Action
{
    private $em;
    private $repo;
   
    public function init()
    {
        $this->em = Zend_Registry::get("em");
        $this->repo = $this->em->getRepository('\Synrgic\ChargeModel');
    }

    public function indexAction()
    {
        $this->view->data = $this->repo->findAll();
        $this->_showMessage();
    }

    public function addAction() {
        $req = $this->getRequest();
        $form = $this->getForm()->setMethod('post');
        if($req->getPost() && $form->isValid($req->getPost())) {
            $obj = new \Synrgic\ChargeModel();
            $obj->fromArray($form->getValues(true));
            $this->em->persist($obj);
            $this->em->flush();
            $this->_gotoAction('index', 'Charge model added successfully: id = '.$obj->getId());
        }
        else {
            $this->view->form = $form;
        }
    }

    public function editAction() {
        $req = $this->getRequest();
        $form = $this->getForm()->setMethod('post');
        $id = $this->_getParam('id');
        $obj = $this->repo->find($id);
        if($req->getPost() && $form->isValid($req->getPost())) {
            $obj->fromArray($form->getValues(true));
            $this->em->persist($obj);
            $this->em->flush();
            $this->_gotoAction('index', 'Charge model updated successfully: id = '.$obj->getId());
        }
        else {
            $form->populate($obj->toArray());
            $this->view->form = $form;
        }
    }

    public function deleteAction() {
       $id = $this->_getParam('id');
       $obj = $this->em->getReference('\Synrgic\ChargeModel', $id);
       if($obj != null) {
           $this->em->remove($obj);
           $this->em->flush();
           $this->_gotoAction('index', 'Charge model deleted successfully: id = '.$id);
       } 
       else {
           $this->_gotoAction('index');
       }
    }

    public function cancelAction() {
        $this->_gotoAction('index');
    }

    private function getForm() 
    {
        $form = new Synrgic_Models_Form();
        $form->addTextField("name", true)
             ->addSelectField("algorithm", false, array(
                     'class'=>'custom',
                     '_multioptions'=>Synrgic_Models_Charge::listAlgorithms(),
                     ))
             ->addSubmitButton()
             ->addCancelButton()
             ->setFormTemplate('charge-model/_form.phtml');
        return $form;
    }

    // a short hand to wrap a notification message
    private function _gotoAction($action, $msg='', $params=array()) {
       $this->_helper->flashMessenger->addMessage($msg);
       //$this->_forward($action, null, null, array('id'=>null));
       $this->_helper->getHelper('Redirector')->gotoSimple($action, null, null, $params);
    }
    
    private function _showMessage() {
        $messages = $this->_helper->flashMessenger->getMessages();
        if(!empty($messages)) {
            $this->view->message = implode('<br/>', $messages);
        }
        else {
            $this->view->message = null;
        }
        $this->_helper->flashMessenger->clearMessages();
    }
}

