<?
/**
 * miscinfo
 * kinds of simple information, for staff editing
 * these data is important for modules like project/site
 */

$tuples = array( 
/* id, label, namechs, nameeng, values */
array(1, "info01", "总承包单位", "General Contractor", ""),
array(2, "info02", "工程性质", "Site Property", ""),
array(3, "info03", "工人考勤", "Worker Attendance", "病假;有薪假;无薪假;出差;其他事由;"),
array(4, "info04", "工种", "Worker Type", "木工;电工;油漆工;瓦工;"),
);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Miscinfo();

    // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
    // put it here to reset id generator
    //$metadata = $em->getClassMetaData(get_class($data));
    //$metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());
    //$data->setId($tuple[0]);

    $data->setLabel($tuple[1]);
    $data->setNamechs($tuple[2]);
    $data->setNameeng($tuple[3]);
    $data->setValues($tuple[4]);

    $em->persist($data);
}

$em->flush();
?>
