<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Service_IndexController extends Zend_Controller_Action
{
	private $em;
	private $roomId;
	private $roomName;
	private $locale;
	private $confirm_order_repo;
	private $detail_order_repo;
	private $list_type;
	
	public function init()
	{
		/*Initializeactioncontrollerhere*/
		 $this->em=Zend_Registry::get('em');
		 $this->confirm_order_repo= $this->em->getRepository('Synrgic\Service\Confirm_orders');
		 $this->detail_order_repo= $this->em->getRepository('Synrgic\Service\Detail_orders');
		 $session=Zend_Registry::get(SYNRGIC_SESSION);
		//Zend_Debug::dump($session->guest);
		//echo'<br/>room:<br/>';
		//Zend_Debug::dump($session->room);
		
		 $this->roomId= $session->room->getId();
		 $room = $this->em->getReference('\Synrgic\OccupiedRoom', $this->roomId);	
		 $this->roomName=$room->getName();
		 
		 //echo $this->roomName;
		 //exit();
		 //$this->locale=$session->guest->getPreferredLanguage()->getLocale();
		$this->locale=$session->language;
		if(!isset($this->roomId)||!isset($this->locale)){
			 $this->_redirect("/welcome/");
		}
		if(!isset($session->list_type)){
		 $session->list_type=1;
		}
	}

	public function indexAction()
	{

        $id=$this->getParam('id');

        // Use case specific services
        //HACK: This should be removed and done correctly - benjsc 20130307
        switch ($id){
            case 1: $this->_redirect('/service/index/catalog/');break;//forward('catalog','index','service'); break;
            case 2: $this->forward('taxi','index','service'); break;
            default:
                    // Everything else
                    $this->forward('other','index','service');
        }

	}

	private function getCatalogs()
	{
		$categorys= $this->em->getRepository('\Synrgic\Service\Catalog')->getroot();
		foreach($categorys as $category)
		{
			$tmp['id']= $category->getId();
			$tmp['name']= $category->getName();
			foreach($category->getTranslate() as $t)
			{
				if($t->getLanguage()== $this->locale)
				{
					$tmp['name']= $t->getName();
				}
			}
			$displayData[]= $tmp;
		}
		return $displayData;
	}
	
	public function catalogAction()
	{
		$session=Zend_Registry::get(SYNRGIC_SESSION);
		$categories = $this->getCatalogs();
		$cid= $this->getParam('cid',0);
		$id= $this->getParam('id',null);
		$key= $this->getParam('searchItem',null);
		$type= $this->getParam('type',null);
		$listType= $this->getParam('listType',null);
		/*if(empty($cid))
		{
			if(empty($key))
			{
				$list= $this->em->getRepository('\Synrgic\Service\Service')->searchTopGoods();
				
			}else{
				//if(isset($type)){
				$type=1;
				$list= $this->em->getRepository('\Synrgic\Service\Service')->searchByKeyAndCatalog($key,$type);	
				//$list= $this->em->getRepository('\Synrgic\Service\Service')->searchByKey($key);
				//}
			}
		// $this->view->list= $list;
		}
		else
		{
			$list= $this->em->getRepository('\Synrgic\Service\Service')->searchByCatalog($cid);
		// $this->view->list= $list;
		}*/
		
		if(empty($cid))
		{
			if(empty($key))
			{
			 $list= $this->em->getRepository('\Synrgic\Service\Service')->searchTopGoods();
			}else{
				//if(isset($type)){
				 $type=1;
				 $list= $this->em->getRepository('\Synrgic\Service\Service')->searchByKeyAndCatalog($key, $type);	
				// $list= $this->em->getRepository('\Synrgic\Service\Service')->searchByKey($key);
				//}
			}
		}
		else
		{
		 $list= $this->em->getRepository('\Synrgic\Service\Service')->searchByCatalog($cid);
		}
		$this->view->list= $list;
		
		if(!empty($list))
		{
			foreach($list as $item)
			{
				 $tmp['id']= $item->getId();
				 $tmp['price']= $item->getPrice();
				 $tmp['org_picture']= $item->getorg_picture();
				 $tmp['icon']= $item->geticon();

				 $tmp['name']= $item->getName();
				 $tmp['introduction']= $item->getIntroduction();
				 //$tmp['name']= $item->getTranslateName($this->locale);
				 //$tmp['introduction']=$item->getTranslateIntroduction($this->locale);
				 $tmp['remark']= $item->getRemark();
				/*foreach($item->getTranslate() as $t)
				{
					if($t->getLanguage()== $this->locale)
					{
						$tmpstr= $t->getName();
						$tmpstr= $t->getName();
						foreach($item->getTranslate() as $t)
						{
						if($t->getLanguage()== $this->locale)
							{
							$tmpstr= $t->getName();
							if(!empty($tmpstr))
							$tmp['name']= $tmpstr;
							$tmpstr= $t->getIntroduction();
							if(!empty($tmpstr))
							$tmp['introduction']= $tmpstr;
							//$tmpstr= $t->getRemark();
							//if(!empty($tmpstr))
							//$tmp['remark']= $tmpstr;
							}
						}
					}
					
				}*/
				$date_start=$item->getStarttime();
				$date_end=$item->getStoptime();
				if(!empty($date_start))
				{
				echo strtotime('now')-$date_start->getTimestamp().'<br>';
				}
				if(!empty($date_end))
				{
				echo $date_end->getTimestamp()-strtotime('now').'<br>';
				}
				$displayList[]= $tmp;
			}
			$this->view->list2= $displayList;
		
			// NOTE: XXX, dtliu
			// here $id actually is the index (1-base) of $list (happen to equal to $item->id)
			// @see serviceList.phtml
			$count = count($list);
			if($id>=1 && $id<=$count) {
				$id= $id-1;
			}
			else
			{
			    //$id=0;
			    $id = rand(0, $count-1);
			}
			    	
			if(isset($listType))
			{			
				$session->list_type= $listType;
			}
			$this->view->item= $list[$id];
			$this->view->item2= $displayList[$id];
			$this->view->listType= $session->list_type;
		    $this->view->categorys= $categories;
		    $this->view->selected = $cid;

			//itemcount 
			$this->view->itemcount=$this->itemcount();
		}
	}
	//主要功能为添加新的order，或者原有order上添加数量
	public function orderAdd()
	{
		$id= $this->getParam('id',null);
		$num= $this->getParam('num',null);
		$setnum= $this->getParam('setnum',null);
		$note= $this->getParam('note',null);
		if(empty($num))
		{
			$num=1;
		}
		//检查该ID对应的商品是否存在
		$item= $this->em->getReference('\Synrgic\Service\Service', $id);
		if(empty($item))
		{
		echo"errorid". $id;
		exit();
		}
		//
		$global=Zend_Registry::get(SYNRGIC_SESSION);

		$orders= empty($global->orders)?array():$global->orders;
		//print_r("<pre>");
		//print_r($orders);
		//print_r("</pre>");
		
		$neworder=array();
		$neworder['note']="Normal";
		if(!empty($setnum)){
			 $neworder['num']= $setnum;			
		}else{
			if(!empty($orders[ $id])){
				 $neworder['num']= $orders[$id]['num']+ $num;
				 if($neworder['num']<1){$neworder['num']=1;}
				 $neworder['note']= $orders[$id]['note'];
			}else{
				 $neworder['num']= $num;				
			}
		}
		if(!empty($note)){
			 $neworder['note']= $note;
		}
		$orders[$id]= $neworder;
		
		$global->orders= $orders;		
		
		//echo "1 item added, ".$count." items in order!";
		//echo $this->view->translate('1 item added,').$this->view->translate('items of order:').$count;
		echo $this->itemcount();
		exit();
	}
	
	private function itemcount(){
		$global=Zend_Registry::get(SYNRGIC_SESSION);
		$count=0;
		if(!empty($global->orders))
			foreach($global->orders as $k=> $v)
			{
			 $count+= $v['num'];
			}
		return $count;
	}

	private function orderEdit(){
		 $id= $this->getParam('id',null);		
		 $note= $this->getParam('note',null);
		 $global=Zend_Registry::get(SYNRGIC_SESSION);
		 $orders= $global->orders;
		 if(empty($orders)) {
		     exit();
		 }
		
		//检测id是否为有效值，验证 $orders[ $id]已被添加
		if(empty($id)){
			echo"emptyid";
			exit();
		}
		 $item= $this->em->getReference('\Synrgic\Service\Service', $id);
		if(empty($item)&& $orders[ $id]){
		    echo"errorid". $id;
		    exit();
		}
		
		// $note非空，则设置note属性
		if(!empty($note))
		{
		    $orders[ $id]['note']= $note;			
		}		

		$global->orders= $orders;
		print_r("<pre>");
		print_r($orders);
		print_r("</pre>");
	}
	
	//生成订单表
	public function orderList()
	{
		$global=Zend_Registry::get(SYNRGIC_SESSION);
		//$this->view->list= $global->orders;

		$json= $this->getParam('json',null);
		$total=0;
		if(empty($json))
		{
			if(!empty($global->orders))
			{
				foreach($global->orders as $k=> $v)
				{
					$item= $this->em->getReference('\Synrgic\Service\Service', $k);
					
					if(!empty($item))
					{					
						// $orders[]=array('name'=> $item->getname(),'price'=> $item->getprice(),'num'=> $v);//*/ $item;
						$orders[]=array('service_id'=> $item->getId(),'service_name'=> $item->getName(),
						'service_price'=> $item->getPrice(),'remark'=> $v['note'],'num'=> $v['num']);
						$total = $total+$item->getPrice()* $v['num'];
					}
				}
				$this->view->orders= $orders;
			}
			$this->view->total= $total;
		}
		else
		{
			if(!empty($global->orders))
			foreach($global->orders as $k=> $v)
			{
				$item= $this->em->getReference('\Synrgic\Service\Service', $k);
				if(!empty($item))
				{
					 $result[]=array('id'=>$item->getId(),'name'=> $item->getname(),'price'=> $item->getprice(),'num'=> $v['num']);
				}
			}
			if(!empty($result))
			echo json_encode($result);
			exit();
		}
	}
	private function getjsonorderlist(){
		
		if(!empty($global->orders))
			foreach($global->orders as $k=> $v)
			{
				$item= $this->em->getReference('\Synrgic\Service\Service', $k);
				if(!empty($item))
				{
					 $result[]=array('name'=> $item->getname(),'price'=> $item->getprice(),'num'=> $v);
//$result[]=array('service_id'=> $item->getId(),'service_name'=> $item->getName(),'service_price'=> $item->getPrice(),'remark'=> $v['note'],'num'=> $v['num'],'operator_id'=>0,"order_state"=>"new");
				}
			}
		return json_encode($result);
	}	

	//订单确认
	public function orderConfirm()
	{
		$global=Zend_Registry::get(SYNRGIC_SESSION);
		// $this->view->list= $global->orders;
		//时间
		$total=0;
		//如果订单为空直接返还
		if(empty($global->orders))
		{
			echo $total;
			exit();
		}
		
		$now=new\Datetime('now');
		//获得商品列表，计算总价
		foreach($global->orders as $k=> $v)
		{
			$item= $this->em->getReference('\Synrgic\Service\Service', $k);
			if(!empty($item))
			{
				$provider_id= $item->getProvider()->getId();
				$provider_name= $item->getProvider()->getName();
				$state="new";
				$orders[]=array("service_id"=> $item->getId(),"service_name"=> $item->getName(),"service_price"=> $item->getPrice(),
				"remark"=> $v['note'],"quantity"=> $v['num'],"provider_id"=> $provider_id,"state"=> $state,"provider_name"=>$provider_name);
				$total+= $item->getPrice()* $v['num'];
			}
		}
		
		
		//添加订单
		 $confirm_orders=new\Synrgic\Service\Confirm_orders();
		 $confirm_orders->setState("new");
		 $confirm_orders->setRemark("Food/Drink");
		 $confirm_orders->setTotal_price($total);		
		// $confirm_orders->setScheduled_time($now->format("Ymdhs").rand(1000,9999));
		 $confirm_orders->setScheduled_time($now);
		 $confirm_orders->setOccupiedroom_id($this->roomId);
		 $confirm_orders->setType(1);
		
		// $confirm_orders->setRoom_id($this->roomId);
		$this->em->persist($confirm_orders);
		$this->em->flush();
		
		//添加billdata
			$billdata = new \Synrgic\BillPreview\Billdata();
            $billdata->setDate($now);
            $billdata->setDescription("Food/Drink");    
            $billdata->setAmount($total);
            $billdata->setRoom($this->roomName);
            //Zend_Debug::dump($billdata);
            $this->em->persist($billdata);
            $this->em->flush(); 
            
		//添加订单详情
		foreach($orders as $v)
		{
			 $v['cid']= $confirm_orders->getId();			
			 $this->detail_order_repo->addDetail_order($v);
			 /*
			 $category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('2');
			 $room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$this->roomId));
			 $this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,$v['service_name'],$v['state']);
			 */
		}

		$category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('4');
		$room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$this->roomId));
		$this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,'Order No:'.$confirm_orders->getId().'','Price:$'.$total.'<br><a href="/service/index/order/id/'.$confirm_orders->getId().'" >Details</a>');

		//清空购物车
		 $global->orders=null;
		//print_r("<pre>");
		//print_r($confirm_orders);
		//print_r("</pre>");
		//exit();
		echo $this->view->translate('Total price').':  $'.$total;
		exit();
	}
	
public function orderDel(){
 $id= $this->getParam('id',null);
 $global=Zend_Registry::get(SYNRGIC_SESSION);
if(empty($id)){
echo"delall";
 $global->orders=null;
}
else{
unset($global->orders[ $id]);
}
 $count=0;
if(!empty($global->orders))
foreach($global->orders as $k=> $v){
 $count+= $v['num'];
}
echo $count;
exit();
}
public function taxiAction()
{
		//数据库查询,需要注意的是：若数据库中不存在type=2的service，将导致无法添加confirm_orders
		 $query= $this->em->createQuery("SELECT b FROM \Synrgic\Service\Service b WHERE b.type=2");
		 $services= $query->getResult();
		
		//session
		 $global=Zend_Registry::get(SYNRGIC_SESSION);
		
		//url参数解析
		 $booking= $this->getParam("booking",null);
		 $location= $this->getParam("location",null);
		 $destination= $this->getParam("destination",null);
		 $addtionalNotes= $this->getParam("addtionalNotes",null);
		 $scheduledTime= $this->getParam("scheduledTime",null);
		
		//booking参数非空
		if(!empty($booking)){
			 $serviceMessage="Fail to book service!";
			foreach($services as $item){
				//if($booking!="taxi")break;
				if($item->getName()!="Taxi")continue;							
				 $order_state="new";
				 $remark="location:". $location."destination:". $destination."addtionalNotes:". $addtionalNotes;
				 $confirm_time=new\Datetime($scheduledTime);
				 $room_id= $this->roomId;
				 $user_id= $this->roomId;
				 $type= $item->getType();
				 $provider_id= $item->getProvider()->getId();
				 $provider_name= $item->getProvider()->getName();

				//订单表
				 $confirm_orders=new\Synrgic\Service\Confirm_orders();			
				
				// $confirm_orders->setState($order_state);
				 $confirm_orders->setRemark($remark);
				 $confirm_orders->setTotal_price(0);
				// $confirm_orders->setSn($confirm_time->format("Ymdhs").rand(1000,9999));
				 $confirm_orders->setScheduled_time($confirm_time);		
				 $confirm_orders->setOccupiedroom_id($user_id);
				 $confirm_orders->setType($type);
	
		 $this->em->persist($confirm_orders);
		 $this->em->flush();
				
				//订单详表
		 $orders=array("cid"=> $confirm_orders->getId(),"service_id"=> $item->getId(),"service_name"=> $item->getName(),																																	"service_price"=> $item->getPrice(),"remark"=> $remark,"quantity"=>1,"provider_id"=> $provider_id,"state"=>"new","provider_name"=>$provider_name);				
				 $this->detail_order_repo->addDetail_order($orders);	
				 $serviceMessage=$this->view->translate("book service taxi ok!");

				 $category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('4');
				 $room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$this->roomId));
				 $this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,'Order No:'.$confirm_orders->getId(),'placed');
				 
			}
			
			if(empty($services)){				
				 $serviceMessage="taxservicedoesnotexist!";
			}
			echo $serviceMessage;
			exit();			
		}		
}
	

public function otherAction()
{
		//url参数解析
		 $booking= $this->getParam("booking",null);
		 $serviceitem= $this->getParam("serviceitem",null);
		 $addtionalNotes= $this->getParam("addtionalNotes",null);

		 $id= $this->getParam("id",null);

		if(!empty($id)){
			$service = $this->em->getReference ( '\Synrgic\Service\Service', $id );
			if(!empty($service)){
				$this->view->service = $service;
				$form = new Synrgic_Models_Form(); 
				$date =new DateTime('now');
				if($service->getHas_quantity()=='1'){
					$quantity = new Zend_Form_Element('quantity');  
					$quantity->setLabel('quantity:')  
						->setRequired(true)->setValue(1)
					; 
					$form->addElement($quantity);
				}
				if($service->getHas_remark()=='1'){
					$remark = new Zend_Form_Element('remark');  
					$remark->setLabel('remark:')  
						->setRequired(true)->setValue('null');
					; 
					$form->addElement($remark);
				}
				if($service->getHas_starttime()=='1'){
					$starttime = new Zend_Form_Element('starttime');  
					$starttime->setLabel('starttime:')  
						->setRequired(true)->setValue($date->format('Y-m-d H:i'))
					; 
					$form->addElement($starttime);
				}
				if($service->getHas_stoptime()=='1'){
					$stoptime = new Zend_Form_Element('stoptime');  
					$stoptime->setLabel('stoptime:')  
						->setRequired(true)->setValue($date->format('Y-m-d H:i'))
					; 
					$form->addElement($stoptime);
				}
				
				$last = new Zend_Form_Element_Hidden('return');
				$last->setValue($this->_helper->URIHolder->getURI());
				$form->setAction('/service/index/other/id/'.$id)  
				->setMethod('post')    
				->addElement($last)
				->addSubmitButton('Confirm','Confirm')
				->addCancelButton();
				$this->view->form=$form;
				
				//post
				$request = $this->getRequest();

				if($request->isPost() && $form->isValid($request->getPost())){
				$quantity = $form->getValue('quantity',1);
				$remark = $form->getValue('remark');
				$starttime = new\Datetime($form->getValue('starttime'));
				$stoptime = new\Datetime($form->getValue('stoptime'));

				$order_state="new";
				//$remark="service item:". $serviceitem."addtional Notes:". $addtionalNotes;
				$confirm_time=new\Datetime('now');		
				$room_id= $this->roomId;
				$user_id= $this->roomId;
				$type= $service->getType();
				$provider_id= $service->getProvider()->getId();
				$provider_name= $service->getProvider()->getName();

				//订单表
				$confirm_orders=new\Synrgic\Service\Confirm_orders();			
						
			  	$confirm_orders->setState($order_state);
				$confirm_orders->setRemark($remark);
				$confirm_orders->setTotal_price($service->getPrice());
			// $confirm_orders->setSn($confirm_time->format("Ymdhs").rand(1000,9999));
				$confirm_orders->setConfirm_time($starttime);
				$confirm_orders->setScheduled_time($stoptime);
			 // $confirm_orders->setRoom_id($room_id);
				$confirm_orders->setOccupiedroom_id($user_id);
				$confirm_orders->setType($type);
					
				$this->em->persist($confirm_orders);
				$this->em->flush();
				
				//订单详表
				 $detail_orders=array("cid"=> $confirm_orders->getId(),"service_id"=> $service->getId(),"service_name"=> $service->getName(),"service_price"=> $service->getPrice(),"remark"=> $remark,"quantity"=>$quantity,"provider_id"=> $provider_id,"provider_name"=>$provider_name);
				 $this->detail_order_repo->addDetail_order($detail_orders);
				// $detail_orders=new\Synrgic\Service\Detail_orders();
				// $detail_orders->fromArray($orders);						
				// $this->em->persist($detail_orders);
				// $this->em->flush();
				 $this->view->msg = $service->getName().' Order No:'.$confirm_orders->getId().' Placed';//.$starttime.$stoptime;
				 $category = $this->em->getRepository('\Synrgic\Noticeboard\AlertCategory')->findOneById('4');
				 $room = $this->em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('id'=>$this->roomId));
				 $this->em->getRepository('\Synrgic\Noticeboard\Alert')->createAlertForRoom($category,$room,'Order No:'.$confirm_orders->getId(),'placed');
				 $this->_redirect("/service/index/other");
					
				}
			}

		}else{
		//数据库查询
		 $global=Zend_Registry::get(SYNRGIC_SESSION);		
		 $query= $this->em->createQuery("SELECT b FROM \Synrgic\Service\Service b WHERE b.type=3");
		 $services= $query->getResult();
		 $this->view->services= $services;
		}
		
		//booking参数非空	
		if(!empty($booking)){
			if($booking=="service"){
				 $serviceMessage="Fail to book service". $serviceitem;
				foreach($services as $item)
				{					
					if($item->getName()== $serviceitem)
					{
						 $order_state="new";
						 $remark="service item:". $serviceitem."addtional Notes:". $addtionalNotes;
						 $confirm_time=new\Datetime('now');		
						 $room_id= $this->roomId;
						 $user_id= $this->roomId;
						 $type= $item->getType();
						 $provider_id= $item->getProvider()->getId();
						 $provider_name= $item->getProvider()->getName();

						//订单表
						 $confirm_orders=new\Synrgic\Service\Confirm_orders();			
						
						 $confirm_orders->setState($order_state);
						 $confirm_orders->setRemark($remark);
						 $confirm_orders->setTotal_price(0);
						// $confirm_orders->setSn($confirm_time->format("Ymdhs").rand(1000,9999));
						 $confirm_orders->setScheduled_time($confirm_time);
						// $confirm_orders->setRoom_id($room_id);
						 $confirm_orders->setOccupiedroom_id($user_id);
						 $confirm_orders->setType($type);
					
						 $this->em->persist($confirm_orders);
						 $this->em->flush();
				
						//订单详表
						$detail_orders=array("cid"=> $confirm_orders->getId(),"service_id"=> $item->getId(),"service_name"=> $item->getName(),"service_price"=> $item->getPrice(),"remark"=> $remark,"quantity"=>1,"provider_id"=> $provider_id,"provider_name"=>$provider_name);
						 $this->detail_order_repo->addDetail_order($detail_orders);
						// $detail_orders=new\Synrgic\Service\Detail_orders();
						// $detail_orders->fromArray($orders);						
						// $this->em->persist($detail_orders);
						// $this->em->flush();
						 $serviceMessage="Bookservice". $serviceitem."Ok!";
						break;
					}
				}
				echo $serviceMessage;			
			}
			exit();
		}		
		// $list= $this->em->getRepository('\Synrgic\Service\Service')->searchByCatalog($cid);
	}

	public function cancelAction()
    {
		$this->_helper->flashMessenger->addMessage('Room addition/Modification cancelled');
		$this->_redirect('/service/index/other');
    }

	public function orderlistAction()
	{
		 $this->_helper->layout->disableLayout();		
		 $opt= $this->getParam('cmd',null);
		
		switch($opt)
		{
		case'add';
		{				
		 $this->orderAdd();
		 $this->orderEdit();
		break;
		}
		case"del":
		{
		 $this->orderDel();
		break;
		}
		case"confirm":
		{
		 $this->orderConfirm();
		break;
		}			
		case"list":
		default:{
		$this->orderList();
		break;
		}
		}
		 
	}

	public function deleteAction(){
		 $id= $this->getParam('id');			
		if($this->confirm_order_repo->deleteConfirm_orders($id)){
			print_r("deleteconfirm_ordertableok");
		}else{
			print_r("deleteconfirm_ordertablefail!");
		}
		exit();			
	}

	public function orderAction()
	{
		$id= $this->getParam('id');
		$this->view->id=$id;
		$confirmOrdersdata = $this->em->getRepository('\Synrgic\Service\Confirm_orders')->findOneBy(array('id'=>$id));
		if(($confirmOrdersdata<>NULL)&&(count($confirmOrdersdata->getDetail_orders())>0))
			{	
				$this->view->details=$confirmOrdersdata->getDetail_orders();
			}
	}
}
