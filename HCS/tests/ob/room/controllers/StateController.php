<?php

class Room_StateController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    { 	
		$session = Zend_Registry::get(SYNRGIC_SESSION);
		if(!isset($session->roomid)){
			// XXX indicate error
			// exit();
		}

		// Find out which device was queried
		$device = $this->getRequest()->getParam('device',null);
		if( $device === null ){
			// XXX indicate error
			exit();
		}

		// XXX Perform actual query to backend system
		// Hack below to give some data
		$data = array('devices'=>array(
						array('name'=>'light1', 'state'=>'on'),
						array('name'=>'light2', 'state'=>'off')
						)
						);

		// Output JSon to caller
		$this->getHelper('json')->sendJson($data);
    }


}

