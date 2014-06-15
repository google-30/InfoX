<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * guest data
 * already nonsense, now it plays as check-in data    
 */
date_default_timezone_set('UTC');

$tuples = array( 
    /* name, room, check-in, check-out, room type, guest id */
    /* name, room, room type, all nonsence */
	array( "Mike", "101", new DateTime("2012-10-01"), new DateTime("2012-10-10"), "junior class", 1),
	array( "Ben", "102", new DateTime("2012-10-11"), new DateTime("2012-10-20"), "senior class", 2),
	array( "Melissa", "103", new DateTime("2012-10-21"), new DateTime("2012-10-30"), "high class", 3),
	);

foreach( $tuples as $tuple ){
	$guest = new \Synrgic\BillPreview\Guestdata();
	$guest->setGuestname($tuple[0]);
	$guest->setRoom($tuple[1]);
	$guest->setArrival($tuple[2]);
	$guest->setDeparture($tuple[3]);
	$guest->setRoomtype($tuple[4]);

    $guestobj = $em->getRepository('\Synrgic\Guest')->findOneBy(array('id'=>$tuple[5]));
    $guest->setGuest($guestobj);
	$em->persist($guest);
}

$em->flush();

?>
