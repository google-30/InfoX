<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_DetailstateController extends Zend_Controller_Action
{
	const pageSize=20;
    private $em;
    private $lang;
    private $session;
    private $userID;
    private $orderState;
    private $userProviderId;
    private $role;

    public function init()
    {
        /* Initialize action controller here */
        $this->em = Zend_Registry::get('em');
        
        $this->session = Zend_Registry::get(SYNRGIC_SESSION);
        
    	$auth=Zend_Auth::getInstance();
		$user=$auth->getIdentity();
		$this->userID=$user->getId();
		$this->role=$user->getRole();
		$this->lang = $user->getpreferredLanguage()->getlocale();
		if ($this->lang==NULL){
			$this->_redirect ( "management" );
		}	

		$FoodDrinkState = array('new','cooking','cook finish','transporting','transport finish');
		$ServiceState = array('new','doing','finish');		
		$TaxiState = array('new','wating','called');
		$this->orderState[1]=$FoodDrinkState;
		$this->orderState[2]=$TaxiState;
		$this->orderState[3]=$ServiceState;	

		
		$currentUser = $this->em->getRepository ( '\Synrgic\User' )->findOneBy ( array ('id' => $this->userID) );
		 
		$provider=$currentUser->getProvider();
		if($provider<>NULL)
			$this->userProviderId=$provider->getId();
		
		
    }

    public function indexAction()
    {       
    	
    	
    	if(($this->userProviderId<>NULL)&&($this->role=='staff'))
    	{
    		
    		$providerId=$this->userProviderId;
    	}
    	else if(($this->userProviderId==NULL)&&($this->role!='admin'))
    	{
    		$this->_redirect ( "management" );
    	}
    	else{
    		$providerId=-1;
    	}
    	
        
        
        $this->view->providerId = $providerId;
		$curPage=$this->getCurPage($this->getParam ( 'page' ),$providerId);
		$this->view->page = $curPage;
		$this->view->endpage = $this->getEndPage($providerId);
		
		$this->view->detailData=NULL;
		$serviceName=NULL;
		$occupiedRoom=NULL;
		if($providerId=='-1')
		{
			$detailData=$this->em->getRepository('\Synrgic\Service\Detail_orders')->pageAll($curPage,self::pageSize);
		}	
		else
		{
			$detailData=$this->em->getRepository('\Synrgic\Service\Detail_orders')->pageOfProvider($curPage,self::pageSize,$providerId);
		}
		
		if($detailData<>NULL)
		{
			foreach($detailData as $r)
			{
				$serviceName[$r->getId()] = $r->getService_name();
				$service = $this->em->getRepository('\Synrgic\Service\Service')->findOneBy(array('id'=>$r->getService_id()));
				if(isset($service))
				{
					$serviceName[$r->getId()] = $service->getTranslateName($this->lang);
				}
								
				$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$r->getConfirm_orders()->getOccupiedroom_id()));
				$tmproom['guestName']=$room->getGuest()->getName();
				$tmproom['roomName']=$room->getName();
				$occupiedRoom[$r->getId()]=	$tmproom;
				
			}
				
			$this->view->detailData=$detailData;
			$this->view->sts = $this->orderState;
		}
		$this->view->rooms = $occupiedRoom;
		$this->view->serviceName=$serviceName;
		
		 
		$detailId=$this->getParam ( 'detailId' );
		$tmpstate=$this->getParam('orderState');

		if(($detailId<>NULL)&&($tmpstate<>NULL)) {			
			$detail = $this->em->getReference('\Synrgic\Service\Detail_orders', $detailId);
			if(isset($detail)){
				$state=$this->getParam('orderState');
				$detail->setOperate_time(new \Datetime('now'));
				$detail->setState($state);
				$oldState=$detail->getConfirm_orders()->getState();
				$detail->getConfirm_orders()->refreshState();
				$newState=$detail->getConfirm_orders()->getState();
				$this->em->persist($detail);
				$this->em->flush();
								
				if($oldState<>$newState)
				{
					$category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('2');
					$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$detail->getConfirm_orders()->getOccupiedroom_id()));
					$this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,"Order NO.".$detail->getConfirm_orders()->getId(),$newState);
				}
								
				$confirm_id=$detail->getConfirm_orders()->getId();
				$event = "detail order #$detailId of oder #$confirm_id state updated";
				$this->em->getRepository('\Synrgic\Service\Operate_record')->addOperationLog($this->userID, $event);
				
				//$this->displayMsg('Detail_orders state updated successfully',$detailId);
				//$this->_redirect ( "management/detailstate/index/providerId/$providerId/page/$curPage" );	
				$this->_redirect ( "management/detailstate/index/page/$curPage" );
			}
		}
    }
        
    public function getCurPage($curPage,$providerId) {
    	$totalCount =count($this->em->getRepository ( '\Synrgic\Service\Detail_orders' )->findBy(array('provider_id'=>$providerId))) ;
    
    	if ((! $curPage) || ($curPage <= 0)) {
    		$curPage = 0;
    	}
    
    	$start = $curPage * self::pageSize;
    	if(($start == $totalCount)&&($totalCount>0)) {
    		$curPage=$totalCount/self::pageSize-1;
    	}
    	return $curPage;
    		
    }
    public function getEndPage($providerId) {
    	$totalCount =count($this->em->getRepository ( '\Synrgic\Service\Detail_orders' )->findBy(array('provider_id'=>$providerId))) ;
    	if($totalCount>self::pageSize)
    	{
    		$page=$totalCount/self::pageSize-1;
    		$page=ceil($page);
    	}
    	else{
    		$page=0;
    	}
    	return $page;
    }
/*   
    public function displayMsg($msg,$id) {
    	$message=$this->view->translate($msg);
    	$this->_helper->flashMessenger->addMessage( $message.': id='.$id);
    }
*/
}

