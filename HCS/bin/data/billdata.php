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
 * Bill data
 */

$tuples = array( 
    /* date, description, amount, room, room id, quantity, name, price*/
	array( new DateTime("2012-10-01"), "CocaCola From Mini Bar", "0","101",1,1,"CocaCola From Mini Bar",2.00),
	array( new DateTime("2012-10-01"), "Wine From Mini Bar",    "0", "101",1,2,"Wine From Mini Bar",2.00),
	array( new DateTime("2012-10-02"), "Bread from Fridge",     "0", "101",1,3,"Bread from Fridge",4.00),
	array( new DateTime("2012-10-02"), "Sausage from Fridge",   "0", "101",1,4,"Sausage from Fridge",4.00),
	array( new DateTime("2012-10-03"), "Bed Stuff",             "0", "101",1,5,"Bed Stuff",6.00),
	array( new DateTime("2012-10-10"), "Table Stuff",           "0", "101",1,6,"Table Stuff",8.00),

	array( new DateTime("2012-10-01"), "CocaCola From Mini Bar", "0","101",2,1,"CocaCola From Mini Bar",2.00),
	array( new DateTime("2012-10-01"), "Wine From Mini Bar",    "0", "101",2,2,"Wine From Mini Bar",2.00),
	array( new DateTime("2012-10-02"), "Bread from Fridge",     "0", "101",2,3,"Bread from Fridge",4.00),
	array( new DateTime("2012-10-02"), "Sausage from Fridge",   "0", "101",2,4,"Sausage from Fridge",4.00),
	array( new DateTime("2012-10-03"), "Bed Stuff",             "0", "101",2,5,"Bed Stuff",6.00),
	array( new DateTime("2012-10-10"), "Table Stuff",           "0", "101",2,6,"Table Stuff",8.00),

	array( new DateTime("2012-10-01"), "CocaCola From Mini Bar", "0","101",3,1,"CocaCola From Mini Bar",2.00),
	array( new DateTime("2012-10-01"), "Wine From Mini Bar",    "0", "101",3,2,"Wine From Mini Bar",2.00),
	array( new DateTime("2012-10-02"), "Bread from Fridge",     "0", "101",3,3,"Bread from Fridge",4.00),
	array( new DateTime("2012-10-02"), "Sausage from Fridge",   "0", "101",3,4,"Sausage from Fridge",4.00),
	array( new DateTime("2012-10-03"), "Bed Stuff",             "0", "101",3,5,"Bed Stuff",6.00),
	array( new DateTime("2012-10-10"), "Table Stuff",           "0", "101",3,6,"Table Stuff",8.00),

	);

foreach( $tuples as $tuple ){
	$bill = new \Synrgic\BillPreview\Billdata();
	$bill->setDate($tuple[0]);
	$bill->setDescription($tuple[1]);
	$bill->setAmount($tuple[2]);
	$bill->setRoom($tuple[3]);
    $room = $em->getRepository('\Synrgic\Room')->findOneBy(array('id'=>$tuple[4]));
    $bill->setPhysicalRoom($room);
    
	$bill->setQuantity($tuple[5]);
	$bill->setName($tuple[6]);
	$bill->setPrice($tuple[7]);

	$em->persist($bill);
}

$em->flush();

?>
