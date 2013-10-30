<?php

class infox_salary
{
    public static $_em;
    public static $_siteatten;
    public static $_workerdetails;
    public static $_salaryhcc;    
    public static $_salaryhcb;    
    public static $_salaryhtc;    
    public static $_salaryhtb;
    public static $_setting;   

    public static function getRepos()
    {
        self::$_em = Zend_Registry::get('em');
        self::$_siteatten = self::$_em->getRepository('Synrgic\Infox\Siteattendance');
        self::$_workerdetails = self::$_em->getRepository('Synrgic\Infox\Workerdetails');

        self::$_salaryhcc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcc');
        self::$_salaryhcb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcb');
        self::$_salaryhtc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtc');
        self::$_salaryhtb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtb');

        self::$_setting = self::$_em->getRepository('Synrgic\Infox\Setting');
    }  

    public static function getSettingArray()
    {
        $settingnames = array("cotmultiple", "botmultiple", "workerfood", "leaderfood", 
                    "absencedays", "absencelow", "absencehigh", "absencetotal");
        return $settingnames;        
    }

    public static function getSettingBySectionName($section, $name)
    {
        self::getRepos();
        $setting = self::$_setting;
        $settingobj = $setting->findOneBy(array("section"=>$section, "name"=>$name)); 
        $value = $settingobj ? $settingobj->getValue() : "";

        return $value;
    }    

    public static function setSettingBySectionName($section, $name, $value)
    {
        self::getRepos();
        $setting = self::$_setting;
        $settingobj = $setting->findOneBy(array("section"=>$section, "name"=>$name)); 
        $querystr = "update Synrgic\Infox\Setting setting set setting.value='". $value 
                . "' where setting.name='" . $name . "' and setting.section='" . $section . "'";    
        //echo $querystr . "<br>";
        $query = self::$_em->createQuery($querystr);
        $result = $query->getResult();
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

        $records = $salaryrepos->findBy(array("month"=>$month));        

        $workersnorecord = array();
        foreach($workerarr as $worker)
        {
            $flag = false;
            foreach($records as $record)
            {
                if($record->getWorker()->getId() == $worker->getId())
                {// found
                    $flag = true;
                    break;
                }
            }

            if(!$flag)
            {//create record
                //self::createSalaryRecordBySheetWorkerMonth($sheet, $worker, $month);                
                $workersnorecord[] = $worker;
            }
        }

        self::createSalaryRecordsBySheetWorkersMonth($sheet, $workersnorecord, $month);
    }

    public function getSalaryRecordsByReposMonth($salaryrepos,$month)
    {    
        //self::getRepos();
        $records = $salaryrepos->findBy(array("month"=>$month));
        
    }

    // low Efficiency, consume too much cpu
    public static function createSalaryRecordsByMonthSheet1($monthstr, $sheet="HC.C")
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

    public static function createSalaryRecordsBySheetWorkersMonth($sheet, $workerarr, $month)
    {
        foreach($workerarr as $worker)
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
            }            
        }

        try {
            $em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }   
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
        self::getRepos();
        $salaryrepos = self::getReposBySheet($sheet);
        $records = $salaryrepos->findBy(array("month"=>$month));
        return $records;
    }

    public static function updatePaymentDataByMonthSheet($month, $sheet="HC.C")
    {
        $records = self::getSalaryRecordsByMonthSheet($month, $sheet);
        $siteatten = self::$_siteatten;

        foreach($records as $record)
        {
            $worker = $record->getWorker();
            $attendrecord = $siteatten->findOneBy(array("month"=>$month, "worker"=>$worker));
            
            if(!$attendrecord)
            {// TODO: no attend, 
                continue;
            }            
        }        
    }    
}
