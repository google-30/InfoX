<?
/**
 * This file defines default user changable settings
 */

$tuples = array( 
/* Name      Value         Section             Description */	 
array( "cotmultiple", "1", "salary", "华人加班倍率" ),
array( "botmultiple", "1.5", "salary", "孟加拉人加班倍率"),
array( "workerfood", "4.5", "salary", "工人伙食" ),
array( "leaderfood", "3", "salary", "工长伙食" ),
array( "absencedays", "4", "salary", "旷工天数界限" ),
array( "absencelow", "10", "salary", "低于界限扣款" ),
array( "absencehigh", "30", "salary", "高于界限扣款" ),
array( "absencetotal", "350", "salary", "扣款上限" ),

array( "cbasic", "600", "salary", "华人基本工资" ),
array( "bbasic", "400", "salary", "孟加拉人基本工资" ),
	);

foreach( $tuples as $tuple ){
	$setting = new \Synrgic\Infox\Setting();
	$setting->setName($tuple[0]);
	$setting->setValue($tuple[1]);
	$setting->setSection($tuple[2]);
	$setting->setDescription($tuple[3]);
	$em->persist($setting);
}

$em->flush();

?>
