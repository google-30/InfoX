<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Noticeboard_ManageController extends Zend_Controller_Action
{

    private $alertRepository = null;
    private $em;

    public function init()
    {
	$this->em = Zend_Registry::get('em');
	$session = Zend_Registry::get(SYNRGIC_SESSION);

	$this->alertRepository = $this->em->getRepository('\Synrgic\Noticeboard\Alert');
    }

    public function indexAction()
    {
	$alerts = $this->alertRepository->findAll();
	$data = array();
	foreach($alerts as $alert)
	{
	    $acknowledged = $alert->getAcknowledged();
	    $item = array();
	    $item['id']=$alert->getId();
	    $item['room']=$alert->getRoom()->getName();
	    $item['category']=$alert->getCategory()->getName();
	    $item['title'] = $alert->getTitle();
	    $item['issued']=$alert->getIssued()->format('Y-m-d H:i:s');
	    $item['acknowledged']=$acknowledged==NULL?"":$acknowledged->format('Y-m-d H:i:s');
	    $data[] = $item;
	}

	$this->view->data = $data;
    }

    public function addAction()
    {
	$form =$this->getForm();
	$req = $this->getRequest();
	$form = $this->getForm()->setMethod('post');
	if($req->getPost() && $form->isValid($req->getPost()))
	{
	    $values = $form->getValues();
	    $category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById($values['category']);
	    if($values['room']=="ALL"){
		$rooms = $this->em->getRepository('\Synrgic\OccupiedRoom')->findAll();
		$this->alertRepository->createAlertForRooms($rooms,$category,$values['title'],$values['message']);
		$this->_helper->flashMessenger->addMessage("Notice Posted To All Rooms");
	    } else {
		$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneById($values['room']);
		$this->alertRepository->createAlertForRoom($category,$room,$values['title'],$values['message']);
		$this->_helper->flashMessenger->addMessage("Notice Posted To Room");
	    }
	    $this->forward('index');
	}
	$this->view->form=$form;
    }

    public function revokeAction()
    {
	$id = $this->_getParam('id');
	if( $id > 0 ){
	    $this->alertRepository->revokeAlert($id); 
	    $this->_helper->flashMessenger->addMessage("Notice Revoked Successfully");
	} else {
	    $this->_helper->flashMessenger->addMessage("The Requested Notice does not exist");
	}
	$this->forward('index');
    }

    private function getForm()
    {
	$form = new Synrgic_Models_Form();

	$roomsList=array();
	$rooms = $this->em->getRepository('\Synrgic\OccupiedRoom')->findAll();

	foreach($rooms as $room){
	    $roomList[$room->getId()]=$room->getName();
	}
	$roomList["ALL"]="All Rooms";

	$categoryList=array();
	$categories = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findAll();

	foreach($categories as $category){
	    $categoryList[$category->getId()]=$category->getName();
	}
	/**
	  To be used later for date selection
	  $element = new ZendX_JQuery_Form_Element_DatePicker('startDate',
	  array('jQueryParams'=> array('dateFormat'=>'yy-mm-dd')));
	  $form->addElement($element);

	  $element = new ZendX_JQuery_Form_Element_DatePicker('endDate',
	  array('jQueryParams'=> array('dateFormat'=>'yy-mm-dd')));
	  $form->addElement($element);
	 **/
	$form->addSelectField('room',true,array('_multioptions'=>$roomList));
	$form->addSelectField('category',true,array('_multioptions'=>$categoryList));
	$form->addTextField('title',true);
	$form->addTextField('message',true)
	    ->addSubmitButton()
	    ->addCancelButton()
	    ->setFormTemplate('/manage/_form.phtml');

	return $form;
    }

}


?>

