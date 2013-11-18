<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Bill_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $roomarray;
    private $guestRepo;
    private $_services;
    private $_occupiedroom;
    private $_checkin;
    private $_bill;
    private $_room;
    private $_nights=0;
    private $_roomtype="X";    

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->guestRepo = $this->_em->getRepository('Synrgic\Guest');
        $this->_services = $this->_em->getRepository('Synrgic\Service\Service');
        $this->_occupiedroom = $this->_em->getRepository('Synrgic\OccupiedRoom');
        $this->_checkin = $this->_em->getRepository('Synrgic\BillPreview\Guestdata');
        $this->_bill = $this->_em->getRepository('\Synrgic\BillPreview\Billdata');
        $this->_room = $this->_em->getRepository('\Synrgic\Room');
        $this->_orders = $this->_em->getRepository('\Synrgic\Service\Confirm_orders');
    }

    public function indexAction()
    {
        // TODO: get room numbers and guest names, room type, room position,
        // maybe should include guest check-in/out time
        $roomlist = array();
        $ocrooms = $this->_occupiedroom->findAll();
        foreach($ocrooms as $tmp)
        {
            $roomdata = array();

            $guest = $tmp->getGuest();
            $guestname = $guest->getName();

            $room = $tmp->getPhysicalRoom();
            $roomid = $room->getId();
            $roomname = $room->getName();
            $roomdes = $room->getDescription();
            $roomtype = $room->getType();
            //echo "name=$guestname<br>";
            //echo "name=$roomname<br>";

            $roomdata['guestname'] = $guestname;
            $roomdata['room'] = $roomname;
            $roomdata['description'] = $roomdes;
            $roomdata['roomtype'] = $roomtype;
            $roomdata['id'] = $roomid;
            
            $roomlist[] = $roomdata;
        }

        $this->view->roomlist = $roomlist;
    }

    public function roomAction()
    {
        $id = $this->getParam('id');
        //$room = $this->_em->getReference('\Synrgic\Room',$id);
        $room = $this->_em->getRepository('\Synrgic\Room')->findOneBy(array('id' => $id));
        $this->view->guestroom = $roomname = $room->getName();
        $this->view->guestroomtype = $room->getType();

        $ocroom =  $this->_em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('physicalRoom' => $room));        
        $guest = $ocroom->getGuest();
        $this->view->guestname = $guest->getName();
        $stay = $this->_checkin->findOneBy(array('guest' => $guest));
        $this->view->guestarrival = $stay->getArrival()->format('Y-m-d');
        $this->view->guestdeparture = $stay->getDeparture()->format('Y-m-d');
        $this->view->guestnights = $stay->getDeparture()->diff($stay->getArrival())->format('%d');

        // TODO: room cost
        // check billdata, if room cost is there
        // A. there, then display
        // B. not there, insert to billdata
        $this->getRoomCostInfo($roomname);
        $type = "Room " . $this->_roomtype;
        $roomcost = $this->_bill->findOneBy(array('name' => $type, 'physicalRoom' => $room));
        if(!$roomcost)
        {
            //echo "no found room cost<br>";
            // insert
            $billdata = array();
            $billdata['date'] = 'now';            
            $billdata['name'] = $type;
            $billdata['roomname'] = $roomname;
            $billdata['quantity'] = $this->_nights;   
            //print_r($billdata); 
            $this->updateBilldata($billdata, "create");            
        }

        // other services
        // some services should be recorded by hotel staff manually:
        // for example: guest drink/have food/drinks in mini bar, only staff check room and record in bill
        $catalog_id = 3;
        $catalog = $this->_em->getRepository('\Synrgic\Service\Catalog')->findOneBy(array('id' => $catalog_id));
        $data = $this->_services->findBy(array('category' => $catalog));
        $this->view->chargingitems = $data;

        // bill          
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
        ->from('\Synrgic\BillPreview\Billdata', 'a')
        ->where('a.physicalRoom = ?1')
        ->orderBy('a.date', 'DESC')
        ->setParameter(1, $room);;
        $billresult = $qb->getQuery()->getResult();
        $this->view->billdata = $billresult;   

        $total=0;
        foreach($billresult as $tmp)
        {
            $total += $tmp->getPrice() * $tmp->getQuantity();
        }
        $this->view->totalamount = $total; 
 
        // TODO: room service, e.g. food orders
        $orders = $this->_orders->findBy(array('occupiedroom_id' => $ocroom->getId()));
        $this->view->roomservices = $orders;
    }

    private function updateBilldata($billdata, $mode)
    {
        $data = null;
        if($mode == "create")
        {
            $data = new \Synrgic\BillPreview\Billdata();
            //echo "Bill Data Created.";
        }
        else if($mode == "edit")
        {
            $data = $this->_bill->findOneBy(array('id' => $id));        
            //echo "Bill Data Updated.";
        }
                
        $date = new DateTime($billdata['date']);
        $roomname = $billdata['roomname'];
        $name = $billdata['name'];        
        $quantity = $billdata['quantity'];

        $data->setDate($date);        
        $data->setRoom($roomname);
        $room = $this->_room->findOneBy(array('name' => $roomname)); 
        $data->setPhysicalRoom($room);
        $data->setName($name);    
        $data->setQuantity($quantity);
          
        $service = $this->_services->findOneBy(array('name' => $name));
        $price = $service->getPrice(); 
        $data->setPrice($price);        
       
        if(array_key_exists('description', $billdata))
        {
            $description = $billdata['description'];
        }
        else
        {
            $description = $service->getIntroduction();
        }
        $data->setDescription($description);       

        $amount = array_key_exists('amount', $billdata) ? $billdata['amount'] : 0;
        $data->setAmount($amount);       

        $this->_em->persist($data);
        $this->_em->flush();
    }    

    private function getRoomCostInfo($roomname)
    {// get nights and type
        $room = $this->_room->findOneBy(array('name' => $roomname));
        $this->_roomtype = $room->getType();
        $ocroom =  $this->_occupiedroom->findOneBy(array('physicalRoom' => $room));        
        $guest = $ocroom->getGuest();
        $stay = $this->_checkin->findOneBy(array('guest' => $guest));
        $this->_nights = $stay->getDeparture()->diff($stay->getArrival())->format('%d');
    }

    public function expsubmitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        // check data
        if(0)
        {
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }

        $date = $this->_getParam('chargedate');
        //$amount = $this->_getParam('amount');
        $description = $this->_getParam('description');
        $roomname = $this->_getParam('room');
        $mode = $this->_getParam('expformmode');
        $id = $this->_getParam('billid');
        $name = $this->_getParam("chargeitem");
        $quantity = $this->_getParam("quantity");        

        $data = null;
        if($mode == "create")
        {
            $data = new \Synrgic\BillPreview\Billdata();
            echo "Bill Data Created.";
        }
        else if($mode == "edit")
        {
            $data = $this->_bill->findOneBy(array('id' => $id));        
            echo "Bill Data Updated.";
        }
        
        $data->setDate(new DateTime($date));
        $data->setDescription($description);
        //$data->setAmount(0.0);
        $data->setRoom($roomname);
        $room = $this->_room->findOneBy(array('name' => $roomname)); 
        $data->setPhysicalRoom($room);
        $data->setName($name);    
        $data->setQuantity($quantity);
          
        // query price
        $service = $this->_services->findOneBy(array('name' => $name));
        $price = $service->getPrice();            
        $data->setPrice($price);        

        $this->_em->persist($data);
        $this->_em->flush();
    }

    public function deleteAction()
    {   // delete records
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        //var_dump($requests);
        //var_dump($_POST);

        foreach($requests as $key => $value)
        {
            if($value == 'true' && $key)
            {
                $data = $this->_em->getRepository('\Synrgic\BillPreview\Billdata')
                        ->findOneBy(array('id' => $key));
                if($data)
                {
                    var_dump($data);
                    $this->_em->remove($data);
                }
            }
        }
        $this->_em->flush();
    }

    public function setGuestInfo($room)
    {
        $guestresult = $this->_em->getRepository('\Synrgic\BillPreview\Guestdata')->findOneBy(array('room' => $room));
        $this->view->guestname = $guestresult->getGuestname();
        $this->view->guestroom = $room;
        $this->view->guestarrival = $guestresult->getArrival()->format('Y-m-d');
        $this->view->guestdeparture = $guestresult->getDeparture()->format('Y-m-d');
        $this->view->guestroomtype = $guestresult->getRoomtype();
        $this->view->guestnights = $guestresult->getDeparture()->diff($guestresult->getArrival())->format('%d');
    }

    public function previewAction()
    {
        $this->_helper->layout->disableLayout();
        //$this->_helper->viewRenderer->setNoRender(TRUE);

        //$room = $this->getRequest()->getParam('no',null);
        $room = $this->_getParam('no');

        if( $room === null ) {
            // XXX indicate error
            echo '<br>Please Provide Room No.<br>';
        }
        else
        {
            // TODO:query guest data from room/occupiedroom/guests
            // guest info
            $guestresult = $this->_em->getRepository('\Synrgic\BillPreview\Guestdata')->findOneBy(array('room' => $room));
            $this->view->guestname = $guestresult->getGuestname();
            $this->view->guestroom = $room;
            $this->view->guestarrival = $guestresult->getArrival()->format('Y-m-d');
            $this->view->guestdeparture = $guestresult->getDeparture()->format('Y-m-d');
            $this->view->guestroomtype = $guestresult->getRoomtype();
            $this->view->guestnights = $guestresult->getDeparture()->diff($guestresult->getArrival())->format('%d');
            // bill preview
            //$billresult = $this->_em->getRepository('\Synrgic\BillPreview\Billdata')->findBy(array('room' => $room));
            $qb = $this->_em->createQueryBuilder();
            $qb->select('a')
            ->from('\Synrgic\BillPreview\Billdata', 'a')
            ->where('a.room = ?1')
            ->orderBy('a.date', 'DESC')
            ->setParameter(1, $room);;

            $billresult = $qb->getQuery()->getResult();

            $this->view->billdata = $billresult;

            $total=0;
            foreach($billresult as $r)
            {
                $total += $r->getAmount();
            }
            $this->view->totalamount = $total;
        }
    }

    public function billspreviewAction()
    {
        $this->_helper->layout->disableLayout();
        $id = $this->getParam('id');
        $room = $this->_em->getReference('\Synrgic\Room',$id);
        $roomarray = array($room->getName());
        $billsarray = array();

        foreach($roomarray as $room)
        {
            $guestresult = $this->_em->getRepository('\Synrgic\BillPreview\Guestdata')->findOneBy(array('room' => $room));
            if($guestresult)
            {
                $infoarray = array();
                $infoarray['name'] = $guestresult->getGuestname();
                $infoarray['room'] = $room;
                $infoarray['arrival'] = $guestresult->getArrival()->format('Y-m-d');
                $infoarray['departure'] = $guestresult->getDeparture()->format('Y-m-d');
                $infoarray['type'] = $guestresult->getRoomtype();
                $infoarray['nights'] = $guestresult->getDeparture()->diff($guestresult->getArrival())->format('%d');


                $qb = $this->_em->createQueryBuilder();
                $qb->select('a')
                ->from('\Synrgic\BillPreview\Billdata', 'a')
                ->where('a.room = ?1')
                ->orderBy('a.date', 'DESC')
                ->setParameter(1, $room);;
                $result = $qb->getQuery()->getResult();
                $infoarray['bills'] = $result;

                $total=0;
                foreach($result as $r)
                {
                    $total += $r->getAmount();
                }
                $infoarray['totalamount'] = $total;

                $billsarray[$room] = $infoarray;
            }
        }

        //var_dump($billsarray);
        $this->view->billsarray = $billsarray;
    }

    public function billcollectAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        //echo "billcollectAction";
        $requests = $this->getRequest()->getPost();
        var_dump($requests);

        $this->roomarray = array();

        //var rooms = $this->roomarray;
        foreach($requests as $key => $value)
        {
            if($value == 'true')
            {
                $this->roomarray[] = $key;
            }
        }

        var_dump($this->roomarray);
    }
}

