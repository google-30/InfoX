<?php

class infox_project {

    public static $_em;
    public static $_siteatten;
    public static $_workerdetails;
    public static $_site;

    public static function getRepos() {
        self::$_em = Zend_Registry::get('em');
        self::$_siteatten = self::$_em->getRepository('Synrgic\Infox\Siteattendance');
        self::$_workerdetails = self::$_em->getRepository('Synrgic\Infox\Workerdetails');
        self::$_site = self::$_em->getRepository('Synrgic\Infox\Site');
    }

    public static function getAttendanceByWorkerMonth($workerarr, $month) {
        $_em = Zend_Registry::get('em');
        $_siteatten = $_em->getRepository('Synrgic\Infox\Siteattendance');

        $attenarr = array();
        foreach ($workerarr as $tmp) {
            $record = $_siteatten->findOneBy(array("worker" => $tmp, "month" => $month));
            if ($record) {
                $attenarr[] = $record;
            }
        }

        return $attenarr;
    }

    public static function getAttendanceByIdMonth($wid, $month) {
        $_em = Zend_Registry::get('em');
        $_siteatten = $_em->getRepository('Synrgic\Infox\Siteattendance');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        $record = null;
        $worker = $_workerdetails->findOneBy(array("id" => $wid));
        if ($worker) {
            $record = $_siteatten->findOneBy(array("worker" => $worker, "month" => $month));
        }

        return $record;
    }

    public static function getSiteById($id) {
        $_em = Zend_Registry::get('em');
        $_repo = $_em->getRepository('Synrgic\Infox\Site');
        return $_repo->findOneBy(array("id" => $id));
    }

    public static function checkWorkerAtten($wid, $month) {
        $_em = Zend_Registry::get('em');
        $_siteatten = $_em->getRepository('Synrgic\Infox\Siteattendance');
        $_workerdetails = $_em->getRepository('Synrgic\Infox\Workerdetails');

        $worker = $_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return false;
        } else {
            $record = $_siteatten->findOneBy(array("worker" => $worker, 'month' => $month));
            if (!$record) {
                return false;
            } else {
                return true;
            }
        }
    }

    public static function getWorkerAtten($wid, $month) {
        self::getRepos();
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;

        $worker = $_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return null;
        } else {
            $record = $_siteatten->findOneBy(array("worker" => $worker, 'month' => $month));
            if (!$record) {
                return null;
            } else {
                return $record;
            }
        }
    }

    public static function createWorkerAtten($wid, $month) {
        self::getRepos();

        $worker = self::$_workerdetails->findOneBy(array("id" => $wid));
        $data = new \Synrgic\Infox\Siteattendance();
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

    public static function updateWorkerAtten1($wid, $date, $salary) {
        self::getRepos();
        $_em = self::$_em;
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;

// get column
        $day = $date->format("d");
        $day = intval($day);
//echo $day;
        $month = $date->format("Y-m-01");
//echo $month;

        $query = "UPDATE Synrgic\Infox\Siteattendance s SET s.day$day = '$salary' WHERE s.worker=$wid and s.month='$month'";
        $query = $_em->createQuery($query);
        $result = $query->getResult();
    }

    public static function updateWorkerAtten($wid, $date, $attend, $food) {
        self::getRepos();
        $_em = self::$_em;
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;

// get column
        $day = $date->format("d");
        $day = intval($day);
//echo $day;
        $month = $date->format("Y-m-01");
//echo $month;

        $dayvalue = $attend . ";" . $food;
        $query = "UPDATE Synrgic\Infox\Siteattendance s SET s.day$day = '$dayvalue' WHERE s.worker=$wid and s.month='$month'";
        $query = $_em->createQuery($query);
        $result = $query->getResult();
    }

    public static function updateWorkerAtten2($record, $date, $salary) {
        self::getRepos();
        $_em = self::$_em;
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;
        /*
          $query = $em->createQuery('UPDATE MyProject\Model\User u SET u.password = 'new' WHERE u.id IN (1, 2, 3)');
          $users = $query->getResult();
         */

// get column
        $day = $date->format("d");
//echo $day;
        $month = $date->format("Y-m-01");
//echo $month;

        $worker = $record->getWorker();
        $wid = $worker->getId();
        $query = "UPDATE Synrgic\Infox\Siteattendance s SET s.day$day = '$salary' WHERE s.worker=$wid and s.month='$month'";
        $query = $_em->createQuery($query);
        $result = $query->getResult();
    }

// monthstr format is "2013-10"
    public static function getAttendanceByMonth($monthstr) {
        self::getRepos();
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;

        $month = new Datetime($monthstr . "-01");
        $records = $_siteatten->findBy(array("month" => $month));

        return $records;
    }

    public static function getAttendanceByMonthSheet($monthstr, $sheet) {
        self::getRepos();
        $_workerdetails = self::$_workerdetails;
        $_siteatten = self::$_siteatten;

        $month = new Datetime($monthstr . "-01");
        $records = $_siteatten->findBy(array("month" => $month));

        $attendarr = array();
        foreach ($records as $tmp) {
            $worker = $tmp->getWorker();

            if ($worker->getSheet() == $sheet) {
                $attendarr[] = $tmp;
            }
        }

        return $attendarr;
    }

    public static function generateAttendanceTab($attendrecord, $monthstr, $attendbtn = false, $highlight = false) {
        self::getRepos();
        $attendresult = self::getAttendFoodData($attendrecord);

        $nowdate = new Datetime("");
        $today = $nowdate->format("d");
        $todaystyle = $highlight ? "background:#ff5c5c;" : "";

        $attendDate = new Datetime($monthstr . "-01");
        $attendmonth = $attendDate->format("m");
        $attendyear = $attendDate->format("Y");
        $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $attendmonth, $attendyear);

        $attendtab = "<table>";
        $tds = "";
        for ($i = 0; $i < $daysinmonth; $i++) {
            $j = $i + 1;
            $value = ($j < 10) ? "0$j" : $j;

            $td = ($j == $today) ? '<td style="' . $todaystyle . '">' . $value . '</td>' : "<td>$value</td>";
            $tds .= $td;
        }
        $tr = '<tr><td class="fixwidthcol">日期</td>' . $tds;

        if ($attendbtn) {
            if ($attendrecord) {
                $worker = $url = "/project/attendance/attendialog?" . "sid=$siteid&month=$monthstr&wid=$workerid";
                $dialog = '<a href="' . $url . '" data-rel="dialog" data-role="button" data-mini="true" data-theme="b">考勤</a>';
                $attendbtntd = "<td rowspan=3>$dialog</td>";
            }
        } else {
            $attendbtntd = '';
        }
        $tr .= $attendbtntd . "</tr>";

        $attendtab .= $tr;

        $tds = "";
        for ($i = 0; $i < $daysinmonth; $i++) {
            $j = $i + 1;
            $value = $attendresult[0][$i];

            $tmparr = explode(";", $value);
            $hoursdata = key_exists(0, $tmparr) ? $tmparr[0] : "";
            $piecedata = key_exists(1, $tmparr) ? $tmparr[1] : "";
            $value = $hoursdata != "" ? $hoursdata : $piecedata;

            $td = "<td>$value</td>";
            $tds .= $td;
        }
        $tr = "<tr><td>数据</td>$tds</tr>
";
        $attendtab .= $tr;

        /*
          $tds = "";
          for ($i = 0; $i < $daysinmonth; $i++) {
          $j = $i + 1;
          $value = $attendresult[1][$i];

          $td = "<td>$value</td>";
          $tds .= $td;
          }
          $tr = "<tr><td>伙食</td>$tds</tr>
          ";
          $attendtab .= $tr;
         * 
         */
        $attendtab .= "</table>";

        return $attendtab;
    }

    //public static function generateAttendanceTabWbtn($attendrecord, $highlight = false, $siteid, $monthstr, $workerid) {
    public static function generateAttendanceTabWbtn($attendrecord, $highlight = false, $siteid, $dateobj, $workerid) {
        self::getRepos();
        $attendresult = self::getAttendFoodData($attendrecord);

        $monthstr = $dateobj->format("Ym");
        $nowdate = new Datetime("");
        $nowmonthstr = $nowdate->format("Ym");
        //$highlight = ($nowmonthstr == $monthstr) ? true : false;
        $today = $nowdate->format("d");
        $todaystyle = $highlight ? "background:#ff5c5c;" : "";

        $attendDate = new Datetime($monthstr . "01");
        $attendmonth = $attendDate->format("m");
        $attendyear = $attendDate->format("Y");
        $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $attendmonth, $attendyear);

        $attendtab = "<table>";
        $tds = "";
        for ($i = 0; $i < $daysinmonth; $i++) {
            $j = $i + 1;
            $value = ($j < 10) ? "0$j" : $j;

            $td = ($j == $today) ? '<td style="' . $todaystyle . '">' . $value . '</td>' : "<td>$value</td>";
//$td = "<td>$value</td>";
            $tds .= $td;
        }
        $tr = '<tr><td class="fixwidthcol">日期</td>' . $tds;

        $url = "/project/attendance/attendialog?" . "sid=$siteid&month=$monthstr&wid=$workerid";
        $dialog = '<a href="' . $url . '" data-rel="dialog" data-role="button" data-mini="true" data-theme="b">考勤</a>';
        $attendbtntd = "<td rowspan=2>$dialog</td>";
        $tr .= $attendbtntd . "</tr>"
                . "";
        $attendtab .= $tr;

        $tds = "";
        for ($i = 0; $i < $daysinmonth; $i++) {
            $j = $i + 1;
            $value = $attendresult[0][$i];
            
            $tmparr = explode(";", $value);
            $hoursdata = key_exists(0, $tmparr) ? $tmparr[0] : "";
            $piecedata = key_exists(1, $tmparr) ? $tmparr[1] : "";
            $value = $hoursdata != "" ? $hoursdata : $piecedata;
            
            $td = "<td>$value</td>";
            $tds .= $td;
        }
        $tr = "<tr><td>数据</td>$tds</tr>
";
        $attendtab .= $tr;

        /*
          $tds = "";
          for($i=0; $i<$daysinmonth; $i++)
          {
          $j = $i +1;
          $value = $attendresult[1][$i];

          $td = "<td>$value</td>";
          $tds .= $td;
          }
          $tr = "<tr><td>伙食</td>$tds</tr>
          ";
          $attendtab .= $tr;
         * 
         */
        $attendtab .= "</table>
                ";

        return $attendtab;
    }

    public static function getAttendFoodData($record) {
        if (!$record) {
            $attendarr = array();
            $foodarr = array();

            for ($i = 1; $i <= 31; $i++) {
                $attendarr[] = "&nbsp;
";
                $foodarr[] = "&nbsp;
";
            }

            $newresult = array($attendarr, $foodarr);
        } else {
            $wid = $record->getWorker()->getId();
            $month = $record->getMonth()->format("Y-m-01");

            $days = "";
            for ($i = 1; $i <= 31; $i++) {
                $day = "s.day$i";
                if ($i != 31) {
                    $day .= ", ";
                }

                $days .= $day;
            }

            $query = "SELECT $days FROM Synrgic\Infox\Siteattendance s WHERE s.worker = $wid and s.month = '$month'";
            //echo $query; return;
            $query = self::$_em->createQuery($query);
            $result = $query->getResult();
            //echo "result = "; var_dump($result);
            //return $result;

            $attendarr = array();
            $foodarr = array();

            $tmparr = $result[0];
            foreach ($tmparr as $tmp) {
                $arr = explode(";
", $tmp);

                $attendarr[] = array_key_exists(0, $arr) ? $arr[0] : "&nbsp;
";
                $foodarr[] = array_key_exists(1, $arr) ? $arr[1] : "&nbsp;
";
            }

            $newresult = array($attendarr, $foodarr);
            //echo print_r($newresult) . "<br>";
        }

        return $newresult;
    }

    public static function createSiteByName($name) {
        self::getRepos();
        $_site = self::$_site;

        $data = $_site->findOneBy(array("name" => $name));
        if ($data) {
            return $data->getId();
        }

        $data = new \Synrgic\Infox\Site();
        $data->setName($name);

        self::$_em->persist($data);
        try {
            self::$_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        return $data->getId();
    }

    public static function savedailyAttend($requests) {
        self::getRepos();

        $data = $requests['data'];
        if (!$data) {
            //echo "";
            return;
        }
        $wid = (int) $requests['wid'];
        $month = $requests['month'];
        $id = $requests['id'];
        $day = substr($id, 4);

        $workerobj = self::$_workerdetails->findOneBy(array('id' => $wid));
        $monthobj = new DateTime($month . "01");
        $monthstr = $monthobj->format("Y-m-d");
        $_siteatten = self::$_siteatten;
        $attendobj = $_siteatten->findOneBy(array("worker" => $workerobj, 'month' => $monthobj));

        if ($attendobj) {
            
        } else {
            $attendobj = new Synrgic\Infox\Siteattendance();
            $attendobj->setWorker($workerobj);
            $attendobj->setMonth($monthobj);
            self::$_em->persist($attendobj);
            self::$_em->flush();
        }

        //return;        
        try {
            $query = "UPDATE Synrgic\Infox\Siteattendance s SET s.day$day = '$data' "
                    . "WHERE s.worker=$wid and s.month='$monthstr'";
            $query = self::$_em->createQuery($query);
            $result = $query->getResult();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    public static function saveMonthAttend($requests) {
        self::getRepos();

        $wid = (int) $requests['wid'];
        $workerobj = self::$_workerdetails->findOneBy(array('id' => $wid));

        $month = $requests['month'];
        $monthobj = new DateTime($month . "01");
        $monthstr = $monthobj->format("Y-m-d");

        $_siteatten = self::$_siteatten;
        $attendobj = $_siteatten->findOneBy(array("worker" => $workerobj, 'month' => $monthobj));

        if (!$attendobj) {
            $attendobj = new Synrgic\Infox\Siteattendance();
            $attendobj->setWorker($workerobj);
            $attendobj->setMonth($monthobj);
            self::$_em->persist($attendobj);
            self::$_em->flush();
        }

        //return;        
        //$id = $requests['id'];
        //$day = substr($id, 4);
        $updatestr = "";
        for ($i = 1; $i <= 31; $i++) {
            if (key_exists("date$i", $requests)) {
                $hour = $requests["date$i"];
                $piece = $requests["piece$i"];

                $value = implode(";", array($hour, $piece));
                //$daily = ($daily == "") 
                $updatestr .= "s.day$i='$value',";
            }
        }

        $updatestr = substr($updatestr, 0, strlen($updatestr) - 1);
        //echo $updatestr;        return;
        try {
            $query = "UPDATE Synrgic\Infox\Siteattendance s SET $updatestr "
                    . "WHERE s.worker=$wid and s.month='$monthstr'";
            $query = self::$_em->createQuery($query);
            $result = $query->getResult();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    public static function generateAttendanceSummaryTab($worker, $date) {
        $workerid = $worker->getId();
        $wpno = $worker->getWpno();
        $name = $worker->getNamechs();
        if (!$name || $name == "") {
            $name = $worker->getNameeng();
        }
        $worktype = $worker->getWorktype();

        // TODO: rate can be defined by staff
        // if 计时，currentrate; if 计件， monthrate
        //$currentrate = $price = $worker->getCurrentrate();
        //$otrate = infox_worker::getWorkerOtRate($worker);
        //$summary = $this->calcMonthSummary($worker, $attendrecord, $currentrate, $otrate);
        $summary = self::calcAttendanceSummary();
        $totaldays = $summary['totaldays'];
        $normalhours = $summary["normalhours"];
        $othours = $summary["othours"];
        $totalhours = $summary["totalhours"];
        $normalsalary = $summary["normalsalary"];
        $otsalary = $summary["otsalary"];
        $totalsalary = $summary["totalsalary"];
        $fooddays = $summary["fooddays"];

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

        $tr = "<tr><td>$sno</td><td>$wpno</td><td>$name</td><td>$price</td><td>$worktype</td>";
        $tr .= "<td>$normalhours</td><td>$normalsalary</td><td>$otrate</td><td>$othours</td><td>$otsalary</td><td>$totalhours</td>";
        $tr .= "<td>$totalsalary</td><td>$totaldays</td><td></td><td></td><td></td><td>$fooddays</td><td></td>";
        $tr .= "</tr>";

        $table .= $tr;
        $table .= "</table>";
        //$tabs[] = $table;
        return $table;
    }

    public static function calcAttendanceSummary() {
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

}
