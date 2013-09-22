<?php
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';

class Project_AttendanceController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
    }

    public function indexAction()
    {
        //$username = infox_user::getUserName();
        //echo $username . "<br>";
        //$userRole = infox_user::getUserRole();
        //echo $userRole . "<br>";
    
        $sites = infox_user::getUserSites();
        $siteid = $this->getParam("siteid", 0);
        
        // get construction time 
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));        
        $startdate = $siteobj->getStart();
        $stopdate = $siteobj->getStop();
        $months = $this->getMonths($startdate, $stopdate);

        $this->view->sites = $sites;
        $this->view->siteid = $siteid;
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

    public function submitAction()
    {
    }

}
