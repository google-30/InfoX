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

class Management_ChargeItemController extends Zend_Controller_Action
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
        $model = $this->repo->find($this->_getParam('mid'));
        $this->view->model = $model;
        $this->view->data = $model->getChargeItems()->toArray();
        $this->_showMessage();
    }

    public function addAction() {
        $req = $this->getRequest();
        $mid = $req->getParam('mid');
        $form = $this->getForm()->setMethod('post');
        if($req->getPost() && $form->isValid($req->getPost())) {
            $obj = $this->repo->addChargeItem($form->getValues(true));
            $this->_gotoAction('index', 'Charge item added successfully: id = '.$obj->getId(), array('mid'=>$mid));
        }
        else {
            $this->view->model = $this->repo->find($mid);
            $form->populate(array('mid'=>$mid, 'units'=>1));
            $this->view->form = $form;
        }
    }

    public function editAction() {
        $req = $this->getRequest();
        $mid = $req->getParam('mid');
        $form = $this->getForm($mid)->setMethod('post');
        $id = $this->_getParam('id');
        $obj = $this->em->getRepository('Synrgic\ChargeItem')->find($id);
        if($req->getPost() && $form->isValid($req->getPost())) {
            $post = $form->getValues(true);
            $post['id'] = $id;
            $obj = $this->repo->updateChargeItem($post);
            $this->_gotoAction('index', 'Charge item updated successfully: id = '.$obj->getId(), array('mid'=>$mid));
        }
        else {
            $values = $obj->toArray();
            $values['mid'] = $mid;
            $form->populate($values);
            $this->view->model = $obj->model;
            $this->view->form = $form;
        }
    }

    public function deleteAction() {
       $mid = $this->_getParam('mid');
       $id = $this->_getParam('id');
       $obj = $this->repo->deleteChargeItem($id);
       if($obj != null) {
           $this->_gotoAction('index', 'Charge model deleted successfully: id = '.$id, array('mid'=>$mid));
       } 
       else {
           $this->_gotoAction('index', null, array('mid'=>$mid));
       }
    }

    public function cancelAction() {
        $this->_gotoAction('index', null, array('mid'=>$this->_getParam('mid')));
    }

    private function getForm() 
    {
        $form = new Synrgic_Models_Form();
        $form->addTextField("name", true)
             ->addTextField("units", true)
             ->addTextField("price", true)
             ->addField("mid", "Hidden",true)
             ->addSubmitButton()
             ->addCancelButton()
             ->setFormTemplate('charge-item/_form.phtml');
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

