<?
/**
 * miscinfo
 * kinds of simple information, for staff editing
 * these data is important for modules like project/site
 */

$tuples = array( 
/* id, label, namechs, nameeng, values */
/*
array(1, "info01", "总承包单位", "General Contractor", "国家建设;铁路建设;"),
array(2, "info02", "工程性质", "Site Property", "公寓;组屋;学校;幼儿园;"),
array(5, "info05", "设备状态", "Emachinery Status", "正常;遗失;修理;损坏;报废"),
array(6, "info06", "材料单位", "Material Unit", "把;台;袋;包;捆;个;套;"),
*/

array(7, "01", "工人详细信息", "Worker Details", "自定义字段1;自定义字段2;自定义字段3;自定义字段4;", "worker", 4),
array(3,"info03", "工人考勤", "Worker Attendance", "病假;有薪假;无薪假;出差;其他事由;", "worker",),
array(4, "info04", "工种", "Worker Type", "木工;电工;油漆工;瓦工;", "worker",),

);

foreach( $tuples as $tuple ){
    $data = new \Synrgic\Infox\Miscinfo();
    $data->setLabel($tuple[1]);
    $data->setNamechs($tuple[2]);
    $data->setNameeng($tuple[3]);
    $data->setValues($tuple[4]);

    if(array_key_exists(5,$tuple)) 
    {
        $data->setCategory($tuple[5]);
    }

    if(array_key_exists(6,$tuple))
    {
        $data->setAmount($tuple[6]);
    }

    $em->persist($data);
}

$em->flush();
?>
