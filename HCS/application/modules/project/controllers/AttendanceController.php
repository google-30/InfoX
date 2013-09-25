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
        $date = new Datetime($monthstr);
        $this->view->date=$date;
        
        $workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);
        $this->view->workerarr = $workerarr;

        $attendancearr=infox_project::getAttendanceByWorkerMonth($workerarr, $date);
        $this->view->attendancearr = $attendancearr;
    }

    public function attendialogAction()
    {
        infox_common::turnoffLayout($this->_helper);
        
        $siteid = $this->getParam("sid", 0);
        $workerid = $this->getParam("wid", 0);
        $monthstr = $this->getParam("month", "");

        
    }

}
