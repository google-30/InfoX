<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\NoticeBoard;

use Doctrine\ORM\EntityRepository;

/**
 * The AlertRepository provides additional helper functions
 * for alert class
 */

class AlertRepository extends EntityRepository 
{
    public function getAlertById($id)
    {
	return $this->find($id);
    }

    public function createAlertForRooms(array $rooms, \Synrgic\Noticeboard\AlertCategory $category, $title, $message)
    {
	foreach($rooms as $room){
	    $this->createAlertForRoom($category,$room,$title,$message);
	}
    }

    public function createAlertForGuest($category, $guest, $title, $message)
    {
	$alert = new Alert();
	$alert->setCategory($category);
	$alert->setIssued(new \DateTime("now"));
	$alert->setGuest($guest);
	$alert->setTitle($title);
	$alert->setMessage($message);
	$this->_em->persist($alert);
	$this->_em->flush();
    }

    public function createAlertForRoom($category, $room, $title, $message)
    {
	$alert = new Alert();
	$alert->setCategory($category);
	$alert->setIssued(new \DateTime("now"));
	$alert->setRoom($room);
	$alert->setTitle($title);
	$alert->setMessage($message);
	$this->_em->persist($alert);
	$this->_em->flush();
    }

    /**
     * Find the Alerts for the given room that have not been purged
     *
     * @param room The room to find alerts for
     */ 
    public function findActiveAlertsByRoom($room)
    {
	/*
	 * Alerts exist both on a room level and a guest level. Hence we need to 
	 * Search for alerts for both the current room and find the rooms
	 * belonging too a guest and search them as well.
	 */
	$alerts = $this->findBy(array('purged'=>null,'room'=>$room->getId()));
	if( $room instanceof \Synrgic\OccupiedRoom ){
	    $galerts = $this->findBy(array('purged'=>null,'guest'=>$room->getGuest()->getId()));	
	    $alerts = array_merge($alerts,$galerts);
	}

	return $alerts;
    }

    /**
     * Find all alerts for a given room
     */
    public function findAllAlertsByRoom($room)
    {
	$alerts = $this->findBy(array('room'=>$room->getId()));
	return $alerts;
    }

    public function removeAllAlertsByRoom($room)
    {
	$alerts = $this->findAllAlertsByRoom($room);
	foreach($alerts as $alert){
	    $this->_em->remove($alert);
	}
	$this->_em->flush();
    }

    public function acknowledgeAlert($alert,$room)
    {
	if($alert->getAcknowledged() == NULL ){
	    $alert->setAcknowledged(new \DateTime("now"));
	    $this->_em->persist($alert);
	    $this->_em->flush();
	}
    }

    public function deleteAlert($alert,$room)
    {
	if($alert->getPurged() == NULL ){
	    $alert->setPurged(new \DateTime("now"));
	    $this->_em->persist($alert);
	    $this->_em->flush();
	}
    }


    public function deleteActiveAlertsByRoom($room)
    {
	$alerts = $this->findActiveAlertsByRoom($room);
	foreach($alerts as $alert){
	    $this->acknowledgeAlert($alert,$room);
	}
    }

}

?>
