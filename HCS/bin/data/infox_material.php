<?
/**
 * material demo data
 */

$tuples = array( 
/* name, onlinedate, price, warehouse, macrotype, detailtype*/
array("hammer01", new DateTime("2012-10-01"), 10.0, "orchard", "mechanic", "heavy"),
array("hammer02", new DateTime("2012-10-02"), 20.0, "orchard", "mechanic", "heavy"),
array("hammer03", new DateTime("2012-10-03"), 30.0, "orchard", "mechanic", "heavy"),
array("锤子04", new DateTime("2012-10-04"), 40.0, "tampines", "mechanic", "heavy"),
array("郎头05", new DateTime("2012-10-05"), 50.0, "tampines", "mechanic", "heavy"),

array("drill01", new DateTime("2012-10-01"), 10.0, "orchard","mechanic", "electronic"),
array("drill02", new DateTime("2012-10-02"), 20.0, "orchard","mechanic", "electronic"),
array("drill03", new DateTime("2012-10-03"), 30.0, "orchard","mechanic", "electronic"),
array("电钻04", new DateTime("2012-10-04"), 40.0, "tampines","mechanic", "electronic"),
array("钻孔机05", new DateTime("2012-10-05"), 50.0, "tampines","mechanic", "electronic"),

array("cement01", new DateTime("2012-10-01"), 10.0, "orchard","material", "consumable"),
array("cement02", new DateTime("2012-10-02"), 20.0, "orchard","material", "consumable"),
array("cement03", new DateTime("2012-10-03"), 30.0, "orchard","material", "consumable"),
array("水泥04", new DateTime("2012-10-04"), 40.0, "tampines","material", "building"),
array("水泥05", new DateTime("2012-10-05"), 50.0, "tampines","material", "building"),

	);

foreach( $tuples as $tuple ){
	$data = new \Synrgic\Infox\Material();
	$data->setName($tuple[0]);
	$data->setOnlinedate($tuple[1]);
    $data->setPrice($tuple[2]);
    $data->setWarehouse($tuple[3]);
    $data->setMacrotype($tuple[4]);
    $data->setDetailtype($tuple[5]);
	$em->persist($data);
}

$em->flush();

?>
