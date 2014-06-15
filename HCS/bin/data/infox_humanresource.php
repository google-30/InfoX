<?php
/**
 * humanresource
 */

$tuples = array( 
/* id, name, nameeng, date, phone1, phone2, email1, email2, othercontact, position, remark, username, password */
array(1, "张一","zhangyi", "2012-10-01", "11111111", 
"11112222", "zhangyi@gmail.com", "zhangyi111@gmail.com", "tampines1", "boss", 
"company boss", "zhangyi", "password"),
array(2, "王二","wanger", "2012-10-01", "22222222", 
"22223333", "wanger@gmail.com", "wanger111@gmail.com", "tampines2", "manager", 
"manage all projects", "wanger", "password"),
array(3, "李三","lisan", "2012-10-01", "11111111", 
"11112222", "lisan@gmail.com", "lisan111@gmail.com", "tampines3", "officer", 
"material officer", "lisan", "password"),
array(4, "王四","wangsi", "2012-10-01", "11111111", 
"11112222", "wangsi@gmail.com", "wangsi111@gmail.com", "tampines1", "leader", 
"工长", "wangsi", "password"),
array(5, "赵五","zhaowu", "2012-10-01", "11111111", 
"11112222", "zhaowu@gmail.com", "zhaowu111@gmail.com", "tampines1", "leader", 
"工长", "zhaowu", "password"),

);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Humanresource();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    $metadata = $em->getClassMetaData(get_class($data));
    $metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    $data->setId($tuple[0]);

    $data->setName($tuple[1]);
    $data->setNameeng($tuple[2]);
    $data->setDate(new DateTime($tuple[3]));
    $data->setPhone1($tuple[4]);
    $data->setPhone2($tuple[5]);
    $data->setEmail1($tuple[6]);
    $data->setEmail2($tuple[7]);
    $data->setOthercontact($tuple[8]);
    $data->setRemark($tuple[10]);
    $data->setUsername($tuple[11]);
    $data->setPassword($tuple[12]);

    $role = $em->getRepository('\Synrgic\Infox\Role')->findOneBy(array('role'=>$tuple[9]));
    $data->setRole($role);
    
    $em->persist($data);
}

$em->flush();

?>
