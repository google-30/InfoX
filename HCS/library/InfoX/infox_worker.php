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
    public static $_workeronsite;
    public static $_setting;
    
    public static function getRepos()
    {
        //echo "infox_worker::getRepos";
        self::$_em = Zend_Registry::get('em');
        self::$_siteatten = self::$_em->getRepository('Synrgic\Infox\Siteattendance');
        self::$_workerdetails = self::$_em->getRepository('Synrgic\Infox\Workerdetails');

        self::$_salaryhcc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcc');
        self::$_salaryhcb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcb');
        self::$_salaryhtc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtc');
        self::$_salaryhtb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtb');
        
        self::$_workeronsite = self::$_em->getRepository('Synrgic\Infox\Workeronsite');
        self::$_setting = self::$_em->getRepository('Synrgic\Infox\Setting');        
    }  

    public static function getSheetarr()
    {
        $sheetarr = array("HC.C","HT.C","HC.B","HT.B",);
        return $sheetarr;        
    }    

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

    public static function getworkerlistbysiteobj($siteobj)
    {
        self::getRepos();
        $_workeronsite = self::$_workeronsite;
        $records = $_workeronsite->findBy(array("site"=>$siteobj));
        
        $workerarr= array();
        foreach($records as $tmp)
        {
            $worker = $tmp->getWorker();
            $workerarr[] = $worker;
        }
        
        return $workerarr;            
    }

    public static function getWorkerdetailsById($wid)
    {
        $_em = Zend_Registry::get('em');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        return $_workerdetails->findOneBy(array("id"=>$wid));
    }

    public static function getActiveWorkerdetailsBySheet($requestsheet)
    {
        self::getRepos();
        $workerarr = array();
        $allworkers = self::$_workerdetails->findAll();

        foreach($allworkers as $tmp)
        {
            $sheet = $tmp->getSheet();
            if($sheet != $requestsheet)
            {
                continue;
            }

            $date = $tmp->getResignation();
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
    
    public static function getWorkerRace($worker)
    {
        $sheet = $worker->getSheet();
        $race = 0; // 0, for chinese, 1 for bag; 2, 3....
        if($sheet == "HC.C" || $sheet == "HT.C")
        {
            $race = 0;
        }
        else
        {
            $race = 1;
        }
        
        return $race;
    }

    public static function getWorkerOtRate($worker)
    {
        self::getRepos();
        
        $race = self::getWorkerRace($worker);
        $rate = $worker->getCurrentrate();
        $_setting = self::$_setting;        
        $otrate = 0;
        switch($race)
        {
            case 0:
                //$otrate = $rate;
                $setting = $_setting->findOneBy(array("name"=>"cotmultiple"));
                $otrate = $rate * $setting->getValue();
                break;
            case 1:
                //$otrate = $rate * 1.5;
                $setting = $_setting->findOneBy(array("name"=>"botmultiple"));
                $otrate = $rate * $setting->getValue();                
                break;
        }
        
        return $otrate;
    } 
    
    public static function getSalaryRepoByWorker($worker)
    {
        self::getRepos();
        $retrepo = null;
        $sheet = $worker->getSheet();
        
        switch ($sheet)
        {
            case "HC.C":
                $retrepo = self::$_salaryhcc;
                break;
            case "HC.B":
                $retrepo = self::$_salaryhcb;
                break;
            case "HT.C":
                $retrepo = self::$_salaryhtc;
                break;
            case "HT.B":
                $retrepo = self::$_salaryhtb;
                break;                    
        }
        
        return $retrepo;
    }       
}
