<?php

class infox_project
{
    public static $_em;
    public static $_siteatten;
    public static $_workerdetails;
    
    public static function getRepos()
    {
        self::$_em = Zend_Registry::get('em');
        self::$_siteatten = self::$_em->getRepository('Synrgic\Infox\Siteattendance');
        self::$_workerdetails = self::$_em->getRepository('Synrgic\Infox\Workerdetails');
    }    

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

    public static function checkWorkerAtten($wid, $month)
    {
        $_em = Zend_Registry::get('em');
        $_siteatten = $_em->getRepository('Synrgic\Infox\Siteattendance');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        $worker = $_workerdetails->findOneBy(array("id"=>$wid));
        if(!$worker)
        {
            return false;
        }        
        else
        {
            $record = $_siteatten->findOneBy(array("worker"=>$worker, 'month'=>$month));
            if(!$record)
            {
                return false;
            }
            else
            {
                return true;
            }
        }        
    }

    public static function getWorkerAtten($wid, $month)
    {
        self::getRepos();
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;

        $worker = $_workerdetails->findOneBy(array("id"=>$wid));
        if(!$worker)
        {
            return null;
        }        
        else
        {
            $record = $_siteatten->findOneBy(array("worker"=>$worker, 'month'=>$month));
            if(!$record)
            {
                return null;
            }
            else
            {
                return $record;
            }
        }        
    }

    public static function createWorkerAtten($wid, $month)
    {
        self::getRepos();

        $worker = self::$_workerdetails->findOneBy(array("id"=>$wid));
        $data = new \Synrgic\Infox\Siteattendance();
        $data->setWorker($worker); 
        $data->setMonth($month);

        $em = self::$_em;
        $em->persist($data);
        try {
            $em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }                
    }

    public static function updateWorkerAtten1($wid, $date, $salary)
    {
        self::getRepos();
        $_em = self::$_em;
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;
        
        // get column
        $day = $date->format("d");
        $day = intval($day);
        //echo $day;
        $month = $date->format("Y-m-01");
        //echo $month;

        $query = "UPDATE Synrgic\Infox\Siteattendance s SET s.day$day = '$salary' WHERE s.worker=$wid and s.month='$month'";
        $query = $_em->createQuery($query);
        $result = $query->getResult();
    }

    public static function updateWorkerAtten($wid, $date, $attend, $food)
    {
        self::getRepos();
        $_em = self::$_em;
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;
        
        // get column
        $day = $date->format("d");
        $day = intval($day);
        //echo $day;
        $month = $date->format("Y-m-01");
        //echo $month;

        $dayvalue= $attend . ";" . $food;
        $query = "UPDATE Synrgic\Infox\Siteattendance s SET s.day$day = '$dayvalue' WHERE s.worker=$wid and s.month='$month'";
        $query = $_em->createQuery($query);
        $result = $query->getResult();
    }

    public static function updateWorkerAtten2($record, $date, $salary)
    {
        self::getRepos();
        $_em = self::$_em;
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;
        /*
        $query = $em->createQuery('UPDATE MyProject\Model\User u SET u.password = 'new' WHERE u.id IN (1, 2, 3)');
        $users = $query->getResult();        
        */
        
        // get column
        $day = $date->format("d");
        //echo $day;
        $month = $date->format("Y-m-01");
        //echo $month;

        $worker = $record->getWorker();
        $wid = $worker->getId();
        $query = "UPDATE Synrgic\Infox\Siteattendance s SET s.day$day = '$salary' WHERE s.worker=$wid and s.month='$month'";
        $query = $_em->createQuery($query);
        $result = $query->getResult();
    }

    // monthstr format is "2013-10"
    public static function getAttendanceByMonth($monthstr)
    {
        self::getRepos();
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;
        
        $month = new Datetime($monthstr . "-01");
        $records = $_siteatten->findBy(array("month"=>$month));                

        return $records;
    }

    public static function getAttendanceByMonthSheet($monthstr, $sheet)
    {
        self::getRepos();
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;
        
        $month = new Datetime($monthstr . "-01");
        $records = $_siteatten->findBy(array("month"=>$month));       

        $attendarr = array();
        foreach($records as $tmp)
        {
            $worker = $tmp->getWorker();

            if($worker->getSheet() == $sheet)
            {
                $attendarr[] = $tmp;
            }

        }         

        return $attendarr;
    }


}
