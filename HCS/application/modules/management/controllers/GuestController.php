<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_GuestController extends Zend_Controller_Action
{

    private $em;
    private $deviceRepo;
    private $session;

    public function init()
    {
	$this->em = Zend_Registry::get('em');
    }

    public function indexAction()
    {
	$data = $this->em->getRepository('\Synrgic\Guest')->findAll();

	$this->view->data = $data;
    }

    public function addAction()
    {
	$req = $this->getRequest();
	$form = $this->getForm();
	if($req->getPost() && $form->isValid($req->getPost())) {
	    $values=$form->getValues();
	    $values['preferredLanguage'] = $this->em->getRepository('\Synrgic\Language')->findOneByLocale( $values['preferredLanguage']);
	    $guest = new \Synrgic\Guest();
	    $guest->fromArray($values);
	    $this->em->persist($guest);
	    $this->em->flush();
	    $this->_helper->flashMessenger->addMessage('Guest' .  $guest->getName() .  ' Added');
	    $this->_forward('index');
	}
	else {
	    $this->view->form = $form;
	} 
    }

    public function editAction()
    {

	$req = $this->getRequest();
	$id = $this->_getParam('id');
	$guest = $this->em->getReference('\Synrgic\Guest', $id);
	$form = $this->getForm();
	if($req->getPost() && $form->isValid($req->getPost())) {
	    $values=$form->getValues();
	    $values['preferredLanguage'] = $this->em->getRepository('\Synrgic\Language')->findOneByLocale( $values['preferredLanguage']);
	    $guest->fromArray($values);
	    $this->em->persist($guest);
	    $this->em->flush();
	    $this->_helper->flashMessenger->addMessage('Guest' .  $guest->getName() .  ' modified successfully');
	    $this->_forward('index');
	}
	else {
	    $this->view->form = $form;

	    $values = $guest->toArray();
	    $values['preferredLanguage']=$values['preferredLanguage']->getLocale();
	    $form->populate($values);
	} 

    }

    public function deleteAction()
    {
	if( $this->_hasParam('id')){
	    $id = $this->_getParam('id');
	    $guestRepo = $this->em->getRepository('\Synrgic\Guest');
	    $guestRepo->deleteById($id);
	    $this->_helper->flashMessenger->addMessage('Guest removed');
            $this->_forward('index');
        }
        else {
            $this->_forward('index');

	}
    }

    public function cancelAction()
    {
	$this->forward('index','guest','management');
    }

    public function checkinAction()
    {
	$guestRepo = $this->em->getRepository('\Synrgic\Guest');
	$req = $this->getRequest();
	$id = $this->_getParam('id');
	$guest = $this->em->getReference('\Synrgic\Guest', $id);
	$form = $this->getCheckinForm($guest);

	if($req->getPost() && $form->isValid($req->getPost())) {
	    $guest = $guestRepo->findOneById($id);
	    $room = $this->em->getReference('\Synrgic\Room',$this->getParam('room'));
	    $rooms = array();
	    $rooms[] = $room;
	    $guestRepo->checkin($guest,$rooms); 
	    $this->_helper->flashMessenger->addMessage($this->view->translate('Guest %s Checked in'), $guest->getName());
	    $this->_forward('index');
	}
	$this->view->form = $form;
    }

    public function checkoutAction()
    {
	$id = $this->_getParam('id');
	$guestRepo = $this->em->getRepository('\Synrgic\Guest');
	$guest = $guestRepo->findOneById($id);
	$guestRepo->checkOut($guest);
	$this->_helper->flashMessenger->addMessage($this->view->translate('Guest %s checkedout'), $guest->getName());
	$this->_forward('index');
    }

    private function getForm()
    {
	$form = new Synrgic_Models_Form();
	$form->addTextField('name', true)
	    ->addTextField('pin',true)
	    ->addSubmitButton()
	    ->addCancelButton()
	    ->setFormTemplate('/guest/_form.phtml');

	$languageList = $this->em->getRepository('\Synrgic\Language')->getLocaleToNameList();        
	$form->addSelectField('preferredLanguage',true,
			      array('_multioptions'=>$languageList));


	return $form;
    }

    private function getCheckinForm($guest)
    {
	$form = new Synrgic_Models_Form();

	$field = new Zend_Form_Element_Text('name');
	$field->setValue($guest->getName());
	$field->setAttrib('disabled','disabled');

	$form->addElement($field)
	    ->addSubmitButton()
	    ->addCancelButton()
	    ->setFormTemplate('/guest/_checkInForm.phtml');
	
	// Find all rooms not currently occupied
	// XXX This should probably be in the model - Benjsc 20130228
	$allRooms = $this->em->getRepository('\Synrgic\Room')->findAll();
	$usedRooms = $this->em->getRepository('\Synrgic\OccupiedRoom')->findAll();
	$availableRooms = array();

	foreach( $allRooms as $room ){
	    $found = false;
	    foreach($usedRooms as $usedRoom ){
		if( $room->getName() == $usedRoom->getName()){
		    $found = true;
		}
	    }
	    if( !$found){
		$availableRooms[] = $room;
	    }
	}


	$roomList=array();
	foreach($availableRooms as $room){
	    $roomList[$room->getId()]=$room->getName();
	}

	$field = new Zend_Form_Element_Select('room');
	$field->setMultiOptions($roomList);
	$form->addElement($field);

	return $form;
    }

}
