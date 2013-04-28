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
 * This file defines default provider of request services
 */

$tuples = array(     /*name			remark */	 
				array("kitchen","kitchen-r"),
				array("cleaner ","clear"),
				array("lobby ","hotel lobby"),
	);

foreach( $tuples as $tuple ){
	$privider = new \Synrgic\Service\Provider();
	$privider->setName($tuple[0]);
	$privider->setRemark($tuple[1]);
	$em->persist($privider);
}

$em->flush();

?>
