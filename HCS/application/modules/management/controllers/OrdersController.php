<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_OrdersController extends Zend_Controller_Action
{
    private $em;
    private $lang;
    private $session;
    private $userID;
    private $orderState;

    public function init()
    {
        /* Initialize action controller here */
        $this->em = Zend_Registry::get('em');
        
        $this->session = Zend_Registry::get(SYNRGIC_SESSION);
        
    	$auth=Zend_Auth::getInstance();
		$user=$auth->getIdentity();
		$this->userID=$user->getId();
		$this->lang = $user->getpreferredLanguage()->getlocale();
		if ($this->lang==NULL){
			$this->_redirect ( "management" );
		}	
		$FoodDrinkState = array('new','cooking','cook finish','transporting','transport finish');
		$ServiceState = array('new','doing','finish');		
		$TaxiState = array('new','called');
		$this->orderState[1]=$FoodDrinkState;
		$this->orderState[2]=$TaxiState;
		$this->orderState[3]=$ServiceState;       
    }

    public function indexAction()
    {       

		$data = $this->em->getRepository('\Synrgic\Service\Confirm_orders')->pageView();
        $occupiedRoom=NULL;
        $categoryName=NULL;
        foreach($data as $r)
        {
        	$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$r->getOccupiedroom_id()));
        	if($room==NULL)
        		continue;
        	$tmproom['guestName']=$room->getGuest()->getName();
        	$tmproom['roomName']=$room->getName();
        	$occupiedRoom[$r->getId()]=	$tmproom;
        	
        	$catlog = $this->em->getReference('\Synrgic\Service\Catalog', $r->getType());
        	$categoryName[$r->getId()]=$catlog->getName();
        	
        	$this->refreshConfOrder($r->getId());
        }

        $this->view->categoryName = $categoryName;             
        $this->view->rooms = $occupiedRoom;
        $this->view->data = $data;
        
        $this->view->confirmOrder=NULL;
        $this->view->serviceName=NULL;
		$id = $this->_getParam('confid');	
		if(($id<>NULL)&&($id>0))
		{
			$confirmOrdersdata = $this->em->getRepository('\Synrgic\Service\Confirm_orders')->findOneBy(array('id'=>$id));
			if(($confirmOrdersdata<>NULL)&&(count($confirmOrdersdata->getDetail_orders())>0))
			{
				$this->view->confirmOrder=$confirmOrdersdata;
				$serviceName=NULL;
				foreach ($confirmOrdersdata->getDetail_orders() as $r) {
					$serviceName[$r->getId()] = $r->getService_name();
					$service = $this->em->getRepository('\Synrgic\Service\Service')->findOneBy(array('id'=>$r->getService_id()));
					if(isset($service))
					{
						$serviceName[$r->getId()] = $service->getTranslateName($this->lang);
					}
				}
				$this->view->delsts = $this->orderState[$confirmOrdersdata->getType()];
				$this->view->serviceName=$serviceName;
			}
		} 
    }
  
    public function addAction() {
        $req = $this->getRequest();
        
        $curTime=new \DateTime('now');        
        $changeType = $this->_getParam ( 'type' );
       
        if($req->getPost()){
			if($this->session->detailOrders!=NULL)
			{
				$room_id=$req->getParam('room_id');
				$ordersData = new \Synrgic\Service\Confirm_orders();
				$ordersData->setRemark($req->getParam('remark'));
				$ordersData->setTotal_price('0.00');
				$ordersData->setConfirm_time($curTime);
				$ordersData->setOccupiedroom_id($room_id);
				$ordersData->setType($changeType);
				if($req->getParam('scheduled_time')<>NULL)
				{
					$ordersData->setScheduled_time(new \DateTime($req->getParam('scheduled_time')));
				}
				else
				{
					$ordersData->setScheduled_time($curTime);
				}
				$this->em->persist($ordersData);				
				$this->em->flush();
								
				$curOrdersId=$ordersData->getId();				
				$event = "Add a confirmed order #$curOrdersId";
				$this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);
								
				//$categoryOrder = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('2');
				$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$room_id));
				
				foreach($this->session->detailOrders as $r)
				{
					$num=$r["quantity"];
					$name=$r["service_name"];
					$book = new \Synrgic\Service\Detail_orders();
				
					$book->setService_id($r["service_id"]);
					$book->setQuantity($num);				 
					$book->setProvider_id($r["provider_id"]);
					$book->setProvider_name($r["provider_name"]);
					$book->setOperate_time($curTime);
					$book->setService_name($name);
					$book->setService_price($r["service_price"]);
										
					$conf_order = $this->em->getRepository('\Synrgic\Service\Confirm_orders')->findOneBy(array('id'=>$curOrdersId));
					$book->setConfirm_orders($conf_order);
					$conf_order->getDetail_orders()->add($book);
					$conf_order->setTotalPrice();										 
					$this->em->persist($conf_order);
					
					$this->em->persist($book);
					$this->em->flush();
				
					$event = "Add $num $name to oder #$curOrdersId";
					$this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);
					
					//$this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($categoryOrder,$room,$name,'status: placed');
					 
				}
				
				$category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('4');
				$this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,'Order No.'.$curOrdersId,'placed');
				
				
				$this->session->detailOrders=NULL;
				$this->displayMsg('Confirm orders added successfully',$ordersData->getId());
				$this->_redirect ( "management/orders/index" );				
			}
			else{
				$this->_redirect ( "management/orders/index" );
			}
        }
        else 
        {	
	        $data = $this->em->getRepository('\Synrgic\Service\Catalog')->getTopCatalogs();

        	$this->view->type = $data;
        	$this->view->curTime=$curTime;
     	
        	$rooms=$this->em->getRepository ( '\Synrgic\OccupiedRoom')->findAll();
        	$this->view->rooms=$rooms;
        	       	
        	if($changeType<>NULL)
        	{
        		$this->view->curType=$changeType; 		              
        	}
        	else{
        		$this->view->curType='1';
        	}
        	$servicesData=$this->em->getRepository('\Synrgic\Service\Service')->getSameTypeServices($this->view->curType);
        	$this->view->services=$servicesData;
        	$this->view->lang = $this->lang;
        	        	 	
        	$serviceId=$this->_getParam ( 'addservice_id' );
        	$quantity=$this->_getParam ( 'quantity' );
        	  	
        	if($serviceId<>NULL)
        	{
        		$tmpService = $this->em->getRepository ( '\Synrgic\Service\Service' )->findOneBy ( array ('id' => $serviceId ) );
        		
        		$tmp['translate_name'] =$tmpService->getTranslateName($this->lang);      		        		
        		$tmp['service_id']=$serviceId;
        		$tmp['service_name']=$tmpService->getName();
        		$tmp['service_price']=$tmpService->getPrice();
        		$tmp['service_type']=$tmpService->getType();
        		$tmp['provider_id']=$tmpService->getProvider()->getId();
        		$tmp['provider_name']=$tmpService->getProvider()->getName();
        		$tmp['quantity']=$quantity;        		
        		$this->session->detailOrders["$serviceId"]=$tmp;       			 
        		$this->view->detailData=$this->session->detailOrders;
        	}
        	else
        	{        		
        		$this->session->detailOrders=NULL;
        	}  	
        }
    }

    public function editAction() {
        $req = $this->getRequest(); 
        $id = $this->_getParam('confid');
        if($req->getPost()) {
        	$book = $this->em->getReference('\Synrgic\Service\Confirm_orders', $id);
            $book->setRemark($req->getParam('remark'));
            $book->setScheduled_time(new \DateTime($req->getParam('scheduled_time')));

            $this->em->persist($book);
            $this->em->flush();

            $event = "Edit confirmed order #$id";
            $this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);
                       
            $this->displayMsg('Confirm orders updated successfully',$book->getId());   
            $this->_redirect ( "management/orders/index/" );
        }
        else {
        	$this->view->orderId=$id;
	        $tmp=$this->em->getReference('\Synrgic\Service\Confirm_orders', $id);
	        $this->view->data = $tmp;
	        $tmproom=NULL;

	        $room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$tmp->getOccupiedroom_id()));
	        if($room<>NULL)
	        {
	        	$tmproom['guestName']=$room->getGuest()->getName();
	        	$tmproom['roomName']=$room->getName();
	        }
	        $this->view->rooms = $tmproom;
        }
    }

    public function deleteAction() {
        $id = $this->_getParam('confid');
               
        if($id>0) {
            $book = $this->em->getRepository('\Synrgic\Service\Confirm_orders')->findOneBy(array('id'=>$id));
            if($book==NULL)
            {
            	$this->_redirect ( "management/orders/index/" );
            }
            else{
            	$this->displayMsg('Confirm orders deleted successfully',$book->getId());
            	
            	$this->em->remove($book);
            	$this->em->flush();
            	
            	$event = "Delete confirmed order #$id";
            	$this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);

            	$this->_redirect ( "management/orders/index/" );          	
            }

        }
        else {
            $this->_redirect ( "management/orders/index/" );
        }
    }
        
    public function deleteDetailAction() {
    	$id = $this->_getParam('id');
    	$conf_id = $this->_getParam('confid');
    	if($id>0) {
	
    		$book = $this->em->getRepository('\Synrgic\Service\Detail_orders')->findOneBy(array('id'=>$id));
    		if($book==NULL)
    		{
    			$this->_redirect ( "management/orders/index/" );
    		}
    		else {
    			$book->setConfirm_orders(NULL);
    			$this->em->remove($book);    				
    		   			
    			$this->em->flush();
    			$detailIsNull=$this->refreshConfOrder($conf_id);
    			
    			$event = "Delete detail order #$id of oder #$conf_id";
    			$this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);
	
    			if($detailIsNull)
    			{
    				$this->displayMsg('Confirm orders deleted successfully',$conf_id);
    				$this->_redirect ( "management/orders/index/" );
    			}else{
    				$this->displayMsg('Detail orders delete successfully',$id);
    				$this->_redirect ( "management/orders/index/confid/$conf_id" );
    			}			
    		}
    	}
    	else {
    		$this->_redirect ( "management/orders/index/" );
    	}
    } 
    
    public function addDetailAction() {
    	
    	$req = $this->getRequest();
    	$conf_id = $this->_getParam('confid');
    	
    	if($req->getPost()) {
    		$sid = $req->getParam('service_id');
    		$num = $req->getParam('quantity');
    		
    		$services = $this->em->getReference('\Synrgic\Service\Service', $sid);
    		$name = $services->getName();
    		
    		$data = $this->em->createQuery("SELECT b FROM \Synrgic\Service\Detail_orders b WHERE b.confirm_orders=$conf_id AND b.service_id=$sid")->getResult();

    		if($data<>NULL)
    		{
    			$detailId=$data[0]->getId();   			
    			$this->displayMsg('Detail_orders is exist',$detailId);
    			$this->_redirect ( "management/orders/index/confid/$conf_id" );
    		}
    		else 
    		{
    			$book = new \Synrgic\Service\Detail_orders();
    			
    			$book->setService_id($sid);
    			$book->setRemark($req->getParam('remark'));
    			
    			$book->setQuantity($num);
    			
    			$book->setProvider_id($services->getProvider()->getId());
    			$book->setOperate_time(new \Datetime('now'));
    			
    			$book->setService_name($name);
    			$book->setService_price($services->getPrice());
    			
    			$provider = $this->em->getRepository ( '\Synrgic\Service\Provider')->findOneBy(array('id'=>$services->getProvider()->getId()));
    			$book->setProvider_name($provider->getName());
    			
    			$conf_order = $this->em->getRepository('\Synrgic\Service\Confirm_orders')->findOneBy(array('id'=>$conf_id));
    			$book->setConfirm_orders($conf_order);
    			$conf_order->getDetail_orders()->add($book);    			
    			$conf_order->setTotalPrice();
    			$oldState=$conf_order->getState();
    			$conf_order->refreshState();
    			$newState=$conf_order->getState();
    			$this->em->persist($conf_order);
    			
    			$this->em->persist($book);
    			$this->em->flush();
    			
    			$detailId=$book->getId();
    			
    			$event = "Add $num $name to oder #$conf_id";
    			$this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);
    			
    			$this->displayMsg('Detail_orders added successfully',$detailId);
    			
    			if($oldState<>$newState)
				{
					$category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('2');
					$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$book->getConfirm_orders()->getOccupiedroom_id()));
					$this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,"Order NO.".$conf_order->getId(),$newState);
				}
    			$this->_redirect ( "management/orders/index/confid/$conf_id" );
    			
    		}
    	}
    	else {
    		
    		$this->view->orderId=$conf_id;
    		
    		$servicesData=$this->em->getRepository('\Synrgic\Service\Service')->getSameTypeServices("1");
    		$this->view->lang = $this->lang;
    		$this->view->services=$servicesData;
    	}
    }    
    
    public function editDetailAction() {
    	$req = $this->getRequest();    	
    	
    	
    	$conf_id = $this->_getParam('confid');
	
    	$id= $this->_getParam('id');
    	$book = $this->em->getReference('\Synrgic\Service\Detail_orders', $id);
    	
    	if($req->getPost()) {
    			$state=$req->getParam('order_state');
				$book->setRemark($req->getParam('remark'));
				$num = $req->getParam('quantity');
				$book->setQuantity($num);
				$book->setState($state);
				$book->setOperate_time(new \Datetime('now'));				
				$book->getConfirm_orders()->setTotalPrice();
				
				$oldState=$book->getConfirm_orders()->getState();
				$book->getConfirm_orders()->refreshState();
				$newState=$book->getConfirm_orders()->getState();
				$this->em->persist($book);
				$this->em->flush();
				
				if($oldState<>$newState)
				{
					$category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('2');
					$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$book->getConfirm_orders()->getOccupiedroom_id()));
					$this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,"Order NO.".$book->getConfirm_orders()->getId(),$newState);
				}				
							
				$event = "Edit detail order #$id of oder #$conf_id";
				$this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);
				
				$this->displayMsg('Detail_orders updated successfully',$id);
				$this->_redirect ( "management/orders/index/confid/$conf_id" );
    	}
    	else {
    		$this->view->orderId=$conf_id;
    			
    		$serviceName[$book->getId()] = $book->getService_name();
    		$service = $this->em->getRepository('\Synrgic\Service\Service')->findOneBy(array('id'=>$book->getService_id()));    			
    	   	if(isset($service))
    		{
    			$serviceName[$book->getId()] = $service->getTranslateName($this->lang);
    		}    		   
    		$this->view->serviceName=$serviceName;    			
    			
    		$this->view->data = $book;

			$this->view->sts = $this->orderState[$book->getConfirm_orders()->getType()];
			
    		$pvd = $this->em->getRepository('\Synrgic\Service\Provider')->findAll();
    		$this->view->provider = $pvd;
    	}
    }
    
    private function refreshConfOrder($confid){
    	$conf_order = $this->em->getReference('\Synrgic\Service\Confirm_orders', $confid);
    	
    	$data=$conf_order->getDetail_orders();
    	$detailCount=0;
    		
    	if(count($data)==0)
    	{
    	    $this->em->remove($conf_order);
    	    $detailCount=1;
    	}
        else
        {
        	$conf_order->setTotalPrice();
        	$oldState=$conf_order->getState();
        	$conf_order->refreshState();
        	$newState=$conf_order->getState();
            $this->em->persist($conf_order);
            
            if($oldState<>$newState)
            {
            	$category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('2');
            	$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$conf_order->getOccupiedroom_id()));
            	$this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,"Order NO.".$conf_order->getId(),$newState);
            }
            
        }
    	
    	$this->em->flush();
    	
    	return $detailCount;
    }
        
    
    public function displayMsg($msg,$id) {
    	$message=$this->view->translate($msg);
    	$this->_helper->flashMessenger->addMessage( $message.': id='.$id);
    }

}

