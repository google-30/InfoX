<?
/**
 * Define some roles in infox, roles are related with AclBuilder.php
 */

$tuples = array( 
    /* username, role, rolechs */
	 array( "manager", "经理",),
	 array( "leader", "工长",),
	 array( "staff", "管理人员"),
	);

foreach( $tuples as $tuple ){
	$data = new \Synrgic\Infox\Role();
	$data->setRole($tuple[0]);
	$data->setRolechs($tuple[1]);	

	$em->persist($data);
}

$em->flush();

?>
