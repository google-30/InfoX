<?php

class infox_project
{
    //private static $_em = Zend_Registry::get('em');
    /*
    private $_ctl;
    private function __construct() { }
    private function __construct($controller) 
    {
        $_ctl = $controller; 
    }
    */

    public static function getAttendanceByWorkerMonth($workerarr, $month)
    {
        $_em = Zend_Registry::get('em');
        $_siteatten = $_em->getRepository('Synrgic\Infox\Siteattendance');

        $attenarr = array();
        foreach($workerarr as $tmp)
        {
            $record = $_siteatten->findOneBy(array("worker"=>$tmp, "month"=>$month));
            if($record)
            {
                $attenarr[] = $record;
            }
        }

        return $attenarr;
    }

    public static function getAttendanceByIdMonth($wid, $month)
    {
        $_em = Zend_Registry::get('em');
        $_siteatten = $_em->getRepository('Synrgic\Infox\Siteattendance');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        $record = null;
        $worker = $_workerdetails->findOneBy(array("id"=>$wid));
        if($worker)
        {        
            $record = $_siteatten->findOneBy(array("worker"=>$worker, "month"=>$month));
        }

        return $record;
    }

    public static function getSiteById($id)
    {
        $_em = Zend_Registry::get('em');
        $_repo = $_em->getRepository('Synrgic\Infox\Site');
        return $_repo->findOneBy(array("id"=>$id));
    }

}
