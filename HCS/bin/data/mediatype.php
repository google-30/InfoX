<?
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * This file defines the media types supported by the HCS Adverts System
 *
 * TODO: Tie this in with the actual language bindings - benjsc 20121011
 */

$tuples = array( 
	array( "png", "png" ),
	array( "gif", "gif" ),
	array( "jpg", "jpg" ),
	);

foreach( $tuples as $tuple ){
	$obj = new \Synrgic\MediaType();
	$obj->setType($tuple[0]);
	$obj->setDescription($tuple[1]);
	$em->persist($obj);
}

$em->flush();

?>
