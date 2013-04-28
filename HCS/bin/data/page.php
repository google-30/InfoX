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
 * page
 */

$tuples = array( 
    /* id, language, group, state, content, created, previousrevision,remark */
	array( 1,1,1,1,"content", new DateTime("2012-01-01"), 0, "remark"),
	array( 2,1,1,0,"content", new DateTime("2012-02-02"), 0, "remark"),
	array( 3,1,1,0,"content", new DateTime("2012-03-03"), 0, "remark"),	
	array( 4,1,1,0,"content", new DateTime("2012-04-04"), 0, "remark"),	
	array( 5,2,1,1,"content", new DateTime("2012-05-05"), 0, "remark"),	
	array( 6,2,1,0,"content", new DateTime("2012-06-06"), 0, "remark"),	
	array( 7,2,1,0,"content", new DateTime("2012-07-07"), 0, "remark"),	
	array( 8,2,1,0,"content", new DateTime("2012-08-08"), 0, "remark"),	
	);

foreach( $tuples as $tuple ){
	$data = new \Synrgic\Page();
    $data->setId($tuple[0]);
    $langobj = $em->getRepository('\Synrgic\Language')->findOneBy(array('id'=>$tuple[1]));
	$data->setLanguage($langobj);
    $groupobj = $em->getRepository('\Synrgic\PageGroup')->findOneBy(array('id'=>$tuple[2]));
	$data->setGroup($groupobj);
	$data->setState($tuple[3]);
	$data->setContent($tuple[4]);
	$data->setCreated($tuple[5]);
	$data->setRemark($tuple[6]);
	$em->persist($data);
}

$em->flush();

?>
