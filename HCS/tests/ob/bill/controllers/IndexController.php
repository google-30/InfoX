<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Bill_IndexController extends Zend_Controller_Action
{
    private $_em;
    private $_room;
    private $_guest;
    private $_services;
    private $_occupiedroom;
    private $_checkin;
    private $_bill;
    private $_nights=0;
    private $_roomtype="X";
    private $_orders;

    public function init()
    {
        $_session = Zend_Registry::get(SYNRGIC_SESSION);
        $this->_em = Zend_Registry::get('em');
        $this->_room = $_session->room->getName();
        $this->_guest = Zend_Auth::getInstance()->getIdentity();
        $this->_services = $this->_em->getRepository('Synrgic\Service\Service');
        $this->_occupiedroom = $this->_em->getRepository('Synrgic\OccupiedRoom');
        $this->_checkin = $this->_em->getRepository('Synrgic\BillPreview\Guestdata');
        $this->_bill = $this->_em->getRepository('\Synrgic\BillPreview\Billdata');
        $this->_orders = $this->_em->getRepository('\Synrgic\Service\Confirm_orders');
    }

    public function indexAction()
    {
        $this->getGuestInfo();
        $this->getHotelContacts();
    }

    public function billsheetAction()
    {
        $this->_helper->layout->disableLayout();

        $room = $this->_em->getRepository('\Synrgic\Room')->findOneBy(array('name' => $this->_room));

        // other services
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

        // room cost    
        // TODO: check room cost if it's already in billdata         
        // A. already there - just return
        // B. not there - stuff/admin should generate room cost in guest bill data
        $this->getRoomCostInfo();
        $type =  "Room " . $this->_roomtype; 
        //echo "nights=" . $this->_nights . ",type=". $type;
        $billdata = $this->_bill->findOneBy(array('name' => $type, 'physicalRoom'=>$room));
        if($billdata)
        {
            //echo "roomcost is there, return<br>";
        }
        else if(0)
        {    
            /*
            $service = $this->_services->findOneBy(array('name' => $type));
            $roomcost = array();
            $roomcost['name'] = $service->getName();
            $roomcost['price'] = $service->getPrice();
            $roomcost['quantity'] = $this->_nights;   
            $roomcost['amount'] = $roomcost['price'] * $this->_nights; 
            // TODO: check this date, if guest leave earlier
            $roomcost['date'] = new DateTime ('now');
            $this->view->roomcost = $roomcost;        
            $this->view->totalamount += $roomcost['amount'];      
            */
        }

        // room service
        $ocroom = $this->_occupiedroom->findOneBy(array('physicalRoom'=>$room));
        $orders = $this->_orders->findBy(array('occupiedroom_id' => $ocroom->getId()));
        $this->view->roomservices = $orders; 
        foreach($orders as $tmp)
        {
            //echo "remark=" . $tmp->getRemark();
            $total += $tmp->getTotal_price();
        }
        $this->view->totalamount = $total;       
    }

    private function getRoomCostInfo()
    {// get nights and type
        $room = $this->_em->getRepository('\Synrgic\Room')->findOneBy(array('name' => $this->_room));
        $this->_roomtype = $room->getType();
        $ocroom =  $this->_occupiedroom->findOneBy(array('physicalRoom' => $room));        
        $guest = $ocroom->getGuest();
        $stay = $this->_checkin->findOneBy(array('guest' => $guest));
        $this->_nights = $stay->getDeparture()->diff($stay->getArrival())->format('%d');
    }

    public function termsAction()
    {
        //echo "termsAction";
        //$this->_helper->layout->disableLayout();
    }

    public function checkoutAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
        //$requests = $this->getRequest()->getPost();
        //var_dump($requests);

        $hardcopy = false;
        $softcopy = false;
        $email = "";

        if( $this->_hasParam('softcopy'))
            $softcopy = $this->_getParam('softcopy');

        if( $this->_hasParam('hardcopy'))
            $hardcopy = $this->_getParam('hardcopy');

        if( $this->_hasParam('email'))
            $email = $this->_getParam('email');

        if($softcopy === true)
        {   // send email
            $mailcontent = $this->composeEmail();
            $this->sendEmail($email, $mailcontent);
        }

        // store checkout
        $data = new \Synrgic\BillPreview\Checkout();
        $data->setName($this->_guest->getName());
        $data->setRoom($this->_room);
        $data->setHardcopy($hardcopy);
        $data->setSoftcopy($softcopy);
        $data->setEmail($email);
        if($softcopy === true)
        {
            $data->setMailcontent($mailcontent);
        }
        $this->_em->persist($data);
        $this->_em->flush();

        //echo "Checkout Success and you will be logouted from this device.";
        $logout = $this->view->translate('Thank you for using the Hotel Content Management System. You have now been checked out of the room. If you have any further enquires please contact the front desk.');
        echo $logout;

        //$guestRepo = $this->_em->getRepository('\Synrgic\Guest');
        //$guestRepo->checkout($this->_guest);
    }

    function quickcheckoutAction()
    {
        $this->getGuestInfo();
        $this->getHotelContacts();
    }

    private function getGuestInfo()
    {
        $room = $this->_em->getRepository('\Synrgic\Room')->findOneBy(array('name' => $this->_room));
        $this->view->guestroom = $roomname = $room->getName();
        $this->view->guestroomtype = $room->getType();

        $ocroom =  $this->_em->getRepository('\Synrgic\OccupiedRoom')->findOneBy(array('physicalRoom' => $room));        
        $guest = $ocroom->getGuest();
        $this->view->guestname = $guest->getName();
        $stay = $this->_checkin->findOneBy(array('guest' => $guest));
        $this->view->guestarrival = $stay->getArrival()->format('Y-m-d');
        $this->view->guestdeparture = $stay->getDeparture()->format('Y-m-d');
        $this->view->guestnights = $stay->getDeparture()->diff($stay->getArrival())->format('%d');
    }

    private function getHotelContacts()
    {
        // hotel contact info
        $category = "bill";
        $contacts = "";

        $result = $this->_em->getRepository('\Synrgic\Contact')->findby(array('category' => $category));
        foreach($result as $r)
        {
            $title = $r->getTitle();
            $detail = $r->getDetail();
            $contacts .= "<strong>$title:$detail</strong><br>";
        }

        //echo "contacts=" . $contacts;
        $this->view->billcontacts = $contacts;
        $this->view->receptionNumber = Zend_Registry::get('settings')->Contact->Reception->Value;
    }

    private function composeEmail()
    {
        //return "bill preview";
        // TODO:

        // guest info
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
        ->from('\Synrgic\BillPreview\Guestdata', 'a')
        ->where('a.room = ?1')
        ->setParameter(1, $this->_room);
        $guestresult = $qb->getQuery()->getResult();

        $contents = "";
        $contents .= "Thanks for ....";
        $contents .= '<table id="guestinfo"><tr>';
        $contents .= "<td>Name:</td><td><strong>" . $this->_guest->getName() . "</strong></td>";
        $contents .= "<td>Room Type:</td><td><strong>" . $guestresult[0]->getRoomtype() . "</strong></td></tr>";
        $contents .= "<tr><td>Arrival:</td><td><strong>" . $guestresult[0]->getArrival()->format('Y-m-d') . "</strong></td>";
        $contents .= "<td>Room Number:</td><td><strong>" . $this->_room . "</strong></td></tr>";
        $contents .= "<tr><td>Departure:</td><td><strong>". $guestresult[0]->getDeparture()->format('Y-m-d') . "</strong></td>";
        $contents .= "<td>Total Nights:</td><td><strong>" . $guestresult[0]->getDeparture()->diff($guestresult[0]->getArrival())->format('%d'). "</strong></td></tr></table>";

        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
        ->from('\Synrgic\BillPreview\Billdata', 'a')
        ->where('a.room = ?1')
        ->setParameter(1, $this->_room)
        ->orderBy('a.date', "DESC");
        $billResult = $qb->getQuery()->getResult();

        $contents .= "<style>
#BillTable { width:100%; }
#BillTable thead th { text-align:left; border-bottom-width:1px; border-top-width:1px; }
#BillTable td { text-align:left; padding:6px;} 
#BillTable .amount { text-align:right; }
                     </style>";

        $contents .= '<hr><table id="BillTable"><thead><tr><th>Date</th><th>Description</th>';
        $contents .= "<th>Amount</th></tr></thead><tbody> ";
        $contents .= "";

        $total=0;
        foreach($billResult as $r)
        {
            $contents .= "<tr><td>" . $r->getDate()->format('Y-m-d H:i:s') . "</td>";
            $contents .= "<td>" . $r->getDescription() . '</td><td class="amount">' . $r->getAmount() . "</td></tr>";
            $total += $r->getAmount();
        }
        $contents .= "</tbody></table><hr><style>#totaltable{     width: 30%;     float: right;    }";
        $contents .= '#totaltable td { text-align:right; } </style><table id="totaltable">';
        $contents .= "<tr><td>Subtotal:</td><td>$" . $total . "</td></tr>";
        $contents .= "<tr><td>Service Taxes(7%):</td><td>$" . $total*0.07 . "</td></tr>";
        $contents .= "<tr><td><strong>Total:</strong></td><td><strong>$" . $total*1.07 . "</strong></td></tr>";
        $contents .= "<tr></tr><tr></tr></table>";

        return $contents;


    }

    private function sendEmail($email, $content)
    {
        //Prepare email
        $mail = new Zend_Mail();
        $mail->addTo($email, 'Dear Customer');
        $mail->setSubject('Bill from The Grand Hotel');
        $mail->setBodyHtml($content);
        $mail->setFrom('philip@synrgicresearch.com', 'The Grand Hotel');

        //Send it!
        $sent = true;
        try {
            $mail->send();
        } catch (Exception $e) {
            $sent = false;
            var_dump($e);
        }

        //Do stuff (display error message, log it, redirect user, etc)
        if($sent) {
            //Mail was sent successfully.
        } else {
            //Mail failed to send.
        }
    }
}

