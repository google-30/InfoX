<?
/**
 * worker personal info data
 */

$tuples = array( 
/* id, worktype, worklevel, education, pastwork, skill1, skill2, drvlic, securityexp*/
array(1, "木工", "普通", "high school", "木工", "木工技术", "电工技术", "无驾照", new DateTime("2021-10-01")),
array(2, "瓦工", "好", "degree",       "瓦工", "瓦工技术", "木工技术", "中国B驾照", new DateTime("2021-10-01")),
array(3, "电工", "很好", "master",      "电工", "电工技术", "瓦工技术", "国际驾照", new DateTime("2021-10-01")),
array(4, "木工", "普通", "high school", "木工", "木工技术", "电工技术", "无驾照", new DateTime("2021-10-01")),
array(5, "瓦工", "好", "degree",       "瓦工", "瓦工技术", "木工技术", "中国B驾照", new DateTime("2021-10-01")),

);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Workerskill();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    $metadata = $em->getClassMetaData(get_class($data));
    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    $data->setId($tuple[0]);

    $data->setWorktype($tuple[1]);
    $data->setWorklevel($tuple[2]);
    $data->setEducation($tuple[3]);
    $data->setPastwork($tuple[4]);
    $data->setSkill1($tuple[5]);
    $data->setSkill2($tuple[6]);
    $data->setDrvlic($tuple[7]);
    $data->setSecurityexp($tuple[8]);

    $em->persist($data);
}

$em->flush();

?>
