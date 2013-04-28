<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_DevicesController extends Zend_Controller_Action
{

    private $em;
    private $deviceRepo;
    private $session;

    public function init()
    {
	$this->em = Zend_Registry::get('em');
	$this->deviceRepo = $this->em->getRepository('\Synrgic\Device');
	$this->session = Zend_Registry::get(SYNRGIC_SESSION);
    }


    public function indexAction()
    {
	$this->forward('list');
    }

    public function pairrequestAction()
    {
	// Show
	// * Device Information
	//   *  Device Type
	//      * Device Association
	//   *  Unique ID Token
	//   *  IP 
	//   *  Software version
	// * Device Purpose
	//   * Advert Display
	//   * Guest Machine
	//     * Room Association 

	$deviceType = \Synrgic\Device::probeDeviceType();
	$this->view->deviceip = $_SERVER['REMOTE_ADDR'];
	$this->view->devicetype = \Synrgic\Device::convertDeviceTypeToString($deviceType);
	$this->view->form = $this->getPairingForm($deviceType);
    }

    public function listAction()
    {
	$data = $this->em->getRepository('\Synrgic\Device')->findAll();

	$this->view->data = $data;
    }

    public function deleteAction()
    {
	$id = $this->_getParam('id');
	if($id>0) {
	    $this->deviceRepo->deleteDevice($id);
	    $this->_helper->flashMessenger->addMessage('Device removed');
	    $this->_redirect('/management/devices/');
	}
	else {
	    $this->_forward('index');
	}
    }

    public function cancelAction()
    {
	$this->_helper->flashMessenger->addMessage('Device addition/Modification cancelled');
	$this->_forward('list','devices','management');
    }

    public function editAction()
    {
	$req = $this->getRequest();
	$form = $this->getForm();
	$id = $this->_getParam('id');
	$device = $this->em->getReference('\Synrgic\Device',$id);
	$existing = $this->deviceRepo-> findOneBy(array( 'name'=>$form->getValue('name')));
	if($req->getPost() && $form->isValid($req->getPost())) {
	    if( isset($existing)){
		$this->view->form = $form;
		$this->_helper->flashMessenger->addMessage('Device already exists');
		$this->_redirect('/management/devices/add');
	    } 
	    $device->fromArray($form->getValues(true));
	    $this->em->persist($device);
	    $this->em->flush();
	    $this->_helper->flashMessenger->addMessage('Device ' .  $device->getName() .  ' modified');
	    $this->_forward('list');
	}
	else {
	    $this->view->form = $form;
	    $values = $device->toArray();
	    $form->populate($values);
	} 
    }

    /**
     * Handle pairing a device, this sets up the association
     */
    public function pairAction()
    {
	$form = $this->getPairingForm();
	$request = $this->getRequest();

	if($request->getPost() && $form->isValid($request->getPost())){

	    $device = new \Synrgic\Device();
	    $device->setUniqueid( $form->getValue('uniqueid'));
	    $device->setDescription($form->getValue('description'));
	    $device->setDeviceType($form->getValue('deviceType')); 
	    $device->setName($form->getvalue('name'));
	    $device->setSessionID(session_id());
	    $this->em->persist($device);

	    /*
	     * Device group support will be readded later - benjsc 20130217
	    $devicegroups = $this->em->getRepository('\Synrgic\DeviceGroup')->findOneByName($form->getValue('type'));
	    $devicegroups->getDevices()->add($device);
	    $this->em->persist($devicegroups);
	     */

	    //
	    // Indicate the room this device belongs too
	    //
	    $room = $this->em->getRepository('\Synrgic\Room')->findOneByName($form->getValue('room'));
	    $device->setRoom($room);
	    $this->em->persist($device);
	    $this->em->flush();

	    //
	    // Set the persistent cookie on the device
	    //
	    setCookie(DEVICE_PERSIST_COOKIE, $device->getUniqueid(), time() + (20*365*24*60*60), '/');

	    // Logout the user
	    // XXX This should probably be moved into the model -benjsc 20121107
	    Zend_Auth::getInstance()->clearIdentity();  
	    Zend_Session::forgetMe();

	    // Redirect back to the initial page
	    $this->redirect('/');
	} 

	// Redirect back to the pairing page
	$this->forward('pairrequest','devices','management');
    }

    public function resetAction()
    {
	$id = $this->_getParam('id');

	$deviceRepository=$this->em->getRepository('\Synrgic\Device');
	$deviceRepository->resetDeviceById($id);
	$this->_helper->flashMessenger->addMessage('Device Reset');
	$this->forward('index');

    }

    public function validateeditformAction()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);    
        $this->_helper->layout->disableLayout();

	$form = $this->getForm();
	$form->isValid($this->_getAllParams());
	$json = $form->getMessages();
	header('Content-type: application/json');
	echo Zend_Json::encode($json);
    }

    private function getForm()
    {
	$form = new Synrgic_Models_Form();

	$form->addTextField('name', true)
	    ->addTextField('description', false)
	    ->addTextField('devicetype', false)
	    ->addSubmitButton()
	    ->addCancelButton()
	    ->setFormTemplate('/devices/_form.phtml');

	$form->addSelectField('deviceType',true,array('_multioptions'=>\Synrgic\Device::getDeviceTypeList()));

	return $form;
    }


    

    private function getPairingForm($deviceType=0)
    {
	$uniqueid=1;
	$form = new Synrgic_Models_Form();

	$rooms = $this->em->getRepository('\Synrgic\Room')->findAll();
	// Convert rooms to form elements:

	$allrooms = array();
	foreach( $rooms as $room ){
	    $allrooms[$room->name]=$room->name;
	}

	$devicegroups = $this->em->getRepository('\Synrgic\DeviceGroup')->findAll();
	// Convert groups to form elements:
	$devicepurpose=array();
	foreach( $devicegroups as $devicegroup ){
	    $devicepurpose[$devicegroup->name]=$devicegroup->name;
	}

	$device = $this->em->getRepository('\Synrgic\Device');
	$uniqueid = $device->generateUniqueID();

	$form->addTextField('name',true);
	$form->addTextField('description',false);
	$form->addField('deviceType','Hidden',true, array ('value'=>$deviceType));
	$form->addSelectField('room',true,array('_multioptions'=>$allrooms));
	$form->addField('uniqueid','Hidden',true,array('value'=>$uniqueid));
	$form->setAction('/management/devices/pair')  
	    ->setFormTemplate('/devices/_pairingForm.phtml')
	    ->setMethod('post')  
	    ->addSubmitButton('submit','Pair');

	$form->getElement('name')->setValue("Device 1XXX");

	return $form;
    }

    private function setState($state, $requests)
    {
        if(0) 
        {    
            var_dump($requests);
            return;
        }   

        foreach($requests as $key => $value)
        {
            if($value == 'true')
            {
                $entryobj = $this->deviceRepo->findOneBy(array('id'=>$key));
                if(isset($entryobj))
                {                    
                    try {    
                    $entryobj->setState($state);
                    $this->em->persist($entryobj);
                    }
                    catch (Exception $e)
                    {
                        var_dump($e);
                    }                    
                }

            } 
        }
        $this->em->flush();
    }

}











