<?
/**
 * site info data - 工地
 */

$tuples = array( 
/* id, name, address, start, stop, workerno, leader, manager*/
array(1, "tampines condo", "tampines", new DateTime("2012-10-01"), new DateTime("2014-10-01"), 
50, 4, 2),
array(2, "orchard condo", "orchard", new DateTime("2012-10-01"), new DateTime("2014-11-01"), 
70, 4, 2),
array(3, "jurong east condo", "jurong east", new DateTime("2012-10-01"), new DateTime("2014-12-01"), 
50, 5, 2),
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

    $obj = $em->getRepository('\Synrgic\Infox\Humanresource')->findOneBy(array('id'=>$tuple[6]));
    $data->setLeader($obj);

    $obj = $em->getRepository('\Synrgic\Infox\Humanresource')->findOneBy(array('id'=>$tuple[7]));
    $data->setManager($obj);

    $em->persist($data);
}

$em->flush();

?>
