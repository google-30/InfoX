<?
/**
 * Define some default accounts
 */

$tuples = array( 
    /* username, name, disabled, language, password, role */
    /*	 
	 array( "admin", "Administrator", "admin@somewhere.com", false, "English", "admin", "admin","1"),
	 array( "staff", "Staff member", "staff@somewhere.com", false, "English", "staff", "staff","1"),
	 array( "boss", "业主", "boss@somewhere.com", false, "English", "boss", "boss","1"),
	 array( "manager", "经理", "manager@somewhere.com", false, "English", "manager", "manager","1"),
	 array( "leader", "工长", "leader@somewhere.com", false, "English", "leader", "leader","1"),
	 array( "officer", "行政", "officer@somewhere.com", false, "English", "officer", "officer","1"),
	 array( "hr", "人事", "humanresource@somewhere.com", false, "English", "hr", "hr","1"),
    */
	 array( "manager", "经理", false, "Chinese", "1234", "manager",),
	 array( "leader", "工长",  false, "Chinese", "1234", "leader",),
	 array( "staff", "管理人员", false, "Chinese", "1234", "staff",),
	);

foreach( $tuples as $tuple ){
	$user = new \Synrgic\User();
	$user->setUsername($tuple[0]);
	$user->setName($tuple[1]);	
	$user->setDisabled($tuple[2]);	

	$language = $em->getRepository('\Synrgic\Language')->findOneByName($tuple[3]);
	$user->setpreferredLanguage($language);

	// Handle Password
	$pwhelper = new Synrgic_Models_PasswordHelper();
	$cryptedPassword = $pwhelper->cryptPassword($tuple[4]);
	$user->setPassword($cryptedPassword);

	$user->setRole($tuple[5]);

	$em->persist($user);
}

$em->flush();

?>
