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
 * This file defines default user changable devicegroups 
 */

$tuples = array( 
   	       /* Name,     Description */	 
	 array( "Staff Devices",       "Devices Staff Usage" ),
	 array( "Guest Devices",       "Devices Used by Guests" ),
	 array( "Advertising Devices", "Devices Used Soley For Adverts" ),
	);

foreach( $tuples as $tuple ){
	$devicegroup = new \Synrgic\DeviceGroup();
	$devicegroup->setName($tuple[0]);
	$devicegroup->setDescription($tuple[1]);
	$em->persist($devicegroup);
}

$em->flush();

?>
