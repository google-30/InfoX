<?php

/**
 * material types
 * data is from material_list.xls
 * these data is essential
 */
$tuples = array(
    /* id, typechs, typeeng, main */

    array(1, "safety material", "安全材料",),
    array(1, "Formwork", "模板",),
    array(1, "concrete", "混凝土",),
    array(1, "rebar", "螺纹钢",),
    array(1, "equipment", "电动机具",),
    array(1, "electrical", "电子器件",),
    array(1, "worker domitory", "工人宿舍",),
    array(1, "Logistic", "后勤",),
    array(1, "water pipe", "水管",),
    array(1, "spare parts", "备件",),
        /* array(1, "scaffolding", "脚手架",), */
);

$data = new \Synrgic\Infox\Materialtype();
$cmd = $em->getClassMetadata(get_class($data));
$connection = $em->getConnection();
$dbPlatform = $connection->getDatabasePlatform();
$connection->beginTransaction();
try {
//$connection->query('SET FOREIGN_KEY_CHECKS=0');
    $q = $dbPlatform->getTruncateTableSql($cmd->getTableName(), true);
    $connection->executeUpdate($q);
//$connection->query('SET FOREIGN_KEY_CHECKS=1');
    $connection->commit();
} catch (\Exception $e) {
    $connection->rollback();
    var_dump($e);
    return;
}

foreach ($tuples as $tuple) {
    $data = new \Synrgic\Infox\Materialtype();

    $data->setTypeeng($tuple[1]);
    $data->setTypechs($tuple[2]);

    $em->persist($data);
    $em->flush();

    $data = $em->getRepository('Synrgic\Infox\Materialtype')->findOneBy(array("typeeng" => $tuple[1]));
    if ($data) {
        $data->setMain($data);
    }
    $em->persist($data);
    $em->flush();
}
//$em->flush();
?>
