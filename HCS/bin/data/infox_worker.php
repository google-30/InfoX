<?php
/**
 * worker personal info data
 */

$tuples = array( 
/* id, nameeng,namechs, finno, passexp, passport, passportexp, age, birth, pic, 
address, marital, hometown, workercompanyinfo, workerskill, workerfamily, gender*/
array("张一", "zhang yi", "W1", new DateTime("2014-10-01"), "G1", 
new DateTime("2021-10-01"),"32", new DateTime("1980-10-01"), "/workers/0001.jpg", "10 Tampines Central 1, #05-35", 
"single", "Shanghai", 1,1,1,
1, "male"),
array("张二", "zhang er", "W2", new DateTime("2014-10-01"), "G1", 
new DateTime("2021-10-01"),"32", new DateTime("1980-10-01"), "/workers/0002.jpg", "10 Tampines Central 1, #05-35", 
"single", "Shanghai", 2,2,2,
2, "male"),
array("张三", "zhang san", "W3 ", new DateTime("2014-10-01"),"G1", 
new DateTime("2021-10-01"),"32", new DateTime("1980-10-01"), "/workers/0003.jpg", "10 Tampines Central 1, #05-35", 
"single", "Shanghai", 3,3,3,
3, "male"),
array("张四", "zhang si", "W4", new DateTime("2014-10-01"), "G1",
new DateTime("2021-10-01"),"32", new DateTime("1980-10-01"), "/workers/0004.jpg", "10 Tampines Central 1, #05-35", 
"married","Shanghai", 4,4,4,
4, "female"),
array("张五", "zhang wu", "W5", new DateTime("2014-10-01"), "G1", 
new DateTime("2021-10-01"),"32", new DateTime("1980-10-01"), "/workers/0005.jpg", "10 Tampines Central 1, #05-35",
"married","Shanghai", 5,5,5,
5, "female"),
);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Worker();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    $metadata = $em->getClassMetaData(get_class($data));
    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    $data->setId($tuple[12]);

    $data->setNamechs($tuple[0]);
    $data->setNameeng($tuple[1]);
    $data->setFin($tuple[2]);
    $data->setPassexp($tuple[3]);
    $data->setPassport($tuple[4]);
    $data->setPassportexp($tuple[5]);
    $data->setAge($tuple[6]);
    $data->setBirth($tuple[7]);
    $data->setPic($tuple[8]);
    $data->setAddress($tuple[9]);
    $data->setMarital($tuple[10]);
    $data->setHometown($tuple[11]);

    $obj = $em->getRepository('\Synrgic\Infox\Workercompanyinfo')->findOneBy(array('id'=>$tuple[13]));    
    $data->setWorkercompanyinfo($obj);

    $obj = $em->getRepository('\Synrgic\Infox\Workerskill')->findOneBy(array('id'=>$tuple[14]));    
    $data->setWorkerskill($obj);

    $obj = $em->getRepository('\Synrgic\Infox\Workerfamily')->findOneBy(array('id'=>$tuple[15]));    
    $data->setWorkerfamily($obj);

    $data->setGender($tuple[16]);

    $em->persist($data);
}

$em->flush();

?>
