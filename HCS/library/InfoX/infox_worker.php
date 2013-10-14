<?php

class infox_worker
{
    public static $_em;
    public static $_siteatten;
    public static $_workerdetails;
    public static $_salaryhcc;    
    public static $_salaryhcb;    
    public static $_salaryhtc;    
    public static $_salaryhtb;    

    public function init()
    {
        echo "infox_worker::init";
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
        $this->_workerattendance = $this->_em->getRepository('Synrgic\Infox\Workerattendance');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');

        $em = Zend_Registry::get('em');
        $workerdetails = $em->getRepository('Synrgic\Infox\Workerdetails');
    }

    public static function getRepos()
    {
        echo "infox_worker::getRepos";
        self::$_em = Zend_Registry::get('em');
        self::$_siteatten = self::$_em->getRepository('Synrgic\Infox\Siteattendance');
        self::$_workerdetails = self::$_em->getRepository('Synrgic\Infox\Workerdetails');

        self::$_salaryhcc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcc');
        self::$_salaryhcb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcb');
        self::$_salaryhtc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtc');
        self::$_salaryhtb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtb');
    }  

    public static function getSheetarr()
    {
        $sheetarr = array("HC.C","HT.C","HC.B","HT.B",);
        return $sheetarr;        
    }    

    /*
    public static function getworkerlist()
    {
        $_em = Zend_Registry::get('em');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        $workerarr = array();
        $allworkers = $_workerdetails->findAll();
        foreach($allworkers as $tmp)
        {
            $sheet = $tmp->getSheet();
            if($sheet != $requestsheet)
            {
                continue;
            }

            $date = $tmp->getResignation();
            workerresigned($date);

            $now = new DateTime("now");
            
            if(!$date)
            {//no date = still on duty
                $workerarr[] = $tmp; 
                continue;
            }

            $interval = $date->diff($now);
            $invert = $interval->invert;
            if($invert)
            {
                $workerarr[] = $tmp; 
            }         
        }

        return $workerarr;
    }
    */

    public static function getworkerlistbysheet($sheet)
    {
        $_em = Zend_Registry::get('em');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        $workerarr = array();
        $allworkers = $_workerdetails->findBy(array("sheet"=>$sheet));
        foreach($allworkers as $tmp)
        {
            $date = $tmp->getResignation();
            if(self::workerresigned($date))
            {
                continue;
            }
            else
            {
                $workerarr[] = $tmp; 
            }         
        }

        return $workerarr;
    }

    private function workerresigned($date)
    {         
        if(!$date)
        {
            return false;
        }

        $now = new DateTime("now");       
        $interval = $date->diff($now);
        $invert = $interval->invert;
        if(!$invert)
        {
            return true;
        }   
        
        return false;
    }


    // TODO: check date, only worker in date return
    public static function getworkerlistbysitedateobj($siteobj, $dateobj)
    {
        $_em = Zend_Registry::get('em');
        $_workeronsite = $_em->getRepository('Synrgic\Infox\Workeronsite');

        $workerarr= array();
        $records = $_workeronsite->findBy(array("site"=>$siteobj));
        foreach($records as $tmp)
        {
            $worker = $tmp->getWorker();
            $flag = false;
            foreach($workerarr as $tmp1)
            {
                if($worker->getId() == $tmp1->getId())
                {
                    $flag = true;
                    break;
                }
                
            }

            if(!$flag)
            {
                $workerarr[] = $worker;
            }
        }
        
        return $workerarr;    
    }

    public static function getWorkerdetailsById($wid)
    {
        $_em = Zend_Registry::get('em');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        return $_workerdetails->findOneBy(array("id"=>$wid));
    }

    public static function getReposBySheet($sheet="HC.C")
    {
        //self::getRepos();
        $repos = null;
        switch($sheet)
        {
            case "HC.C":
                $repos = self::$_salaryhcc;
                break;
            case "HC.B":
                $repos = self::$_salaryhcb;
                break;
            case "HT.C":
                $repos = self::$_salaryhtc;
                break;
            case "HT.B":
                $repos = self::$_salaryhtb;
                break;    
        }

        return $repos;
    }

    public static function createSalaryRecordsByMonthSheet($monthstr, $sheet="HC.C")
    {
        self::getRepos();
        $workerarr = self::getworkerlistbysheet($sheet);
        $salaryrepos = self::getReposBySheet($sheet);

        $month = new Datetime($monthstr . "-01");

        foreach($workerarr as $worker)
        {
            $record = self::getSalaryRecordByReposWorkerMonth($salaryrepos, $worker, $month);
            if(!$record)
            {
                //echo "no record for " . $worker->getNamechs();
                // create record
                self::createSalaryRecordBySheetWorkerMonth($sheet, $worker, $month);
            }
        }
    }

    public static function getSalaryRecordByReposWorkerMonth($salaryrepos, $worker, $month)
    {
        $record = $salaryrepos->findOneBy(array("worker"=>$worker, "month"=>$month));        
        return $record;
    }

    public static function createSalaryRecordBySheetWorkerMonth($sheet, $worker, $month)
    {
        $data = null;
        switch($sheet)
        {
            case "HC.C":
                $data = new \Synrgic\Infox\Workersalaryhcc();
                break;
            case "HC.B":
                $data = new \Synrgic\Infox\Workersalaryhcb();
                break;
            case "HT.C":
                $data = new \Synrgic\Infox\Workersalaryhtc();
                break;
            case "HT.B":
                $data = new \Synrgic\Infox\Workersalaryhtb();
                break;
        }

        if($data)
        {
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
    }

    // sheet, month, workers not resigned    
    public static function getSalaryRecordsByMonthSheet($month, $sheet="HC.C")
    {
        $salaryrepos = self::getReposBySheet($sheet);
        $records = $salaryrepos->findBy(array("month"=>$month));
        return $records;
    }
}
