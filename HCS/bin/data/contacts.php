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
 * contacts
 */

$tuples = array( 
	array( "phone", "order", "11111111-222"),
	array( "phone", "bill",  "22222222-333"),
	array( "phone", "frontdesk", "33333333-444"),
	array( "phone", "help", "44444444-555"),
    	array( "phone", "service", "55555555-666"),

	array( "email", "order", "11111111@gmail.com"),
	array( "email", "bill",  "22222222@gmail.com"),
	array( "email", "frontdesk", "33333333@gmail.com"),
	array( "email", "help", "44444444@gmail.com"),
    	array( "email", "service", "55555555@gmail.com"),

	array( "skype", "order", "skype-222"),
	array( "skype", "bill",  "skype-333"),
	array( "skype", "frontdesk", "skype-444"),
	array( "skype", "help", "skype-555"),
    	array( "skype", "service", "skype-666"),

	);

foreach( $tuples as $tuple ){
	$bill = new \Synrgic\Contact();
	$bill->setTitle($tuple[0]);
	$bill->setCategory($tuple[1]);
	$bill->setDetail($tuple[2]);
	$em->persist($bill);
}

$em->flush();

?>
