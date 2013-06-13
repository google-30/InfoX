<?
/**
 * suppliers
 */

$tuples = array( 
/* id, update, name, address, officephone, mobilephone, email, remark, business, contact */
array(1, new DateTime("2012-10-01"), "Dafa Construction", "tampines 1, 520846", "11111111", 
"11112222", "dafa@gmail.com", "sell everything; do business with us for 5 years.", "Electronic Equipment", "Mr. Zhang"),
array(2, new DateTime("2012-11-01"), "Star", "tampines 1, 520846", "22222222", 
"11112222", "star@gmail.com", "sell everything; do business with us for 10 years.", "cement", "Mr. Wang"),
array(3, new DateTime("2012-12-01"), "Moonwalker", "tampines 1, 520846", "33333333", 
"11112222", "moonwalker@gmail.com", "sell everything; do business with us for 15 years.", "dumplings", "Mr. Zhao"),
);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Supplier();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    $metadata = $em->getClassMetaData(get_class($data));
    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    $data->setId($tuple[0]);

    $data->setUpdate($tuple[1]);
    $data->setName($tuple[2]);
    $data->setAddress($tuple[3]);
    $data->setOfficephone($tuple[4]);
    $data->setMobilephone($tuple[5]);
    $data->setEmail($tuple[6]);
    $data->setRemark($tuple[7]);
    $data->setBusiness($tuple[8]);
    $data->setContact($tuple[9]);

    $em->persist($data);
}

$em->flush();

?>
