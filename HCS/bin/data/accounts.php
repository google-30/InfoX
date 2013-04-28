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
 * Define some default accounts
 */

$tuples = array( 
   	       /* username, name, email, disabled, language, password, role, provider */	 
	 array( "admin", "Administrator", "admin@somewhere.com", false, "English", "admin", "admin","1"),
	 array( "admincn", "Administrator", "admin@somewhere.com", false, "Chinese", "admin", "admin","1"  ),
	 array( "staff", "Staff member", "staff@somewhere.com", false, "English", "staff", "staff","1"),
	 array( "staffcn", "Staff member", "staff@somewhere.com", false, "Chinese", "staff", "staff","3"),
	 array( "test", "SW Tester", "test@somewhere.com", false, "English", "test", "staff","1"),
	 array( "manager", "Project Manager", "projectmanager@somewhere.com", false, "English", "manager", "manager","1"),
	 array( "leader", "Team Leader", "teamleader@somewhere.com", false, "English", "leader", "leader","1"),
	 array( "hr", "human resource", "humanresource@somewhere.com", false, "English", "hr", "hr","1"),
	);

foreach( $tuples as $tuple ){
	$user = new \Synrgic\User();
	$user->setUsername($tuple[0]);
	$user->setName($tuple[1]);
	$user->setEmail($tuple[2]);
	$user->setDisabled($tuple[3]);
	

	$provider=$em->getRepository('\Synrgic\Service\Provider')->findOneById( $tuple[7]);
	$user->setprovider($provider);
	

	// Find language
	$language = $em->getRepository('\Synrgic\Language')->findOneByName($tuple[4]);
	$user->setpreferredLanguage($language);

	// Handle Password
	$pwhelper = new Synrgic_Models_PasswordHelper();
	$cryptedPassword = $pwhelper->cryptPassword($tuple[5]);
	$user->setRole($tuple[6]);
	$user->setPassword($cryptedPassword);
	$em->persist($user);
}

$em->flush();

?>
