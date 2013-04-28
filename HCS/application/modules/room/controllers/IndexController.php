<?php
/*
 * - Copyright (c) 2012 Synrgic Research Pte Ltd All rights reserved
 * Redistribution of this file in source code, or binary form is expressly not
 * permitted without prior written approval of Synrgic Research Pte Ltd
 */

require_once 'Zend/Session/Namespace.php';

class Room_IndexController extends Zend_Controller_Action{
		/*$preState:       |     light:Main Door,Living Room a,Living Room b,Bedroom,Toilet,Bedside,Bathe Room,Table Lamp
		 * Wakeup:         | curtains: Bedroom, Living Room,Main Door,Toilet   
		 * Reading:        |       tv:
		 * Romantic:       |    radio:
		 * Sleep:          |    temp:
		 * Getting Dressed:|
		 * Watching TV:    |
		 * Night:          |
		 * All Lights:     |
		 * */
	private $preState=array(	
			array('light'=>array('flip1'=>'off','flip2'=>'off','flip3'=>'off','flip4'=>'off','flip5'=>'off','flip6'=>'on','flip7'=>'off','flip8'=>'off'),
				  'curtains'=>array('1'=>'on','2'=>'on','3'=>'on','4'=>'on'),
				  'tv'=>array('state'=>'on','channel'=>'11','volume'=>'1','play'=>'on'),
				  'radio'=>array('channel'=>'91','volume'=>'1'),
				  'temp'=>array('state'=>'on','hours1'=>'1','T'=>'24','fan'=>'High','hours2'=>'1'),
				),
			array('light'=>array('flip1'=>'off','flip2'=>'off','flip3'=>'off','flip4'=>'off','flip5'=>'off','flip6'=>'off','flip7'=>'off','flip8'=>'on'),
				  'curtains'=>array('1'=>'on','2'=>'on','3'=>'off','4'=>'off'),
					'tv'=>array('state'=>'off','channel'=>'12','volume'=>'2','play'=>'off'),
					'radio'=>array('channel'=>'92','volume'=>'2'),
					'temp'=>array('state'=>'off','hours1'=>'3','T'=>'22','fan'=>'Medium','hours2'=>'3'),
			),			
			array('light'=>array('flip1'=>'off','flip2'=>'off','flip3'=>'on','flip4'=>'off','flip5'=>'off','flip6'=>'off','flip7'=>'off','flip8'=>'off'),
				  'curtains'=>array('1'=>'off','2'=>'on','3'=>'on','4'=>'off'),
					'tv'=>array('state'=>'off','channel'=>'13','volume'=>'3','play'=>'off'),
					'radio'=>array('channel'=>'93','volume'=>'3'),
					'temp'=>array('state'=>'on','hours1'=>'3','T'=>'23','fan'=>'High','hours2'=>'3'),
			),			
			array('light'=>array('flip1'=>'off','flip2'=>'off','flip3'=>'off','flip4'=>'off','flip5'=>'off','flip6'=>'off','flip7'=>'off','flip8'=>'off'),
				  'curtains'=>array('1'=>'off','2'=>'off','3'=>'off','4'=>'off'),
					'tv'=>array('state'=>'off','channel'=>'14','volume'=>'4','play'=>'off'),
					'radio'=>array('channel'=>'94','volume'=>'4'),
					'temp'=>array('state'=>'on','hours1'=>'4','T'=>'24','fan'=>'Medium','hours2'=>'4'),
			),			
			array('light'=>array('flip1'=>'off','flip2'=>'off','flip3'=>'off','flip4'=>'on','flip5'=>'off','flip6'=>'off','flip7'=>'off','flip8'=>'off'),
				  'curtains'=>array('1'=>'off','2'=>'off','3'=>'off','4'=>'off'),
					'tv'=>array('state'=>'off','channel'=>'15','volume'=>'5','play'=>'off'),
					'radio'=>array('channel'=>'95','volume'=>'5'),
					'temp'=>array('state'=>'off','hours1'=>'5','T'=>'25','fan'=>'High','hours2'=>'5'),
			),			
			array('light'=>array('flip1'=>'off','flip2'=>'on','flip3'=>'off','flip4'=>'off','flip5'=>'off','flip6'=>'off','flip7'=>'off','flip8'=>'off'),
				  'curtains'=>array('1'=>'off','2'=>'on','3'=>'off','4'=>'off'),
					'tv'=>array('state'=>'on','channel'=>'16','volume'=>'6','play'=>'on'),
					'radio'=>array('channel'=>'96','volume'=>'6'),
					'temp'=>array('state'=>'on','hours1'=>'6','T'=>'26','fan'=>'Low','hours2'=>'6'),
			),			
			array('light'=>array('flip1'=>'on','flip2'=>'on','flip3'=>'on','flip4'=>'on','flip5'=>'on','flip6'=>'on','flip7'=>'off','flip8'=>'off'),
				  'curtains'=>array('1'=>'off','2'=>'off','3'=>'off','4'=>'off'),
					'tv'=>array('state'=>'on','channel'=>'17','volume'=>'7','play'=>'on'),
					'radio'=>array('channel'=>'97','volume'=>'7'),
					'temp'=>array('state'=>'off','hours1'=>'7','T'=>'27','fan'=>'High','hours2'=>'7'),
			),				
			array('light'=>array('flip1'=>'on','flip2'=>'on','flip3'=>'on','flip4'=>'on','flip5'=>'on','flip6'=>'on','flip7'=>'on','flip8'=>'on'),
				  'curtains'=>array('1'=>'off','2'=>'off','3'=>'off','4'=>'off'),
					'tv'=>array('state'=>'off','channel'=>'18','volume'=>'on','play'=>'off'),
					'radio'=>array('channel'=>'98','volume'=>'8'),
					'temp'=>array('state'=>'on','hours1'=>'8','T'=>'26','fan'=>'Low','hours2'=>'8'),
			)			
		);
	
    // $session = new Zend_Session_Namespace(SYNRGIC_SESSION);
    private $em;
	private $roomType;
	private $roomId;
	const IDLE_TIME = 1; // In Seconds
	const MAX_TIME  = 120; // In Seconds

    public function init ()
    {
        /* Initialize action controller here */
    	$this->em = Zend_Registry::get('em');

		$session = Zend_Registry::get(SYNRGIC_SESSION);
		$this->roomId = $session->room->physicalRoom->getId();
		$room = $this->em->getReference('\Synrgic\Room', $this->roomId);
		//print_r('<pre>');
		//print_r($session->room);
		//print_r('</pre>');
		//echo $session->room->physicalRoom->getId();
		//exit();
		$this->roomType = $room->getType();

        /* get this room ip2rf gateway ip address. */
        $this->view->GW_ip = "192.168.0.117";
        $this->view->roomid=$this->roomId;
    }

	public function indexAction ()
	{
		// Default to lights
		$this->forward('roomlights','index','room');	
    }

    public function roomlightsAction()
    { 	
			$file = APPLICATION_PATH."/modules/room/views/scripts/index/lights/".$this->roomType.".html";
			if(is_readable($file)==true){
				$this->view->file=$file;
			}
			else{
				$this->view->content = "The lights in this room are not controllable via this device";
			}

			$this->view->state=$this->setStateDB('light');		
    }

    public function roomtvAction()
    {
        //$this->_helper->layout->disableLayout();    
        //$this->_helper->viewRenderer->setNoRender(TRUE);
        
    	$this->view->state=$this->setStateDB('tv');
    }
    
    public function roomcurtainsAction()
    {
    	$this->view->state=$this->setStateDB('curtains');	
    }    
       
    public function roomradioAction()
    {
    	$this->view->state=$this->setStateDB('radio');	
    } 

    public function roomtempAction()
    {  		 
    	$this->view->state=$this->setStateDB('temp'); 
    }

    public function roompresetAction()
    {  	
    	if($this->hasParam('presetid')){
    		$presetid=$this->getParam ( 'presetid' );
    		
    		$json = Zend_Json::encode($this->preState[$presetid]);
    		$state = $this->em->getRepository ( '\Synrgic\RoomControl\State' )->findOneBy ( array ('roomid' => $this->roomId ) );    		
    		if(empty($state))
    		{
    			$state = new \Synrgic\RoomControl\State();
    		}

    		$state->setState($json);
    		$state->setRoomid($this->roomId);
    		$state->setChangeid();
    		$state->setChangeItem('all');
    		$state->setpresetsid($presetid);
    		$this->em->persist ( $state );
    		$this->em->flush (); 		  		
    	}	
    }
    

    public function setstateAction()
    {
    	if ( $this->hasParam('deviceId')){
    		
    		$roomId = $this->getParam('roomid');
    		$deviceId=$this->getParam('deviceId');
    		$Item = $this->getParam('Item');
    		$data=$this->getParam('value');
    		
    		$ctrlstate = $this->em->getRepository ( '\Synrgic\RoomControl\State' )->findOneBy ( array ('roomid' =>$roomId ) );
    		
    		if(empty($ctrlstate))
    		{
    			$ctrlstate = new \Synrgic\RoomControl\State();
    			$date = $this->preState[0];
    		}
    		else{
    			$tmpdata = Zend_Json::decode($ctrlstate->getState());
    		}
    		
    		$tmpdata[$deviceId][$Item]=$data;
    		
    		$json = Zend_Json::encode($tmpdata);
    		
    		$ctrlstate->setState($json);
    		$ctrlstate->setRoomid($roomId);
    		$ctrlstate->setChangeid();
    		$ctrlstate->setChangeItem($deviceId);
    		$ctrlstate->setpresetsid('-1');
    		$this->em->persist ( $ctrlstate );
    		$this->em->flush ();	
    	}
    	exit();
    }
        
    public function dataAction()
    {
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    	
    	$roomid = $_POST['roomid'];
    	$newVersion = $_POST['version'];
    	
    	$state = $this->em->getRepository ( '\Synrgic\RoomControl\State' )->findOneBy ( array ('roomid' => $roomid) );
    	
    	$oldVersion=$state->getChangeid();
   	
/*    	while($newVersion==$oldVersion  && $initial < self::MAX_TIME){
    		$oldVersion=$state->getChangeid();
    		sleep(self::IDLE_TIME);
    		$initial+=self::IDLE_TIME;
    	}*/

    	if($newVersion!=$oldVersion)
    	{
    		$oldVersion=$state->getChangeid();
    		$data['newVersion']=$oldVersion;
    		$data['changeItem']=$state->getChangeItem();
    		$data['presetid']=$state->getpresetsid();
    		$data['state'] = Zend_Json::decode($state->getState());   
    		$json = Zend_Json::encode($data);
    		echo $json;
    	}	
    }

 	private function setStateDB($changeItem){
 		$ctrlstate = $this->em->getRepository ( '\Synrgic\RoomControl\State' )->findOneBy ( array ('roomid' =>$this->roomId ) );
 		
 		if(empty($ctrlstate))
 		{
 			$ctrlstate = new \Synrgic\RoomControl\State();
 			$data = $this->preState[0];
 			
 			$json = Zend_Json::encode($data);
 			
 			$ctrlstate->setChangeItem('all');
 			$ctrlstate->setState($json);
 		}
 		else{
 			$data = Zend_Json::decode($ctrlstate->getState());
 			$ctrlstate->setChangeItem($changeItem);
 		}
 		
 		$ctrlstate->setRoomid($this->roomId);
 		$ctrlstate->setChangeid(); 			
 		$this->em->persist ( $ctrlstate );
 		$this->em->flush ();
 		
 		return $data[$changeItem];
 	}   
}

