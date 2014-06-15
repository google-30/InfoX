<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

$tuples = array( 
           array(
               'model'=>array("Adverts", "Adverts"),
               'items'=> array (
                       array('blocks',1, 1),
                       array('minutes', 30, 0.5),
                       )
                   ),   
           array(
    	       'model'=>array( "Accumlate", "AccumulateByItems"),
               'items'=>array( 
    	              array( "month", 1, 20),
                      array( "days", 2, 3)
                    )
                 ),
           array(
                'model'=>array( "Monthly", "Fixed" ),
                'items'=>array(
                       array("Month", 1, 100)
                    )
                 ),
          );

foreach( $tuples as $tuple ){
	$model = new \Synrgic\ChargeModel();
	$model->setName($tuple['model'][0]);
	$model->setAlgorithm($tuple['model'][1]);
	$em->persist($model);
        foreach($tuple['items'] as $item) {
		$obj = new \Synrgic\ChargeItem();
                $obj->model = $model;
                $obj->setName($item[0]);
                $obj->setUnits($item[1]);
                $obj->setPrice($item[2]);
		$em->persist($obj);
        }
}

$em->flush();

?>
