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
 * This file defines the languages supported by the HCS System
 *
 * TODO: Tie this in with the actual language bindings - benjsc 20121011
 */

$tuples = array( 
	array( "English", "en_US" ),
	array( "Chinese", "zh_CN" )
	);

foreach( $tuples as $tuple ){
	$lang = new \Synrgic\Language();
	$lang->setName($tuple[0]);
	$lang->setLocale($tuple[1]);
        $lang->setActive(true);
	$em->persist($lang);
}

$em->flush();

?>
