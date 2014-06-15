<?php
/**
 * worker personal info data
 */

$tuples = array( 
/* id, homeaddr, member1, contact1, member2, contact2, member3, contac3, securityexp*/
array(1, "北京", "父亲", "86-21-11111111", "母亲", 
"mother@gmail.com", "哥哥", "86-10-22222222"),
array(2, "北京", "父亲", "86-21-11111111", "母亲", 
"mother@gmail.com", "哥哥", "86-10-22222222"),
array(3, "北京", "父亲", "86-21-11111111", "母亲", 
"mother@gmail.com", "哥哥", "86-10-22222222"),
array(4, "北京", "父亲", "86-21-11111111", "母亲", 
"mother@gmail.com", "哥哥", "86-10-22222222"),
array(5, "北京", "父亲", "86-21-11111111", "母亲", 
"mother@gmail.com", "哥哥", "86-10-22222222"),

);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Workerfamily();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    $metadata = $em->getClassMetaData(get_class($data));
    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    $data->setId($tuple[0]);

    $data->setHomeaddr($tuple[1]);
    $data->setMember1($tuple[2]);
    $data->setContact1($tuple[3]);
    $data->setMember2($tuple[4]);
    $data->setContact2($tuple[5]);
    $data->setMember3($tuple[6]);
    $data->setContact3($tuple[7]);

    $em->persist($data);
}

$em->flush();

?>
