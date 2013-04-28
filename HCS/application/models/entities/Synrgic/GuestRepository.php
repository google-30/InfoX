<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic;

use Doctrine\ORM\EntityRepository;

class GuestRepository extends EntityRepository 
{
    /**
     * Checks the Guest into the given rooms
     *
     * @param guest The guest object to check in
     * @param rooms An array of \Synrgic\Room objects
     */
    public function checkIn(\Synrgic\Guest $guest,array $rooms)
    {
	$deviceRepo = $this->_em->getRepository('\Synrgic\Device');

	foreach($rooms as $room ){
	    $occupiedRoom = new \Synrgic\OccupiedRoom();
	    $occupiedRoom->setGuest($guest);
	    $occupiedRoom->setPhysicalRoom($room);
	    $this->_em->persist($occupiedRoom);

	    $devices = $deviceRepo->findBy(array('room'=>$room));
	    foreach($devices as $device){
		    $deviceRepo->resetDevice($device);
	    }
	}
	$this->_em->flush();
    }

    /**
     * Checkout the guest from the hotel
     *
     * This function performs the following:
     *  o Clear the session for any device the guest has been using
     * 	o Checks out the guest from any occupied rooms they are in
     *
     * @param guest The guest to check out
     */
    public function checkOut(\Synrgic\Guest $guest)
    {
	// The entity we may be given may be in a detached state.
	// Rather than forcing each caller to attach the state, we
	// do it for them here
	$guest = $this->_em->find('\Synrgic\Guest',$guest->getId());

	$deviceRepo = $this->_em->getRepository('\Synrgic\Device');
	$alertsRepo = $this->_em->getRepository('\Synrgic\Noticeboard\Alert');
	$rooms = $guest->getRooms();


	foreach($rooms as $room){
		
		$roomId=$room->getId();
		$ordersData=$this->_em->getRepository('\Synrgic\Service\Confirm_orders')->removeOrdersOfRoom($roomId);
		
		foreach($ordersData as $r)
		{
			$r->setDelete();
		}

	    $phyroom = $room->getPhysicalRoom();
	    $device = $deviceRepo->findOneBy(array('room'=>$phyroom));
	    if(isset($device))
		$deviceRepo->resetDevice($device);

	    // Remove any alerts for the room.
	    $alertsRepo->removeAllAlertsByRoom($room);

	    $this->_em->remove($room);
	}
	$this->_em->flush();
    }

    /**
     * Remove the guest from the system. This also checks out the guest
     * if the guest is currently checked in
     * 
     * @param ID The id of the guest to checkout
     */
    public function deleteById($id)
    {
	$guest = $this->_em->find('\Synrgic\Guest',$id);
	if( $guest != null ){
	    $this->checkOut( $guest );
	    $this->_em->remove( $guest );
	    $this->_em->flush();
	}
    }

    /*
     * get guest rooms
     *
     */      
    public function findGuestRoomsByName()
    {

    }

    /*
     * get guest rooms
     */           
    public function findGuestsRooms()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a, ar')
            ->from('\Synrgic\Guest', 'a')
            ->innerJoin('a.rooms', 'ar');
        return $qb->getQuery()->getArrayResult();
    }
}
