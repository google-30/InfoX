<?php

include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';
include 'InfoX/infox_salary.php';

class Project_AttendanceController extends Zend_Controller_Action {

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_siteattendance = $this->_em->getRepository('Synrgic\Infox\Siteattendance');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
    }

    public function indexAction() {
        $error = "";

        $sites = infox_user::getActiveSites();
        $this->view->sites = $sites;

        $siteid = $this->getParam("siteid", 0);
        $this->view->siteid = $siteid;

        $months = null;
        // get construction time 
        if ($siteid) {
            $siteobj = $this->_site->findOneBy(array("id" => $siteid));
            $startdate = $siteobj->getStart();
            //$stopdate = $siteobj->getStop();
            $stopdate = new Datetime("now");
            $months = $this->getMonths($startdate, $stopdate);
            if (!$months) {
                $error .= infox_common::setErrorOutput("请在工地管理指定开始日期");
            }

            // TODO:
            $result = $this->_workeronsite->findBy(array('site' => $siteobj));
            $workersonsite = array();
            foreach ($result as $tmp) {
                $worker = $tmp->getWorker();
                $wid = $worker->getId();
                if (!key_exists($wid, $workersonsite)) {
                    $workersonsite[$wid] = $worker;
                }
            }
            $this->view->workersonsite = $workersonsite;
        }

        $this->view->workingmonths = $months;
        $this->view->error = $error;
    }

    private function getMonths($start, $stop) {
        if (!$start || !$stop) {
            return null;
        }
        $startyear = $start->format("Y");
        $stopyear = $stop->format("Y");

        $deltayear = $stopyear - $startyear;
        //echo "deltayear=$deltayear<br>";

        $startmonth = $start->format("m");
        //echo "startmonth=$startmonth<br>";        
        $stopmonth = $stop->format("m");
        //echo "stopmonth=$stopmonth<br>";

        $dateArr = array();

        if ($deltayear == 0) {// same year 
            $deltamonth = $stopmonth - $startmonth + 1;
            //echo "deltamonth=$deltamonth<br>";
            for ($i = 0; $i < $deltamonth; $i++) {
                $year = $startyear;
                $month = $startmonth + $i;
                $date = new Datetime("$year-$month");
                $dateArr[] = $date;
            }
        } else {
            // first year
            $deltamonth = 12 - $startmonth + 1;
            $i = 0;
            for ($i = 0; $i < $deltamonth; $i++) {
                $year = $startyear;
                $month = $startmonth + $i;
                $date = new Datetime("$year-$month");
                $dateArr[] = $date;
            }

            // years
            for ($i = 0; $i < $deltayear - 1; $i++) {
                $year = $startyear + $i + 1;
                for ($j = 0; $j < 12; $j++) {
                    $month = $j + 1;
                    $date = new Datetime("$year-$month");
                    $dateArr[] = $date;
                }
            }

            // last year
            $deltamonth = $stopmonth;
            for ($i = 0; $i < $deltamonth; $i++) {
                $month = $i + 1;
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

    public function attendancepageAction() {
        infox_common::turnoffLayout($this->_helper);

        $siteid = $this->getParam("siteid", 0);
        $siteobj = $this->_site->findOneBy(array("id" => $siteid));
        $this->view->site = $siteobj;
        $this->view->siteid = $siteid;

        $monthstr = $this->getParam("month", "");
        // 20131001
        $date = new Datetime($monthstr . "01");
        $this->view->date = $date;

        //$workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);
        $workerarr = infox_worker::getworkerlistbysiteobj($siteobj);
        $this->view->workerarr = $workerarr;
        if (0) {
            foreach ($workerarr as $tmp) {
                echo "getNamechs=" . $tmp->getNamechs();
            }
        }

        $attendancearr = infox_project::getAttendanceByWorkerMonth($workerarr, $date);
        $this->view->attendancearr = $attendancearr;
        //echo "count=" . count($attendancearr);

        $this->view->workerhtmls = $this->genWorkerHtmls($workerarr, $attendancearr, $siteid, $date);

        $this->view->username = infox_common::getUsername();
    }

    private function genWorkerHtmls($workerarr, $attendancearr, $siteid, $dateobj) {
        $tablearr = array();
        $table = "";
        $sno = 0;
        foreach ($workerarr as $tmp) {
            $sno++;
            $workerid = $tmp->getId();

            $attendrecord = null;
            foreach ($attendancearr as $attendtmp) {
                $wid = $attendtmp->getWorker()->getId();
                if ($wid == $workerid) {
                    $attendrecord = $attendtmp;
                    break;
                }
            }

            $tabs = $this->getWorkerMonthAttendHtml($sno, $tmp, $attendrecord, $siteid, $dateobj);
            $tablearr[] = $tabs;
        }

        return $tablearr;
    }

    private function getWorkerMonthAttendHtml($sno, $worker, $attendrecord, $siteid, $dateobj, $nobtn = false) {
        $tabs = array();

        $tab = infox_salary::getWorkerSummaryTab($worker, $dateobj, $sno);
        $tabs[] = $tab;

        if ($nobtn) {
            $attendtab = infox_project::generateAttendanceTab($attendrecord, $dateobj, false, false);
        } else {
            $attendtab = infox_project::generateAttendanceTabWbtn2014($attendrecord, false, $siteid, $dateobj, $worker->getId());
        }
        $tabs[] = $attendtab;

        return $tabs;
    }

    private function calcMonthSummary($worker = null, $attendance, $rate, $otrate) {
        $summay = array();
        if (!$attendance) {
            $summary["totaldays"] = "";
            $summary["normalhours"] = "";
            $summary["normalsalary"] = "";
            $summary["othours"] = "";
            $summary["otsalary"] = "";
            $summary["totalhours"] = "";
            $summary["totalsalary"] = "";
            $summary["fooddays"] = "";
        } else {
            $wid = $attendance->getWorker()->getId();
            $month = $attendance->getMonth()->format("Y-m-d");
            ;

            $days = "";
            for ($i = 1; $i <= 31; $i++) {
                $day = "s.day$i";
                if ($i != 31) {
                    $day .= ",";
                }

                $days .= $day;
            }

            $query = "SELECT $days FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
            $result = $this->_em->createQuery($query)->getResult();
            //print_r($result);

            $totaldays = 0;
            $normalhours = 0;
            $normalsalary = 0;
            $othours = 0;
            $otsalary = 0;

            $fooddays = 0;
            foreach ($result[0] as $tmp) {
                if ($tmp) {
                    $totaldays++;

                    // normal work
                    $tmparr = explode(";", $tmp);
                    if (array_key_exists(0, $tmparr)) {
                        $workhours = $tmparr[0];
                        if ($workhours >= 8) {
                            $normalhours += 8;
                            $othours += ($workhours - 8);
                        } else {
                            $normalhours += $workhours;
                        }
                    }

                    if (array_key_exists(1, $tmparr)) {
                        $food = $tmparr[1];
                        $fooddays += ($food === "1") ? 1 : 0;
                    }
                }
            }

            $normalsalary = $normalhours * $rate;
            $otsalary = $othours * $otrate;

            $summary["totaldays"] = $totaldays;
            $summary["normalhours"] = $normalhours;
            $summary["normalsalary"] = $normalsalary;
            $summary["othours"] = $othours;
            $summary["otsalary"] = $otsalary;
            $summary["totalhours"] = $othours + $normalhours;
            $summary["totalsalary"] = $otsalary + $normalsalary;
            $summary["fooddays"] = $fooddays;
        }

        return $summary;
    }

    // data format: 
    //  "8;1", means work 8 hours, and 1 means had food also
    //  "9;0", means work 8 hours, and ot 1 hour, and 0 means no food
    private function getAttendFoodData($record) {
        /*
          $query = "SELECT s FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
          //echo $query;
          $query = $this->_em->createQuery($query);
          $result = $query->getResult();
         */

        $wid = $record->getWorker()->getId();
        $month = $record->getMonth()->format("Y-m-01");

        $days = "";
        for ($i = 1; $i <= 31; $i++) {

            $day = "s.day$i";
            if ($i != 31) {
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
        foreach ($tmparr as $tmp) {
            $arr = explode(";", $tmp);

            $attendarr[] = array_key_exists(0, $arr) ? $arr[0] : "&nbsp;";
            $foodarr[] = array_key_exists(1, $arr) ? $arr[1] : "&nbsp;";
        }

        $newresult = array($attendarr, $foodarr);
        //print_r($newresult);

        return $newresult;
    }

    public function attendialogAction() {
        infox_common::turnoffLayout($this->_helper);

        // date        
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr . "01");
        $this->view->date = $date;
        $this->view->monthstr = $monthstr;

        // worker info
        $wid = $this->getParam("wid", 0);
        $worker = infox_worker::getWorkerdetailsById($wid);
        $this->view->workerdetails = $worker;

        // site info
        $siteid = $this->getParam("sid", 0);
        $this->view->siteid = $siteid;
        $site = infox_project::getSiteById($siteid);
        $this->view->site = $site;

        // active sites
        $sites1 = $this->_site->findBy(array("completed" => FALSE));
        $sites2 = $this->_site->findBy(array("completed" => NULL));
        $sites = array_merge($sites1, $sites2);

        /*
          $siteoptions = '<option value="0">选择工地</option>';
          foreach ($sites as $tmp) {
          $name = $tmp->getName();
          $id = $tmp->getId();
          $option = '<option value="' . $id . '">' . $name . '</option>';
          $siteoptions .= $option;
          }
          $sitesel = '<select id="site" name="site" data-mini="true">' . $siteoptions . '</select>';
         */

        // worker atten
        $record = infox_project::getAttendanceByIdMonth($wid, $date);
        //if(!$record) { echo "xxxxx"; }
        $this->view->attendance = $record;

        $workerarr = infox_worker::getworkerlistbysitedateobj($site, $date);
        $sno = 0;
        foreach ($workerarr as $tmp) {
            $sno++;
            if ($tmp->getId() == $wid) {
                //$tabs = $this->getWorkerMonthAttendHtml($sno, $worker, $record, $siteid, $monthstr, true);                
                //$this->view->workerattendtabs = $tabs;
                $tab = infox_salary::getWorkerSummaryTab($worker, $date);
                $this->view->workersummarytab = $tab;
                break;
            }
        }

        // create tab just like datepicker
        $dayinmonth = $date->format("Y-m-d");
        $dayofweek = date('w', strtotime($dayinmonth));
        $attendmonth = $date->format("m");
        $attendyear = $date->format("Y");
        $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $attendmonth, $attendyear);
        //echo "dayinmonth=$dayinmonth, dayofweek=$dayofweek,daysinmonth=$daysinmonth";
        // find attend record
        $attendobj = $this->_siteattendance->findOneBy(array('worker' => $worker, 'month' => $date));
        //print_r($attendobj);
        //echo "day20 =" . $attendobj['day20'];

        $datetab = '<table id="datetab"><thead>            
                    <tr><th>Sunday</th><th>Monday</th><th>Thursday</th><th>Wednesday</th>
                        <th>Tuesday</th><th>Friday</th><th>Saturday</th>
                    </tr>
                    </thead>';

        $daycount = 0;
        $trs = "";
        $sitelatest = 0;

        for ($i = 0; $i < 6; $i++) {
            $tr = "";
            $tds = "";

            $dayofweek = ($i == 0) ? $dayofweek : 0;

            if ($i == 0) {
                for ($j = 0; $j < $dayofweek; $j++) {
                    $tds .= "<td></td>";
                }
            }
            for ($k = 0; $k < 7 - $dayofweek; $k++) {
                $daycount++;
                if ($daycount > $daysinmonth) {
                    break;
                }

                $daystr = "<div>$daycount</div>";
                $daydata = $attendobj['day' . $daycount] ? $attendobj['day' . $daycount] : "";

                $tmparr = explode(";", $daydata);
                $hoursdata = key_exists(0, $tmparr) ? $tmparr[0] : "";
                $piecedata = key_exists(1, $tmparr) ? $tmparr[1] : "";

                $input1 = '<input type="text" class="dayvalue" id="date' . $daycount . '" '
                        . 'name="date' . $daycount . '" value="' . $hoursdata . '" placeholder="">';
                $input2 = '<input type="text" class="dayvalue" id="piece' . $daycount . '" '
                        . 'name="piece' . $daycount . '" value="' . $piecedata . '" placeholder="">';

                // site support
                $sitedata = key_exists(2, $tmparr) ? $tmparr[2] : 0;
                //$sitelatest = $sitedata = ($sitedata != 0) ? $sitedata : $sitelatest;

                $siteoptions = '<option value="0">选择工地</option>';
                foreach ($sites as $tmp) {
                    $name = $tmp->getName();
                    $id = $tmp->getId();
                    if ($sitedata == $id) {
                        $option = '<option value="' . $id . '" selected>' . $name . '</option>';
                    } else {
                        $option = '<option value="' . $id . '" >' . $name . '</option>';
                    }
                    $siteoptions .= $option;
                }

                $sitesel = '<select id="site" name="site' . $daycount
                        . '" data-mini="true">' . $siteoptions . '</select>';
                $divinputs = $sitesel . '<div class="ui-grid-a">'
                        . '<div class="ui-block-a">' . $input1 . '</div>'
                        . '<div class="ui-block-b">' . $input2 . '</div>'
                        . '</div>';

                $daystr .= $divinputs;
                $tds .= "<td>$daystr</td>";
            }

            $tr = "<tr>$tds</tr>
                    ";
            $trs.=$tr;
        }

        //echo "trs=" . htmlspecialchars($trs);
        $datetab .= "<tbody>$trs</tbody>
                </table>";
        $this->view->datetab = $datetab;
    }

    public function savedailyinputAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $data = $requests['data'];
        if (!$data) {
            //echo "";
            return;
        }
        infox_project::savedailyAttend($requests);
    }

    public function postattendmonthAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $wid = $requests['wid'];
        $workerobj = $this->_workerdetails->findOneBy(array('id' => $wid));

        $month = $requests['month'];
        $monthobj = new DateTime($month . "01");
        $monthstr = $monthobj->format("Y-m-d");

        infox_project::saveMonthAttend($requests);
        infox_salary::saveMonthSalary($workerobj, $monthobj);
    }

    public function postattendAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

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
        if (!$record) {// create atten
            infox_project::createWorkerAtten($wid, $monthobj);
        }
        // update atten
        $dateobj = new Datetime($date);
        //infox_project::updateWorkerAtten($record, $dateobj, $salary);
        infox_project::updateWorkerAtten($wid, $dateobj, $attend, $food);
    }

    public function attendsheetAction() {
        infox_common::turnoffView($this->_helper);

        // date        
        $monthstr = $this->getParam("month", "");
        $dateobj = $date = new Datetime($monthstr . "01");
        $this->view->date = $date;
        $this->view->monthstr = $monthstr;

        // worker info
        $wid = $this->getParam("wid", 0);
        $workerobj = $worker = infox_worker::getWorkerdetailsById($wid);
        $this->view->workerdetails = $worker;

        $tab = infox_salary::getWorkerSummaryTab($workerobj, $dateobj, 0);
        echo $tab;
        return;
    }

    public function attendsheetAction1() {
        infox_common::turnoffLayout($this->_helper);

        // date        
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr . "01");
        $this->view->date = $date;
        $this->view->monthstr = $monthstr;

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
        foreach ($workerarr as $tmp) {
            $sno++;
            if ($tmp->getId() == $wid) {
                $tabs = $this->getWorkerMonthAttendHtml($sno, $worker, $record, $siteid, $monthstr, true);
                break;
            }
        }
        $this->view->workerattendtabs = $tabs;
    }

    public function attendsheetAction2() {
        infox_common::turnoffLayout($this->_helper);

        // date        
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr);
        $this->view->date = $date;
        $this->view->monthstr = $monthstr;
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
        for ($i = 0; $i < 31; $i++) {
            $j = $i + 1;
            $days .= "s.day" . $j;
            if ($i != 30) {
                $days .=",";
            }
        }

        $query = "SELECT $days FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
        echo $query;
        $query = $this->_em->createQuery($query);
        $result = $query->getResult();

        //var_dump($result);        
    }

    public function attendquickAction() {
        infox_common::turnoffLayout($this->_helper);

        // date     
        $monthstr = $this->getParam("month", "");
        $date = new Datetime($monthstr);
        $this->view->date = $date;
        $this->view->monthstr = $monthstr;
        $month = $date->format("Y-m-01");

        $siteid = $this->getParam("sid", 0);
        $siteobj = $this->_site->findOneBy(array("id" => $siteid));
        $this->view->site = $siteobj;
        $this->view->siteid = $siteid;

        $workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);
        $this->view->workerarr = $workerarr;
        //echo "workers=" . count($workerarr);
        $attendancearr = infox_project::getAttendanceByWorkerMonth($workerarr, $date);
        $this->view->attendancearr = $attendancearr;
    }

    public function quicksubmitAction1() {
        infox_common::turnoffView($this->_helper);
        //infox_common::turnoffLayout($this->_helper);
        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $quickdate = $this->getParam("quickdate", "");
        echo "quickdate=$quickdate";
        return;

        $date = new Datetime($quickdate);
        $month = $date->format("Y-m-01");
        $monthobj = new Datetime($month);
        $siteid = $this->getParam("sid", 0);
        $siteobj = $this->_site->findOneBy(array("id" => $siteid));

        $workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);

        foreach ($workerarr as $tmp) {
            $wid = $tmp->getId();
            $attend = $this->getParam("attend$wid", "");
            $food = $this->getParam("food$wid", "");

            //$record = $this->_siteattendance->findOneBy();
            $record = infox_project::getWorkerAtten($wid, $monthobj);
            if (!$record) {// create atten
                infox_project::createWorkerAtten($wid, $monthobj);
            }
            // update atten
            infox_project::updateWorkerAtten($wid, $date, $attend, $food);
        }

        //$url = "/project/attendance/attendancepage?&siteid=$siteid&month=" . $date->format("Ym");
        //echo $url;
        //$this->_redirect($url);
    }

    public function quicksubmitAction() {
        infox_common::turnoffView($this->_helper);
        //infox_common::turnoffLayout($this->_helper);
        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }


        $quickdate = infox_common::getSerializeArrayValueByKey($requests, "quickdate");
        //echo "quickdate=$quickdate"; return;
        $date = new Datetime($quickdate);
        $month = $date->format("Y-m-01");
        $monthobj = new Datetime($month);
        $siteid = infox_common::getSerializeArrayValueByKey($requests, "sid");
        $siteobj = $this->_site->findOneBy(array("id" => $siteid));

        $workerarr = infox_worker::getworkerlistbysitedateobj($siteobj, $date);

        foreach ($workerarr as $tmp) {
            $wid = $tmp->getId();
            $attend = infox_common::getSerializeArrayValueByKey($requests, "attend$wid");
            $food = infox_common::getSerializeArrayValueByKey($requests, "food$wid");

            //$record = $this->_siteattendance->findOneBy();
            $record = infox_project::getWorkerAtten($wid, $monthobj);
            if (!$record) {// create atten
                infox_project::createWorkerAtten($wid, $monthobj);
            }
            // update atten
            infox_project::updateWorkerAtten($wid, $date, $attend, $food);
        }
    }

}
