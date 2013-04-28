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
 * This file defines default catalog of request services
 */

$tuples = array( /*father catalog_id name isdiplay */ 
  array("-1", "Room Service", "1"),
  array("-1", "Book a Taxi",  "1"),
  array("-1", "Other Service","1"),
  array("1", "Breakfast", "1"),
  array("1", "Lunch", "1"),
  array("1", "Dinner", "1"),
  array("1", "Specials", "1"),
);

foreach( $tuples as $tuple ){
$catalog = new \Synrgic\Service\Catalog();
$catalog->setFid($tuple[0]);
$catalog->setName($tuple[1]);
$catalog->setIs_display($tuple[2]);
$em->persist($catalog);
}

$em->flush();

?>
