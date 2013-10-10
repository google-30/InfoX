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

        $this->view->workerhtmls = $this->genWorkerHtmls($workerarr, $attendancearr, $siteid, $monthstr);
    }

    private function genWorkerHtmls1($workerarr, $attendancearr, $siteid, $monthstr)
    {
        $tablearr = array(); 
        $table ="";
        $sno = 0;
        foreach($workerarr as $tmp)
        {
            $tabs = array();

            $sno++;
           
            $table = '<table class="attendsum">';    
            // worker info and month summary
            $tr = "";
            $tr .= '<tr><th rowspan="2" class="fixwidthcol">序号</th><th colspan=4>工人信息</th>'; 
            $tr .= '<th colspan=2>正常工作</th><th colspan=3>加班工作</th><th colspan=2>总工作</th><th rowspan=2>考勤天数</th><th colspan=2>缺勤罚款</th><th rowspan="2">项目总工资</th><th colspan="2">伙食费</th></tr>
';
            $table .= $tr;
            $tr = '<tr><th>准证号</th><th>姓名</th><th>单价</th><th>工种</th>';
            $tr .= '<th>小时</th><th>金额</th><th>单价</th><th>小时</th><th>金额</th>';
            $tr .= '<th>小时</th><th>金额</th><th>天数</th><th>金额</th><th>天数</th><th>金额</th></tr>
';
            $table .= $tr;
            
            $workerid = $tmp->getId();
            $wpno = $tmp->getWpno();
            $name=$tmp->getNamechs();
            if(!$name || $name=="")
            {
                $name = $tmp->getNameeng();
            }
            //$td="<td>$name</td>";
            //$tr .= $td;
            $price = "75"; //$tmp->getPrice();

            $worktype=$tmp->getWorktype();
            $tr = "<tr><td>$sno</td><td>$wpno</td><td>$name</td><td>$price</td><td>$worktype</td>";

            $tr .= "<td></td><td></td><td></td><td></td><td></td><td></td>";
            $tr .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td>";            

            $table .= $tr;
            $table .= "</table>";
            $tabs[] = $table;

            // attendance and food
            $attendrecord = null;
            foreach($attendancearr as $attendtmp)
            {
                $wid = $attendtmp->getWorker()->getId();
                if($wid == $tmp->getId())
                {
                    $attendrecord = $attendtmp;
                    break;
                }
            }

            if(!$attendrecord)
            {
                continue;
            }
            //var_dump($attendrecord);            
            $attendresult = $this->getAttendFoodData($attendrecord);

            $attendtab = "<table>";
/*
            $tr = '<tr><th rowspan=2 class="fixwidthcol"></th><th colspan=31>日期</th><th rowspan=4><button data-mini="true" data-theme="b">考勤</button></th></tr>
';            
*/
            $url = "/project/attendance/attendialog?" . "&sid=$siteid&month=$monthstr&wid=$workerid";   
            $personattendth = '<th rowspan=4><a href="' . $url . '" data-rel="dialog" data-role="button" data-mini="true" data-theme="b">考勤</a></th>';
            $tr = '<tr><th rowspan=2 class="fixwidthcol"></th><th colspan=31>日期</th>' . $personattendth . '</tr>
';                        

            $attendtab .= $tr;
            
            $ths="";
            for($i=0; $i<31; $i++)
            {
                $j = $i+1;
                if($j<10)
                {
                    $th = "<th>0$j</th>";
                }
                else
                {
                    $th = "<th>$j</th>";
                }
                $ths .= $th;
            }
            $tr = "<tr>$ths</tr>";
            $attendtab .= $tr;            

            $tds = "";    
            for($i=0; $i<31; $i++)
            {
                $j = $i + 1;
                $attend = $attendresult[0][$i];

                $td = "<td>$attend</td>";
                $tds .= $td;
            }
            //$tr = "<tr><td>工时</td>$tds" . '<td rowspan=2><button data-mini="true" data-theme="b">考勤</button></td></tr>
//';            
            $tr = "<tr><td>工时</td>$tds</tr>
";          
            $attendtab .= $tr;

            $tds = "";    
            for($i=0; $i<31; $i++)
            {
                //$j = $i +1;
                $food = $attendresult[1][$i];
                $td = "<td>$food</td>";
                $tds .= $td;
            }
            $tr = "<tr><td>伙食</td>$tds</tr>
";            
            $attendtab .= $tr;

            $attendtab .= "</table>";
            

            $tabs[] = $attendtab;

            $tablearr[] = $tabs;            
        }

        return $tablearr;
    }

    private function genWorkerHtmls($workerarr, $attendancearr, $siteid, $monthstr)
    {
        $tablearr = array(); 
        $table ="";
        $sno = 0;
        foreach($workerarr as $tmp)
        {
            $sno++;
            $workerid = $tmp->getId();

            $attendrecord = null;
            foreach($attendancearr as $attendtmp)
            {
                $wid = $attendtmp->getWorker()->getId();
                if($wid == $workerid)
                {
                    $attendrecord = $attendtmp;
                    break;
                }
            }

            if($attendrecord)
            {
                $tabs = $this->getWorkerMonthAttendHtml($sno, $tmp, $attendrecord, $siteid, $monthstr);
            }

            $tablearr[] = $tabs;
        }

        return $tablearr;
    }

    private function getWorkerMonthAttendHtml($sno, $worker, $attendrecord, $siteid, $monthstr, $nobtn=false)
    {
            $table = '<table class="attendsum">';    
            // worker info and month summary
            $tr = "";
            $tr .= '<tr><th rowspan="2" class="fixwidthcol">序号</th><th colspan=4>工人信息</th>'; 
            $tr .= '<th colspan=2>正常工作</th><th colspan=3>加班工作</th><th colspan=2>总工作</th><th rowspan=2>考勤天数</th><th colspan=2>缺勤罚款</th><th rowspan="2">项目总工资</th><th colspan="2">伙食费</th></tr>
';
            $table .= $tr;
            $tr = '<tr><th>准证号</th><th>姓名</th><th>单价</th><th>工种</th>';
            $tr .= '<th>小时</th><th>金额</th><th>单价</th><th>小时</th><th>金额</th>';
            $tr .= '<th>小时</th><th>金额</th><th>天数</th><th>金额</th><th>天数</th><th>金额</th></tr>
';
            $table .= $tr;
            
            $workerid = $worker->getId();
            $wpno = $worker->getWpno();
            $name=$worker->getNamechs();
            if(!$name || $name=="")
            {
                $name = $worker->getNameeng();
            }
            //$td="<td>$name</td>";
            //$tr .= $td;
            $price = "75"; //$worker->getPrice();

            $worktype=$worker->getWorktype();
            $tr = "<tr><td>$sno</td><td>$wpno</td><td>$name</td><td>$price</td><td>$worktype</td>";

            $tr .= "<td></td><td></td><td></td><td></td><td></td><td></td>";
            $tr .= "<td></td><td></td><td></td><td></td><td></td><td></td><td></td>";            

            $table .= $tr;
            $table .= "</table>";
            $tabs[] = $table;

            // attendance and food           
            $attendresult = $this->getAttendFoodData($attendrecord);

            $attendtab = "<table>";
            if(!$nobtn)
            {
            $url = "/project/attendance/attendialog?" . "&sid=$siteid&month=$monthstr&wid=$workerid";   
            $personattendth = '<th rowspan=4><a href="' . $url . '" data-rel="dialog" data-role="button" data-mini="true" data-theme="b">考勤</a></th>';
            $tr = '<tr><th rowspan=2 class="fixwidthcol"></th><th colspan=31>日期</th>' . $personattendth . '</tr>
';                        
            }
            else
            {
            $tr = '<tr><th rowspan=2 class="fixwidthcol"></th><th colspan=31>日期</th></tr>
';                        
            }

            $attendtab .= $tr;

            $nowdate = new Datetime("");
            $today = $nowdate->format("d");
            
            $ths="";
            for($i=0; $i<31; $i++)
            {
                $j = $i+1;
                $value = ($j<10) ? "0$j" : $j;
                $th = ($j == $today) ? '<th style="background:#ff5c5c;">' . $value . '</th>' : "<th>$value</th>";
                $ths .= $th;
            }
            $tr = "<tr>$ths</tr>";
            $attendtab .= $tr;            


            $tds = "";    
            for($i=0; $i<31; $i++)
            {
                $j = $i + 1;
                $value = $attendresult[0][$i];

                //$td = "<td>$attend</td>";
                $td = ($j == $today) ? '<td style="background:#ff5c5c;">' . $value . '</td>' : "<td>$value</td>";
                $tds .= $td;
            }
            //$tr = "<tr><td>工时</td>$tds" . '<td rowspan=2><button data-mini="true" data-theme="b">考勤</button></td></tr>
//';            
            $tr = "<tr><td>工时</td>$tds</tr>
";          
            $attendtab .= $tr;

            $tds = "";    
            for($i=0; $i<31; $i++)
            {
                $j = $i +1;
                $value = $attendresult[1][$i];
                //$td = "<td>$food</td>";

                $td = ($j == $today) ? '<td style="background:#ff5c5c;">' . $value . '</td>' : "<td>$value</td>";
                $tds .= $td;
            }
            $tr = "<tr><td>伙食</td>$tds</tr>
";            
            $attendtab .= $tr;
            $attendtab .= "</table>";
            
            $tabs[] = $attendtab;

            return $tabs;
    }

    // data format: 
    //  "8;1", means work 8 hours, and 1 means had food also
    //  "9;0", means work 8 hours, and ot 1 hour, and 0 means no food
    private function getAttendFoodData($record)
    {
        /*
        $query = "SELECT s FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
        //echo $query;
        $query = $this->_em->createQuery($query);
        $result = $query->getResult();          
        */

        $wid = $record->getWorker()->getId();
        $month = $record->getMonth()->format("Y-m-01");

        $days = "";
        for($i=1; $i<=31; $i++)
        {
            
            $day = "s.day$i";            
            if($i != 31)
            {
                $day .= ",";
            }

            $days .= $day;
        }

        $query = "SELECT $days FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
        //echo $query; return;
        $query = $this->_em->createQuery($query);
        $result = $query->getResult();
        //echo "result="; var_dump($result);
        //return $result;

        $attendarr = array();
        $foodarr = array();

        $tmparr = $result[0];
        foreach($tmparr as $tmp)
        {
            $arr = explode(";", $tmp);
            
            $attendarr[] = array_key_exists(0, $arr) ? $arr[0] : "&nbsp;";
            $foodarr[] = array_key_exists(1, $arr) ? $arr[1] : "&nbsp;";
        }
        
        $newresult = array($attendarr, $foodarr);
        //print_r($newresult);

        return $newresult;
    }

    public function attendialogAction()
    {
        infox_common::turnoffLayout($this->_helper);

        // date        
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr . "01");
        $this->view->date=$date;
        $this->view->monthstr=$monthstr;
        
        // worker info
        $wid = $this->getParam("wid", 0);
        $worker = infox_worker::getWorkerdetailsById($wid);
        $this->view->workerdetails = $worker;

        // site info
        $siteid = $this->getParam("sid", 0);
        $this->view->siteid = $siteid;
        $site = infox_project::getSiteById($siteid);
        $this->view->site = $site;

        // worker atten
        $record = infox_project::getAttendanceByIdMonth($wid, $date);
        //if(!$record) { echo "xxxxx"; }
        $this->view->attendance = $record;        

        $workerarr = infox_worker::getworkerlistbysitedateobj($site, $date);
        $sno = 0;
        foreach($workerarr as $tmp)
        {
            $sno++;
            if($tmp->getId() == $wid)
            {
                $tabs = $this->getWorkerMonthAttendHtml($sno, $worker, $record, $siteid, $monthstr, true);
                break;
            }
        }
        $this->view->workerattendtabs = $tabs;
    }

    public function postattendAction()
    {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }        
        
        $wid = $this->getParam("wid", 0);
        $date = $this->getParam("date", "");
        $dateobj = new Datetime($date);
        //$month = $this->getParam("month", 0);                
        //$monthobj = new Datetime($month);
        $month = $dateobj->format("Y-m-01");        
        $monthobj = new Datetime($month);

        $attend = $this->getParam("attend", 0);
        $food = $this->getParam("food", 1);
        $remark = $this->getParam("remark", 0);                
        
        $record = infox_project::getWorkerAtten($wid, $monthobj);
        if(!$record)
        {// create atten
            infox_project::createWorkerAtten($wid, $monthobj);              
        }   
        // update atten
        $dateobj = new Datetime($date);
        //infox_project::updateWorkerAtten($record, $dateobj, $salary);
        infox_project::updateWorkerAtten($wid, $dateobj, $attend, $food);       
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
            $attend = $this->getParam("attend$wid", "");
            $food = $this->getParam("food$wid", "");

            //$record = $this->_siteattendance->findOneBy();
            $record = infox_project::getWorkerAtten($wid, $monthobj);
            if(!$record)
            {// create atten
                infox_project::createWorkerAtten($wid, $monthobj);              
            }   
            // update atten
            infox_project::updateWorkerAtten($wid, $date, $attend, $food);
        }

        $url = "/project/attendance/attendancepage?&siteid=$siteid&month=" . $date->format("Ym");
        $this->_redirect($url);
    }
}
