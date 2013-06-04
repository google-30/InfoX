<?
/**
 * archive info
 */

$tuples = array( 
/* id, update, title, path, size, remark, type*/
array(0, new DateTime("2012-10-01"),"绘图软件1", "" , 20480, "绘图软件", "sw"),
array(0,new DateTime("2012-11-01"),"办公软件1", "" , 204800, "办公软件", "sw"),
array(0,new DateTime("2012-12-01"),"财务软件1", "" , 2048000, "财务软件", "sw"),

array(0,new DateTime("2012-01-01"),"报价文档1", "" , 10240, "报价文档", "doc"),
array(0,new DateTime("2012-02-01"),"项目文档1", "" , 102400, "项目文档", "doc"),
array(0,new DateTime("2012-03-01"),"工资文档1", "" , 1024000, "工资文档", "doc"),
);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Archive();

    $data->setUpdate($tuple[1]);
    $data->setTitle($tuple[2]);
    $data->setPath($tuple[3]);
    $data->setSize($tuple[4]);
    $data->setRemark($tuple[5]);
    $data->setType($tuple[6]);

    $em->persist($data);
}

$em->flush();

?>
