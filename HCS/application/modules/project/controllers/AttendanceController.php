<?php
include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';

class Project_AttendanceController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        //$this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
    }

    public function indexAction()
    {
        $sites = infox_user::getUserSites();
        $this->view->sites = $sites;

        $siteid = $this->getParam("siteid", 0);
        $this->view->siteid = $siteid;
        
        $months=null;
        // get construction time 
        if($siteid)
        {
            $siteobj = $this->_site->findOneBy(array("id"=>$siteid));        
            $startdate = $siteobj->getStart();
            $stopdate = $siteobj->getStop();
            $months = $this->getMonths($startdate, $stopdate);
        }

        $this->view->workingmonths=$months;
    }

    private function getMonths($start, $stop)
    {
        $startyear = $start->format("Y");
        $stopyear = $stop->format("Y");

        $deltayear = $stopyear - $startyear;
        //echo "deltayear=$deltayear<br>";

        $startmonth = $start->format("m");
        //echo "startmonth=$startmonth<br>";        
        $stopmonth = $stop->format("m");
        //echo "stopmonth=$stopmonth<br>";
        
        $dateArr = array();

        if($deltayear==0)
        {// same year 
            $deltamonth = $stopmonth - $startmonth;
            //echo "deltamonth=$deltamonth<br>";
            for($i=0; $i<$deltamonth; $i++)
            {
                $year = $startyear;    
                $month = $startmonth + $i;
                $date = new Datetime("$year-$month");
                $dateArr[] = $date;
            }
        }
        else
        {
            // first year
            $deltamonth = 12 - $startmonth +1;
            $i = 0;
            for($i=0; $i<$deltamonth; $i++)
            {
                $year = $startyear;    
                $month = $startmonth + $i;
                $date = new Datetime("$year-$month");
                $dateArr[] = $date;
            }

            // years
            for($i=0; $i<$deltayear-1; $i++)
            {
                $year = $startyear + $i + 1;
                for($j=0; $j<12; $j++)
                {
                    $month = $j+1;
                    $date = new Datetime("$year-$month");
                    $dateArr[] = $date;
                }
            }

            // last year
            $deltamonth = $stopmonth;
            for($i=0; $i<$deltamonth; $i++)
            {
                $month = $i+1;
                $year = $stopyear;
                $date = new Datetime("$year-$month");
                $dateArr[] = $date;

            }            
        }

        /*
        foreach($dateArr as $tmp)
        {
            echo $tmp->format("Y-m") . "<br>";
        }
        */

        return $dateArr;
    }

    public function attendancepageAction()
    {
        infox_common::turnoffLayout($this->_helper);

        $siteid = $this->getParam("siteid", 0);
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));        
        $this->view->site = $siteobj;
        $this->view->siteid = $siteid;

        $monthstr = $this->getParam("month", "");
        // 20131001
        $date = new Datetime($monthstr."01");
        $this->view->date=$date;
        
        $workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);
        $this->view->workerarr = $workerarr;

        $attendancearr=infox_project::getAttendanceByWorkerMonth($workerarr, $date);
        $this->view->attendancearr = $attendancearr;
        //echo "count=" . count($attendancearr);
    }

    public function attendialogAction()
    {
        infox_common::turnoffLayout($this->_helper);

        // date        
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr);
        $this->view->date=$date;
        $this->view->monthstr=$monthstr;
        
        // worker info
        $wid = $this->getParam("wid", 0);
        $wdetails = infox_worker::getWorkerdetailsById($wid);
        $this->view->workerdetails = $wdetails;

        // site info
        $siteid = $this->getParam("sid", 0);
        $this->view->siteid = $siteid;
        $site = infox_project::getSiteById($siteid);
        $this->view->site = $site;

        // worker atten
        $record = infox_project::getAttendanceByIdMonth($wid, $date);
        $this->view->attendance = $record;

        // datepicker

    }

    public function postsalaryAction()
    {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }        
        
        $wid = $this->getParam("wid", 0);
        $date = $this->getParam("date", 0);
        $salary = $this->getParam("salary", 0);
        $remark = $this->getParam("remark", 0);                
        $month = $this->getParam("month", 0);                

        $monthobj = new Datetime($month);
        $record = infox_project::getWorkerAtten($wid, $monthobj);

        if(!$record)
        {// create atten
            infox_project::createWorkerAtten($wid, $monthobj);              
        }   
        // update atten
        $dateobj = new Datetime($date);
        infox_project::updateWorkerAtten($record, $dateobj, $salary);       
    }

    public function attendsheetAction()
    {
        infox_common::turnoffLayout($this->_helper);        

        // date        
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr);
        $this->view->date=$date;
        $this->view->monthstr=$monthstr;
        $month = $date->format("Y-m-01");

        // worker info
        $wid = $this->getParam("wid", 0);
        $wdetails = infox_worker::getWorkerdetailsById($wid);
        $this->view->workerdetails = $wdetails;

        // site info
        /*
        $siteid = $this->getParam("sid", 0);
        $this->view->siteid = $siteid;
        $site = infox_project::getSiteById($siteid);
        $this->view->site = $site;
        */

        // worker atten
        $record = infox_project::getAttendanceByIdMonth($wid, $date);
        $this->view->attendance = $record;        

        /*
        $query = "SELECT s FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
        //echo $query;
        $query = $this->_em->createQuery($query);
        $result = $query->getResult();                
        echo $result[0]->getDay28();
        */

        $days = "";
        for($i=0; $i<31; $i++)
        {
            $j = $i+1;                        
            $days .= "s.day" . $j;            
            if($i!=30)
            {
                $days .=",";
            }            
        }

        $query = "SELECT $days FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
        echo $query;
        $query = $this->_em->createQuery($query);
        $result = $query->getResult();        

        //var_dump($result);        
    }

    private function getdata()
    {

    }

    public function attendquickAction()
    {
        infox_common::turnoffLayout($this->_helper);

        // date     
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr);
        $this->view->date=$date;
        $this->view->monthstr=$monthstr;
        $month = $date->format("Y-m-01");

        $siteid = $this->getParam("sid", 0);
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));        
        $this->view->site = $siteobj;
        $this->view->siteid = $siteid;

        $workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);
        $this->view->workerarr = $workerarr;
        //echo "workers=" . count($workerarr);
        $attendancearr=infox_project::getAttendanceByWorkerMonth($workerarr, $date);
        $this->view->attendancearr = $attendancearr;
        
    }

    public function quicksubmitAction()
    {
        infox_common::turnoffView($this->_helper);
        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }        

        $quickdate = $this->getParam("quickdate", "");
        $date = new Datetime($quickdate);
        $month = $date->format("Y-m-01");        
        $monthobj = new Datetime($month);

        $siteid = $this->getParam("sid", 0);
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));        

        $workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);

        foreach($workerarr as $tmp) 
        {
            $wid = $tmp->getId();
            $salary = $this->getParam("attend$wid", "");

            //$record = $this->_siteattendance->findOneBy();
            $record = infox_project::getWorkerAtten($wid, $monthobj);
            if(!$record)
            {// create atten
                infox_project::createWorkerAtten($wid, $monthobj);              
            }   
            // update atten
            infox_project::updateWorkerAtten($wid, $date, $salary);
        }

        $url = "/project/attendance/attendancepage?&siteid=$siteid&month=" . $date->format("Ym");
        $this->_redirect($url);
    }
}
