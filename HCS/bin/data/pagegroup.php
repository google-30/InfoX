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
 * PageGroup
 */

$tuples = array( 
	array(1),
    array(2), 
    array(3),    
	);

foreach( $tuples as $tuple ){
	$data = new \Synrgic\PageGroup();
    $data->setId($tuple[0]);    
	$em->persist($data);
}

$em->flush();

?>
