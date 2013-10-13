<?php

class infox_worker
{
    

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

    public static function getSalaryRecordsByMonthSheet($monthstr, $sheet="HC.C")
    {
                
    }
}
