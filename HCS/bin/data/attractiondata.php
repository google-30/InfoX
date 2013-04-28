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
 * Attractiondata
 */

$tuples = array( 
/* language, title, description, type, latitude, longitude, postcode, level */
	array( 1, "ION orchard", "<p><strong>ION orchard</strong></p><p><strong>shopping mall</strong></p>", 
"Shopping", 1.30495, 103.832495, "238801", 1, "ion"),
	array( 1, "Takashimaya", "<p><strong>Takashimaya</strong></p><p><strong>shopping mall</strong></p>", 
"Shopping", 1.30168, 103.83565, "238873", 2, "taka"),
	array( 1, "Nex", "<p><strong>Nex</strong></p><p><strong>shopping mall</strong></p>", 
"Shopping", 1.35038, 103.87324, "556083", 3, "nex"),
	array( 1, "VivoCity", "<p><strong>VivoCity</strong></p><p><strong>shopping mall</strong></p>", 
"Shopping", 1.26503, 103.82176, "098585", 4, "vivo"),

	array( 1, "Universal Studios", "Universal Studios",  
"Amusement", 1.254119, 103.821483, "099054", 1),
	array( 1, "Chinatown", "Chinatown", 
"Culture", 1.28444, 103.843376, "059422", 1),
	array( 1, "Singapore Flyer", '<p>Singapore Flyer</p><p>A moving experience</p>
<p><img src="http://www.singaporeflyer.com/wp-content/header-images/about-us.jpg" alt="" width="795" height="260" /></p>', 
"Relax", 1.289397,103.863231, "039803", 1),

    array( 2, "牛车水", "牛车水，中国城", 
"Culture", 1.28444, 103.843376, "Chinese", 1),
    array( 2, "环球影城", "圣淘沙名胜世界™邀请您进入电影的梦幻世界，感受“身历其境 体验电影”的奇妙旅程。无论是和坏人搏斗、在斗转星移间穿越时空，或者是在娱乐圈中大放异彩等，新加坡环球影城®里的过山车及其他游乐设施都将让您置身于电影世界里，体验成为“电影主角”的新奇与荣耀。", 
"Amusement", 1.254119, 103.821483, "099054", 1),
    array( 2, "爱昂乌接路", "重新诠释新加坡的零售、时尚与休闲体验", 
"Shopping", 1.30495, 103.832495, "238801", 1),
    array( 2, "新加坡摩天观景轮", "165米高的新加坡摩天观景轮是世界最高最大的巨型观景轮，也是亚洲最耀眼的旅游景点。", 
"Relax", 1.289397,103.863231, "039803", 1),
	);

foreach( $tuples as $tuple ){
	$data = new \Synrgic\LocalAttractions\Attractiondata();

    $langobj = $em->getRepository('\Synrgic\Language')->findOneBy(array('id'=>$tuple[0]));
    $data->setLanguage($langobj);
    
	$data->setTitle($tuple[1]);
	$data->setDescription($tuple[2]);
	$data->setType($tuple[3]);
	$data->setLatitude($tuple[4]);
	$data->setLongitude($tuple[5]);

    $data->setPostcode($tuple[6]);
    $data->setLevel($tuple[7]);
    if(array_key_exists(8, $tuple)) { $data->setSponsor($tuple[8]); }
	$em->persist($data);
}

$em->flush();

?>
