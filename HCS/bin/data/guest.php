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
 * Define some default guests
 */

$tuples = array( 
   	       /* name, language, pin, Room assigned */
	 array( "Bill Gates", "English", "12345", array("101")),
	 array( "Steve Jobs", "Chinese", "54321", array("102")),
	 array( "John Doe", "English", "67890", NULL),
	 array( "Steve Smith", "Chinese", "09876", NULL)
	);

foreach( $tuples as $tuple ){
	$guest = new \Synrgic\Guest();
	$guest->setName($tuple[0]);
	$guest->setPin($tuple[2]);

	// Find language
	$language = $em->getRepository('\Synrgic\Language')->findOneByName($tuple[1]);
	$guest->setpreferredLanguage($language);
	$em->persist($guest);

	$guestRepo = $em->getRepository('\Synrgic\Guest');

	if( $tuple[3] != NULL ){
	    $rooms = array();
	    foreach($tuple[3] as $roomName){
		$room = $em->getRepository('\Synrgic\Room')->findOneByName($roomName);
		$rooms[] = $room;
	    }
	    $guestRepo->checkIn($guest,$rooms);
	}
}

$em->flush();


