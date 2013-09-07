<?
/**
 * material types
 * data is from material_list.xls
 * these data is essential
 */

$tuples = array( 
/* id, typechs, typeeng, main */

array(1, "safety material", "安全材料",),
array(1, "formwork", "模板",),
array(1, "concrete", "混凝土",),
array(1, "rebar", "螺纹钢",),
array(1, "equipment", "电动机具",),
array(1, "electrical", "电子器件",),
array(1, "worker domitory", "工人宿舍",),
array(1, "logistics", "后勤",),
array(1, "water pipe", "水管",),
array(1, "spare parts", "备件",),
array(1, "scaffolding", "脚手架",),
);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Materialtype();

    $data->setTypeeng($tuple[1]);
    $data->setTypechs($tuple[2]);

    $em->persist($data);
}

$em->flush();
?>
