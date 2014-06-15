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
 * This file defines default services of request services
 */

$tuples = array(/*catalog_id name click_count price key_words is_saleis_topis_deleted top_index is_new new_indexorg_picture  iconintroduction remark addedtime type provider_ID*/
 array("2", "Taxi","0", "0.00", "taxi","1","0","0", "-1", "0", "-1", "/services-pic/taxi.gif", "/services-pic/taxi.gif","Taxi","t",new DateTime ('now'),"2","3",),

 array("3", "Clean Room","0", "0.00", "Clean Room","1","0","0", "-1", "0", "-1", "/services-pic/taxi.gif", "/services-pic/taxi.gif","Clean Room","t",new DateTime ('now'),"3","2",),
 array("3", "CocaCola From Mini Bar","0", "2.00", "Drink From Mini Bar","1","0","0", "-1", "0", "-1", "", "","Drink From Mini Bar","t",new DateTime ('now'),"3","2",),
 array("3", "Wine From Mini Bar","0", "2.00", "Drink From Mini Bar","1","0","0", "-1", "0", "-1", "", "","Drink From Mini Bar","t",new DateTime ('now'),"3","2",),
 array("3", "Bread from Fridge","0", "4.00", "Food from Fridge","1","0","0", "-1", "0", "-1", "", "","Food from Fridge","t",new DateTime ('now'),"3","2",),
 array("3", "Sausage from Fridge","0", "4.00", "Food from Fridge","1","0","0", "-1", "0", "-1", "", "","Food from Fridge","t",new DateTime ('now'),"3","2",),
 array("3", "Bed Stuff","0", "6.00", "Bed Stuff","1","0","0", "-1", "0", "-1", "", "","Bed Stuff","t",new DateTime ('now'),"3","2",),
 array("3", "Table Stuff","0", "8.00", "Table Stuff","1","0","0", "-1", "0", "-1", "", "","Table Stuff","t",new DateTime ('now'),"3","2",),
 array("3", "Room A","0", "100.00", "room","1","0","0", "-1", "0", "-1", "", "","Room A night cost","t",new DateTime ('now'),"3","2",),
 array("3", "Room B","0", "200.00", "room","1","0","0", "-1", "0", "-1", "", "","Room B night cost","t",new DateTime ('now'),"3","2",),
 array("3", "Room C","0", "300.00", "room","1","0","0", "-1", "0", "-1", "", "","Room C night cost","t",new DateTime ('now'),"3","2",),
 array("3", "Room D","0", "400.00", "room","1","0","0", "-1", "0", "-1", "", "","Room D night cost","t",new DateTime ('now'),"3","2",),
 array("3", "Room E","0", "500.00", "room","1","0","0", "-1", "0", "-1", "", "","Room E night cost","t",new DateTime ('now'),"3","2",),

 //array("4", "apple juice", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/9.jpg", "/services-pic/7.jpg", "apple juice","t",new DateTime ('now'),"1","1",),
 //array("4", "Orange juice", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/8.jpg", "/services-pic/8.jpg", "Orange juice","t",new DateTime ('now'),"1","1",),
 //array("5", "duck","0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/5.jpg", "/services-pic/6.jpg", "duck","t",new DateTime('now'),"1","1",),
 //array("6", "rice","0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/3.jpg", "/services-pic/4.jpg", "rice","t",new DateTime('now'),"1","1",),
 
 array("4", "Pearl milk tea", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/6868.jpg", "/services-pic/6868.jpg", "Pearl milk tea with black sesame","t",new DateTime ('now'),"1","1",),
 array("4", "Strawberry milkshake", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/1042.jpg", "/services-pic/1042.jpg", "Strawberry milkshake","t",new DateTime ('now'),"1","1",),
 array("4", "Vanilla milkshake", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/1043.jpg", "/services-pic/1043.jpg", "Vanilla milkshake","t",new DateTime ('now'),"1","1",),
 array("4", "Fresh coffee", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/3518.jpg", "/services-pic/3518.jpg", "Fresh coffee","t",new DateTime ('now'),"1","1",),
 array("4", "Coke", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/3000.jpg", "/services-pic/3000.jpg", "cocacola","t",new DateTime ('now'),"1","1",),
 array("4", "Sprite", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/3100.jpg", "/services-pic/3100.jpg", "Sprite","t",new DateTime ('now'),"1","1",),
 array("4", "Pure Milk", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/3755.jpg", "/services-pic/3755.jpg", "Pure Milk","t",new DateTime ('now'),"1","1",),
 array("4", "Black tea", "0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/3505.jpg", "/services-pic/3505.jpg", "Black tea","t",new DateTime ('now'),"1","1",),
 
 array("5", "Korean roast pork","0", "18.00", "food","1","1","0", "1", "1", "1", "/services-pic/hskr.jpg", "/services-pic/hskr.jpg", "Promote growth and development to improve the iron deficiency anemia enhance memory","t",new DateTime('now'),"1","1",),
 array("5", "Grilled steak","0", "25.00", "food","1","1","0", "1", "1", "1", "/services-pic/knp.jpg", "/services-pic/knp.jpg", "Promote gastrointestinal peristalsis down the blood pressure qingfei appetizer hepatoprotective anti-cancer","t",new DateTime('now'),"1","1",),
 array("5", "Spiced volume","0", "20.00", "food","1","1","0", "1", "1", "1", "/services-pic/wxj.jpg", "/services-pic/wxj.jpg", "Pork","t",new DateTime('now'),"1","1",),
 array("5", "Salted duck liver","0", "20.00", "food","1","1","0", "1", "1", "1", "/services-pic/ysyg.jpg", "/services-pic/ysyg.jpg", "Very delicate taste, duck liver after the bubble, boiled water, rinse, pepper saline the little wake taste after cooking also do not have a lot of complex spices, a touch of pepper aroma, slightly salty","t",new DateTime('now'),"1","1",),
 array("5", "Sauce tofu","0", "20.00", "food","1","1","0", "1", "1", "1", "/services-pic/jzdf.jpg", "/services-pic/jzdf.jpg", "Slightly acidic, salty taste, tofu plump and juicy, tender of coke. Favorably with a variety of side dishes, carrots, crisp green peppers, mushrooms, creamy, delicious ham. Rich soup appetizer","t",new DateTime('now'),"1","1",),
 array("5", "Braised pork ribs","0", "30.00", "food","1","1","0", "1", "1", "1", "/services-pic/hspg.jpg", "/services-pic/hspg.jpg", "Braised pork ribs","t",new DateTime('now'),"1","1",),
 array("5", "Broth, tofu pot","0", "22.00", "food","1","1","0", "1", "1", "1", "/services-pic/fcdf.jpg", "/services-pic/fcdf.jpg", "Broth, tofu pot","t",new DateTime('now'),"1","1",),
 array("5", "Braised Prawns","0", "32.00", "food","1","1","0", "1", "1", "1", "/services-pic/hmdx.jpg", "/services-pic/hmdx.jpg", "Braised Prawns","t",new DateTime('now'),"1","1",),
 array("5", "Glutton Griddle chicken","0", "24.00", "food","1","1","0", "1", "1", "1", "/services-pic/ggj.jpg", "/services-pic/ggj.jpg", "Dry fruity, the tempeh the flavor and the variety of vegetables, chicken fusion","t",new DateTime('now'),"1","1",),
 
 array("6", "Spicy Hele","0", "18.00", "food","1","1","0", "1", "1", "1", "/services-pic/mlql.jpg", "/services-pic/mlql.jpg", "Changfeng swallow in the desert, calendar bitterly cold in the wilderness, the beginning of the end of 365 days was declared perfect, in the appearance of fine grind generosity heart, talent dietary fiber and a variety of trace elements to any rubbing hammer steamed and independence into a grid,not help people lingering aftertaste.","t",new DateTime('now'),"1","1",),
 array("6", "Beef noodles","0", "15.00", "food","1","1","0", "1", "1", "1", "/services-pic/nrm.jpg", "/services-pic/nrm.jpg", "Beef noodles","t",new DateTime('now'),"1","1",),
 array("6", "Sansho surface","0", "20.00", "food","1","1","0", "1", "1", "1", "/services-pic/sjm.jpg", "/services-pic/sjm.jpg", "Sansho surface","t",new DateTime('now'),"1","1",),
 array("6", "Fine dry noodles","0", "18.00", "food","1","1","0", "1", "1", "1", "/services-pic/gbm.jpg", "/services-pic/gbm.jpg", "Fine dry noodles","t",new DateTime('now'),"1","1",),
 
 array("7", "Pure Milk", "0", "8.00", "food","1","1","0", "1", "1", "1", "/services-pic/3755.jpg", "/services-pic/3755.jpg", "Pure Milk","t",new DateTime ('now'),"1","1",),
 array("7", "Black tea", "0", "8.00", "food","1","1","0", "1", "1", "1", "/services-pic/3505.jpg", "/services-pic/3505.jpg", "Black tea","t",new DateTime ('now'),"1","1",),
 array("7", "Fine dry noodles","0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/gbm.jpg", "/services-pic/gbm.jpg", "Fine dry noodles","t",new DateTime('now'),"1","1",),
 array("7", "Braised Prawns","0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/hmdx.jpg", "/services-pic/hmdx.jpg", "Braised Prawns","t",new DateTime('now'),"1","1",),
 array("7", "Glutton Griddle chicken","0", "10.00", "food","1","1","0", "1", "1", "1", "/services-pic/ggj.jpg", "/services-pic/ggj.jpg", "Dry fruity, the tempeh the flavor and the variety of vegetables, chicken fusion","t",new DateTime('now'),"1","1",),
);

foreach( $tuples as $tuple ){
$catalog = $em->getRepository('\Synrgic\Service\Catalog')->findOneBy(array('id'=>$tuple[0]));
$provider=$em->getRepository('\Synrgic\Service\Provider')->findOneBy(array('id'=>$tuple[17]));
$services = new \Synrgic\Service\Service();
$services->setName($tuple[1]);
$services->setClick_count($tuple[2]);
$services->setPrice($tuple[3]);
$services->setKey_words($tuple[4]);
$services->setIs_sale($tuple[5]);
$services->setIs_top($tuple[6]);
$services->setIs_deleted($tuple[7]);
$services->setTop_index($tuple[8]);
$services->setIs_new($tuple[9]);
$services->setNew_index($tuple[10]);
$services->setOrg_picture($tuple[11]);
$services->setIcon($tuple[12]);
$services->setIntroduction($tuple[13]);
$services->setRemark($tuple[14]);
$services->setAdd_time($tuple[15]);
$services->setType($tuple[16]);
$services->setCategory($catalog);
$services->setProvider($provider);
$em->persist($services);
}

$em->flush();

?>
