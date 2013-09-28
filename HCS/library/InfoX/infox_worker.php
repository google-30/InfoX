<?php

class infox_worker
{
    //private static $_em = Zend_Registry::get('em');
    //private static $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
        $this->_workerattendance = $this->_em->getRepository('Synrgic\Infox\Workerattendance');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');

        $em = Zend_Registry::get('em');
        $workerdetails = $em->getRepository('Synrgic\Infox\Workerdetails');
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
}
