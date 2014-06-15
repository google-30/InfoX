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
 * Define Alert Categories
 */

$tuples = array( "Missed Call", "Status Update", "General Notice", "Notification");

foreach( $tuples as $tuple ){
	$cat = new \Synrgic\Noticeboard\AlertCategory();
	$cat->setName($tuple);
	$em->persist($cat);
}

$em->flush();

?>
