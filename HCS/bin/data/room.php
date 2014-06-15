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
 * Define some default rooms
 */

$tuples = array( 
   	       /* Name     Description */	 
	 array( "101",     "First Floor Room 1" ,"A"),
	 array( "102",     "First Floor Room 2" ,"B"),
	 array( "103",     "First Floor Room 3" ,"C")
	);

foreach( $tuples as $tuple ){
	$room = new \Synrgic\Room();
	$room->setName($tuple[0]);
	$room->setDescription($tuple[1]);
	$room->setType($tuple[2]);
	$em->persist($room);
}

$em->flush();

?>
