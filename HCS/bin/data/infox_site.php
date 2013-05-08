<?
/**
 * site info data - 工地
 */

$tuples = array( 
/* id, companylabel, hwage, site, srvyears, yrsinsing*/
array(1, "公司1", 75, 1, 5, 10),
array(2, "公司2", 80, 2, 10, 15),
array(3, "公司3", 85, 3, 15, 20),
);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Workercompanyinfo();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    $metadata = $em->getClassMetaData(get_class($data));
    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    $data->setId($tuple[0]);

    $data->setCompanylabel($tuple[1]);
    $data->setHwage($tuple[2]);
    $data->setSite($tuple[3]);
    $data->setSrvyears($tuple[4]);
    $data->setYrsinsing($tuple[5]);

    $em->persist($data);
}

$em->flush();

?>
