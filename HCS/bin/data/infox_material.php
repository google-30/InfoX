<?
/**
 * material demo data
 */

$tuples = array( 
/* name, onlinedate, price, warehouse, macrotype, detailtype, supplier, nameeng, */
array("锤子1号", new DateTime("2012-10-01"), 10.0, "orchard", "mechanic", 
"heavy",1,"hammer01"),
array("锤子2号", new DateTime("2012-10-02"), 20.0, "orchard", "mechanic", 
"heavy",1,"hammer02"),
array("锤子3号", new DateTime("2012-10-03"), 30.0, "orchard", "mechanic", 
"heavy",1,"hammer03"),
array("锤子4号", new DateTime("2012-10-04"), 40.0, "tampines", "mechanic", 
"heavy",1,"hammer04"),
array("锤子5号", new DateTime("2012-10-05"), 50.0, "tampines", "mechanic", 
"heavy",1,"hammer05"),

array("电钻1号", new DateTime("2012-10-01"), 10.0, "orchard","mechanic", 
"electronic",2,"drill01"),
array("电钻2号", new DateTime("2012-10-02"), 20.0, "orchard","mechanic", 
"electronic",2,"drill02"),
array("电钻3号", new DateTime("2012-10-03"), 30.0, "orchard","mechanic", 
"electronic",2,"drill03"),
array("电钻4号", new DateTime("2012-10-04"), 40.0, "tampines","mechanic", 
"electronic",2,"drill04"),
array("电钻5号", new DateTime("2012-10-05"), 50.0, "tampines","mechanic", 
"electronic",2,"drill05"),

array("水泥1号", new DateTime("2012-10-01"), 10.0, "orchard","material", 
"consumable",3,"cement01"),
array("水泥2号", new DateTime("2012-10-02"), 20.0, "orchard","material", 
"consumable",3,"cement02"),
array("水泥3号", new DateTime("2012-10-03"), 30.0, "orchard","material", 
"consumable",3,"cement03"),
array("水泥4号", new DateTime("2012-10-04"), 40.0, "tampines","material", 
"building",3,"cement04"),
array("水泥5号", new DateTime("2012-10-05"), 50.0, "tampines","material", 
"building",3,"cement05"),

	);

foreach( $tuples as $tuple ){
	$data = new \Synrgic\Infox\Material();
	$data->setName($tuple[0]);
	$data->setOnlinedate($tuple[1]);
    $data->setPrice($tuple[2]);
    $data->setWarehouse($tuple[3]);
    $data->setMacrotype($tuple[4]);
    $data->setDetailtype($tuple[5]);

    $supplier = $em->getRepository('\Synrgic\Infox\Supplier')->findOneBy(array('id'=>$tuple[6]));
    $data->setSupplier($supplier);

    $data->setNameeng($tuple[7]);

	$em->persist($data);
}

$em->flush();

?>
