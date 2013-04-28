<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_RoomController extends Zend_Controller_Action
{

    private $em = null;

    public function init()
    {
		$this->em = Zend_Registry::get('em');
    }


    public function indexAction()
    {
		$this->forward('list');
    }

    public function listAction()
    {
        $data = $this->em->getRepository('\Synrgic\Room')->findAll();

        $this->view->data = $data;
    }

    public function addAction()
    {
        $req = $this->getRequest();
        $form = $this->getForm()->setMethod('post');
        if($req->getPost() && $form->isValid($req->getPost())) {
            $room = new \Synrgic\Room();
			$existing = $this->em->getRepository('\Synrgic\Room')->
				findOneBy(array( 'name'=>$form->getValue('name')));
			if( isset($existing)){
				$this->view->form = $form;
				$this->_helper->flashMessenger->addMessage('Room already exists');
				$this->_redirect('/management/room/add');
			} 
            $room->fromArray($form->getValues(true));
			$this->em->persist($room);
			$this->em->flush();
			$this->_helper->flashMessenger->addMessage('Room ' . $room->getName() .  'added');
			$this->_forward('index');
        }
        else {
            $this->view->form = $form;
        } 
    }

    public function deleteAction()
    {
        $id = $this->_getParam('id');
        if($id>0) {
            $user = $this->em->getReference('\Synrgic\Room', $id);
            $this->em->remove($user);
            $this->em->flush();
			$this->_helper->flashMessenger->addMessage('Room removed');
            $this->_redirect('/management/room/');
        }
        else {
            $this->_forward('index');
        }
    }

    public function editAction()
    {
        $req = $this->getRequest();
        $form = $this->getForm()->setMethod('post');
        $id = $this->_getParam('id');
        $room = $this->em->getReference('\Synrgic\Room', $id);
        if($req->getPost() && $form->isValid($req->getPost())) {
            $room->fromArray($form->getValues(true));
            $this->em->persist($room);
            $this->em->flush();
			$this->_helper->flashMessenger->addMessage('Room' . $room->getName() .  'updated');
			$this->_redirect('/management/room/index');
        }
        else {
            $form->populate($room->toArray());
            $this->view->form = $form;
        } 
    }

    public function cancelAction()
    {
		$this->_helper->flashMessenger->addMessage('Room addition/Modification cancelled');
		$this->_redirect('/management/room/index');
    }

    private function getForm()
    {
        $form = new Synrgic_Models_Form();
		$types = Array('A'=>'A','B'=>'B','C'=>'C','D'=>'D','E'=>'E');
        $form->addTextField('name', true)
             ->addTextField('description', true)
			 ->addSelectField('type', true,array('_multioptions'=>$types))
             ->addSubmitButton()
             ->addCancelButton()
             ->setFormTemplate('/room/_form.phtml');

        return $form;
    }

}











