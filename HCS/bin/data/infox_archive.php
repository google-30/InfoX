<?
/**
 * archive info
 */

$tuples = array( 
/* id, update, title, path, size, remark, type*/
array(1, "tampines condo", "tampines", new DateTime("2012-10-01"), new DateTime("2014-10-01"), 50),
array(2, "orchard condo", "orchard", new DateTime("2012-10-01"), new DateTime("2014-10-01"), 50),
array(3, "jurong east condo", "jurong east", new DateTime("2012-10-01"), new DateTime("2014-10-01"), 50),
);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Site();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    $metadata = $em->getClassMetaData(get_class($data));
    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    $data->setId($tuple[0]);

    $data->setName($tuple[1]);
    $data->setAddress($tuple[2]);
    $data->setStart($tuple[3]);
    $data->setStop($tuple[4]);
    $data->setWorkerno($tuple[5]);

    $em->persist($data);
}

$em->flush();

?>
