<?
/**
 * worker demo data
 */

$tuples = array( 
/* namechs, nameeng, finno, age, birth, skills, pic*/
array("张一", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/Philo-Macklemore-CLICK.jpg"),
array("张二", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("张三", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("张四", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("张五", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),

array("周一", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/Philo-Macklemore-CLICK.jpg"),
array("周二", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/Philo-Macklemore-CLICK.jpg"),
array("周三", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/Philo-Macklemore-CLICK.jpg"),
array("周四", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("周五", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),

array("王一", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("王二", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("王三", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("王四", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("王五", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),

array("李一", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/Philo-Macklemore-CLICK.jpg"),
array("李二", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("李三", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("李四", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),
array("李五", "zhang yi", "12345678", "32", "Electrician,Bricklayer", "/example/images/ted_logo.jpg"),

	);

foreach( $tuples as $tuple ){
	$data = new \Synrgic\Infox\Worker();
	$data->setNamechs($tuple[0]);
	$data->setNameeng($tuple[1]);
    $data->setFinno($tuple[2]);
    $data->setAge($tuple[3]);
    $data->setSkills($tuple[4]);
    $data->setPic($tuple[5]);
	$em->persist($data);
}

$em->flush();

?>
