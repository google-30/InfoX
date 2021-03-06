<?php

include_once 'infox_project.php';

class infox_salary {

    public static $_em;
    public static $_siteatten;
    public static $_workerdetails;
    public static $_salaryhcc;
    public static $_salaryhcb;
    public static $_salaryhtc;
    public static $_salaryhtb;
    public static $_setting;
    public static $_salaryall;
    public static $_salarysummary;
    public static $_site;

    public static function getRepos() {
        self::$_em = Zend_Registry::get('em');
        self::$_siteatten = self::$_em->getRepository('Synrgic\Infox\Siteattendance');
        self::$_workerdetails = self::$_em->getRepository('Synrgic\Infox\Workerdetails');

        self::$_salaryhcc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcc');
        self::$_salaryhcb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcb');
        self::$_salaryhtc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtc');
        self::$_salaryhtb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtb');

        self::$_salaryall = self::$_em->getRepository('Synrgic\Infox\Workersalaryall');
        self::$_setting = self::$_em->getRepository('Synrgic\Infox\Setting');

        self::$_salarysummary = self::$_em->getRepository('Synrgic\Infox\Salarysummary');
        
        self::$_site = self::$_em->getRepository('Synrgic\Infox\Site');
    }

    public static function getSettingArray() {
        $settingnames = array("cotmultiple", "botmultiple", "workerfood", "leaderfood",
            "absencedays", "absencelow", "absencehigh", "absencetotal",
            'bbasic', 'cbasic', 'fullmonaward');
        return $settingnames;
    }

    public static function getSettingBySectionName($section, $name) {
        self::getRepos();
        $setting = self::$_setting;
        $settingobj = $setting->findOneBy(array("section" => $section, "name" => $name));
        $value = $settingobj ? $settingobj->getValue() : "";

        return $value;
    }

    public static function setSettingBySectionName($section, $name, $value) {
        self::getRepos();
        $setting = self::$_setting;
        $settingobj = $setting->findOneBy(array("section" => $section, "name" => $name));
        $querystr = "update Synrgic\Infox\Setting setting set setting.value='" . $value
                . "' where setting.name='" . $name . "' and setting.section='" . $section . "'";
        //echo $querystr . "<br>";
        $query = self::$_em->createQuery($querystr);
        $result = $query->getResult();
    }

    public static function getReposBySheet($sheet = "HC.C") {
        self::getRepos();
        $repos = null;
        switch ($sheet) {
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

    public static function createSalaryRecordsByMonthSheet($monthstr, $sheet = "HC.C") {
        self::getRepos();
        $workerarr = infox_worker::getworkerlistbysheet($sheet);
        $salaryrepos = self::getReposBySheet($sheet);

        $month = new Datetime($monthstr . "-01");

        $records = $salaryrepos->findBy(array("month" => $month));

        $workersnorecord = array();
        foreach ($workerarr as $worker) {
            $flag = false;
            foreach ($records as $record) {
                if ($record->getWorker()->getId() == $worker->getId()) {// found
                    $flag = true;
                    break;
                }
            }

            if (!$flag) {//create record
                //self::createSalaryRecordBySheetWorkerMonth($sheet, $worker, $month);                
                $workersnorecord[] = $worker;
            }
        }

        self::createSalaryRecordsBySheetWorkersMonth($sheet, $workersnorecord, $month);
    }

    public function getSalaryRecordsByReposMonth($salaryrepos, $month) {
        //self::getRepos();
        $records = $salaryrepos->findBy(array("month" => $month));
    }

    // low Efficiency, consume too much cpu
    public static function createSalaryRecordsByMonthSheet1($monthstr, $sheet = "HC.C") {
        self::getRepos();
        $workerarr = infox_worker::getworkerlistbysheet($sheet);
        $salaryrepos = self::getReposBySheet($sheet);

        $month = new Datetime($monthstr . "-01");

        foreach ($workerarr as $worker) {
            $record = self::getSalaryRecordByReposWorkerMonth($salaryrepos, $worker, $month);
            if (!$record) {
                //echo "no record for " . $worker->getNamechs();
                // create record
                self::createSalaryRecordBySheetWorkerMonth($sheet, $worker, $month);
            }
        }
    }

    public static function getSalaryRecordByReposWorkerMonth($salaryrepos, $worker, $month) {
        $record = $salaryrepos->findOneBy(array("worker" => $worker, "month" => $month));
        return $record;
    }

    public static function createSalaryRecordsBySheetWorkersMonth($sheet, $workerarr, $month) {
        foreach ($workerarr as $worker) {
            $data = null;
            switch ($sheet) {
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

            if ($data) {
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

    public static function createSalaryRecordBySheetWorkerMonth($sheet, $worker, $month) {
        $data = null;
        switch ($sheet) {
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

        if ($data) {
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

    public static function createSalaryRecordByWorkerMonth($worker, $month) {
        $data = new \Synrgic\Infox\Workersalaryall();
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

    // sheet, month, workers not resigned    
    public static function getSalaryRecordsByMonthSheet($month, $sheet = "HC.C") {
        self::getRepos();
        $_salaryall = self::$_salaryall;

        $result = $_salaryall->findBy(array("month" => $month));
        if ($sheet == "ALL") {
            return $result;
        }

        $records = array();
        foreach ($result as $tmp) {
            $worker = $tmp->getWorker();
            $workersheet = $worker->getSheet();
            if ($workersheet == $sheet) {
                $records[] = $tmp;
            }
        }
        usort($records, function($a, $b) {
            $aid = $a->getWorker()->getId();
            $bid = $b->getWorker()->getId();
            if ($aid == $bid) {
                return 0;
            }
            return ($aid < $bid) ? -1 : 1;
        });

        return $records;
    }

    public static function updatePaymentDataByMonthSheet($month, $sheet = "HC.C") {
        $records = self::getSalaryRecordsByMonthSheet($month, $sheet);
        $siteatten = self::$_siteatten;

        foreach ($records as $record) {
            $worker = $record->getWorker();
            $attendrecord = $siteatten->findOneBy(array("month" => $month, "worker" => $worker));

            if (!$attendrecord) {// TODO: no attend, 
                continue;
            }
        }
    }

    public static function calcMonthSummaryByWorkerAttendRate1($worker, $attend, $currentrate, $otrate) {
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

    public static function calcMonthSummaryByWorkerAttendRate($worker, $attendance, $rate, $otrate) {
        $summay = array();
        $summary["totaldays"] = "";
        $summary["normalhours"] = "";
        $summary["normalsalary"] = "";
        $summary["othours"] = "";
        $summary["otsalary"] = "";
        $summary["totalhours"] = "";
        $summary["totalsalary"] = "";
        $summary["fooddays"] = "";

        if ($attendance) {
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
            $result = self::$_em->createQuery($query)->getResult();
            //print_r($result);

            $totaldays = 0;
            $normalhours = 0;
            $normalsalary = 0;
            $othours = 0;
            $otsalary = 0;
            $fooddays = 0;
            $totalsalary = 0;
            $piecesalary = 0;
            foreach ($result[0] as $tmp) {
                if ($tmp) {

                    if ($tmp == ";;0") {
                        continue;
                    }

                    $tmparr = explode(";", $tmp);
                    $hoursdata = key_exists(0, $tmparr) ? $tmparr[0] : "";
                    $piecedata = key_exists(1, $tmparr) ? $tmparr[1] : "";

                    if ($hoursdata && $hoursdata != "") {
                        $normalhours += ($hoursdata <= 8) ? $hoursdata : 8;
                        $othours += ($hoursdata > 8) ? ($hoursdata - 8) : 0;
                        $totaldays += ($hoursdata < 8) ? $hoursdata / 8 : 1;
                    } else if (($piecedata && $piecedata != "")) {
                        $totalsalary += $piecedata;
                        $piecesalary += $piecedata;
                        $totaldays++;
                    }
                }
            }

            $totalsalary += $normalhours * $rate + $othours * $otrate;

            // TODO: get basic salary from salary settings
            //$normalsalary = ($totalsalary > 600) ? 600 : $totalsalary;
            // TODO: get correct race define, for other sheet
            // get basic salary by race                       
            // get race 
            $wsheet = $worker->getSheet();
            $tmparr = explode(".", $wsheet);
            if (key_exists(1, $tmparr) && $tmparr[1] == "C") {
                $basic = self::$_setting->findOneBy(array("name" => "cbasic"));
                $basicval = $basic->getValue();
            } else {
                $basic = self::$_setting->findOneBy(array("name" => "bbasic"));
                $basicval = $basic->getValue();
            }
            $normalsalary = ($totalsalary > $basicval) ? $basicval : $totalsalary;

            $otsalary = $totalsalary - $normalsalary;

            $summary["totaldays"] = $totaldays;
            $summary["normalhours"] = $normalhours;
            $summary["normalsalary"] = $normalsalary;
            $summary["othours"] = $othours;
            $summary["otsalary"] = $otsalary;
            $summary["totalhours"] = $othours + $normalhours;
            $summary["totalsalary"] = $totalsalary;
            //$summary["fooddays"] = $fooddays;
            $summary["piecesalary"] = $piecesalary;
        }

        return $summary;
    }

    public static function updateOneSalaryRecordByAttend($salaryrecord, $attendance) {
        $worker = $salaryrecord->getWorker();
        $currentrate = self::getWorkerRate($salaryrecord); //$worker->getCurrentrate();
        $otrate = self::getWorkerOtRate($salaryrecord); //infox_worker::getWorkerOtRate($worker);

        $wid = $worker->getId();
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
        $result = self::$_em->createQuery($query)->getResult();

        $totaldays = 0;
        $normalhours = $salaryrecord->getNormalhours();
        $normalsalary = $salaryrecord->getNormalsalary();
        $othours = $salaryrecord->getOthours();
        ;
        $otsalary = $salaryrecord->getOtsalary();
        $fooddays = 0;
        $totalsalary = $salaryrecord->getTotalsalary();

        $totalsalary += $currentrate * $normalhours + $otrate * $othours;

        $salaryrecord->setNormalhours($normalhours);
        $salaryrecord->setNormalpay($normalpay);

        $salaryrecord->setOthours($othours);
        $salaryrecord->setOtprice($otrate);
        $salaryrecord->setOtpay($otpay);

        $salaryrecord->setAllhours($allhours);
        $salaryrecord->setAllpay($allpay);

        $salaryrecord->setAttenddays($totaldays);

        // food
        $setting = self::$_setting->findOneBy(array('name' => 'workerfood'));
        $workfood = $setting->getValue();
        $foodpay = $workfood * $fooddays;
        $salaryrecord->setFooddays($fooddays);
        $salaryrecord->setFoodpay($foodpay);

        self::$_em->persist($salaryrecord);
        try {
            self::$_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    public static function updateOneSalaryRecord($salaryrecord) {
        $worker = $salaryrecord->getWorker();
        $currentrate = self::getWorkerRate($salaryrecord);
        $otrate = self::getWorkerOtRate($salaryrecord);

        $wid = $worker->getId();
        $monthobj = $salaryrecord->getMonth();
        $month = $monthobj->format("Y-m-d");
        $attendmonth = $monthobj->format("m");
        $attendyear = $monthobj->format("Y");
        $daysinmonth = cal_days_in_month(CAL_GREGORIAN, $attendmonth, $attendyear);

        $days = "";
        for ($i = 1; $i <= $daysinmonth; $i++) {
            $day = "s.day$i";
            if ($i != $daysinmonth) {
                $day .= ",";
            }

            $days .= $day;
        }

        $query = "SELECT $days FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
        $result = self::$_em->createQuery($query)->getResult();

        $totaldays = 0;
        $normalhours = 0;
        $normalsalary = 0;
        $normalpay = 0;
        $othours = 0;
        $otsalary = 0;
        $fooddays = 0;

        $daydata = 0;
        $daypay = 0;
        $salarybydaycount = 0; // 计件日期
        $salarybyday = 0;

        $totaldays = 0;
        $normalhours = $salaryrecord->getNormalhours();
        $normalsalary = $salaryrecord->getNormalsalary();
        $othours = $salaryrecord->getOthours();
        $otsalary = $salaryrecord->getOtsalary();
        $fooddays = 0;
        $totalsalary = $salaryrecord->getTotalsalary();
        $piecesalary = $salaryrecord->getPiecesalary();
        $inadvance = $salaryrecord->getInadvance();

        $workersheet = $worker->getSheet();
        $basicsalary = 500;
        if ($workersheet == "HC.C" || $workersheet == "HT.C" || $workersheet == "Others.C") {
            $basicsalary = self::getSettingBySectionName("salary", "cbasic");
        } else {
            $basicsalary = self::getSettingBySectionName("salary", "bbasic");
        }

        $totalsalary = $piecesalary + $currentrate * $normalhours + $otrate * $othours;
        $normalsalary = ($totalsalary > $basicsalary) ? $basicsalary : $totalsalary;
        $otsalary = $totalsalary - $normalsalary;

        $salaryrecord->setNormalsalary($normalsalary);
        $salaryrecord->setOtsalary($otsalary);
        $salaryrecord->setTotalsalary($totalsalary);

        $otherfee = $salaryrecord->getOtherfee();
        $absencefines = $salaryrecord->getAbsencefines();
        $rtmonthpay = $salaryrecord->getRtmonthpay();
        $utfee = $salaryrecord->getUtfee();
        $utallowance = $salaryrecord->getUtallowance();
        $fullmonaward = $salaryrecord->getFullmonaward();
        $foodpay = $salaryrecord->getFoodpay();

        $finalsalary = $totalsalary - abs($foodpay) - abs($absencefines) - abs($rtmonthpay) - abs($utfee) + abs($utallowance) + $otherfee + abs($fullmonaward) - abs($inadvance);
        $salaryrecord->setSalary($finalsalary);

        self::$_em->persist($salaryrecord);
        try {
            self::$_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    public static function updateSalaryRecordsByAttend($salaryrecords, $attendarr) {
        //echo "updateSalaryRecordsByAttend<br>";
        $updatearr = array();
        $recordOne = null;
        $attendOne = null;

        foreach ($salaryrecords as $record) {
            $flag = false;
            $salaryworker = $record->getWorker();
            foreach ($attendarr as $attendance) {
                $attendworker = $attendance->getWorker();
                if ($attendworker->getId() == $salaryworker->getId()) {
                    $flag = true;

                    //self::updateOneSalaryRecordByAttend($record, $attendance);
                    self::updateOneSalaryRecord($record);
                    break;
                }
            }
        }
    }

    public static function generatePaymentTabByRecord($record, $inputbtn = false) {
        $worker = $record->getWorker();
        $wid = $worker->getId();
        $monthstr = $record->getMonth()->format("Y-m");

        $normalhours = $record->getNormalhours();
        $normalpay = $record->getNormalsalary();

        $othours = $record->getOthours();
        $otpay = $record->getOtsalary();
        //$otprice = $record->getOtprice();
        $otprice = infox_salary::getWorkerOtRate($record);

        $allhours = $record->getTotalhours();
        $allpay = $record->getTotalsalary();

        $attenddays = $record->getAttenddays();
        $absencedays = $record->getAbsencedays();
        $absencefines = $record->getAbsencefines();
        //$projectpay = "xx";
        $fooddays = $record->getFooddays();
        $foodpay = $record->getFoodpay() ? "-" . $record->getFoodpay() : "&nbsp;";

        $rtmonthpay = $record->getRtmonthpay();
        $rtmonths = $record->getRtmonths();
        $rtall = $record->getRtall();
        $utfee = $record->getUtfee();
        $utallowance = $record->getUtallowance();
        $otherfee = $record->getOtherfee();
        $inadvance = $record->getInadvance();
        $salary = $record->getSalary();
        $netpay = $record->getNetpay();
        $piecesalary = $record->getPiecesalary();
        $fullmonaward = $record->getFullmonaward();

        $workersheet = $worker->getSheet();
        $basicsalary = 500;
        if ($workersheet == "HC.C" || $workersheet == "HT.C" || $workersheet == "Others.C") {
            $basicsalary = self::getSettingBySectionName("salary", "cbasic");
        } else {
            $basicsalary = self::getSettingBySectionName("salary", "bbasic");
        }

        if ($allpay > $basicsalary) {
            $normalpay = $basicsalary;
            $otpay = $allpay - $basicsalary;
        } else {
            //$normalpay = $basicsalary;
            $otpay = 0;
        }


        $tab = "<table>";
        $tab .= "<tr><td colspan=2>正常工作</td><td colspan=3>加班工作</td><td colspan=3>总工作</td>"
                . "<td rowspan=2>考勤天数</td><td colspan=2>缺勤罚款</td>";
        $tab .="<td rowspan=2>满勤奖</td>";
        $tab .= "<td rowspan=2>伙食费</td><td colspan=3>预扣税</td><td colspan=2>水电费</td>"
                . "<td rowspan=2>其他补扣</td><td rowspan=2>提前结帐</td><td rowspan=2>当月净工资</td>";

        if ($inputbtn) {
            $url = "/salary/salary/datainput?wid=$wid&month=$monthstr";
            $tab .= '<td rowspan=3><a href="' . $url . '" data-rel="dialog" data-role="button" data-mini="true" data-theme="b">输入</a></td>';
        }
        $tab .= '</tr>';
        $tab .= "<tr><td>小时</td><td>金额</td>"
                . "<td>单价</td><td>小时</td><td>金额</td>"
                . "<td>计时工时</td><td>计件工资</td><td>金额</td>"
                . "<td>天数</td><td>金额</td>";

        // food
        //$tab .= "<td>天数</td><td>金额</td>";
        $tab .= "<td>当月</td><td>月数</td><td>累计</td>";
        $tab .= "<td>扣款</td><td>补助</td>";
        $tab .= "</tr>";

        // insert value
        $tdclass = 'class="workerpay"';
        $tab .= "<tr><td $tdclass>$normalhours</td><td $tdclass>$normalpay</td>";
        $tab .= "<td $tdclass>$otprice</td><td $tdclass>$othours</td><td $tdclass>$otpay</td>";
        $tab .= "<td $tdclass>$allhours</td><td $tdclass>$piecesalary</td><td $tdclass>$allpay</td>"
                . "<td $tdclass>$attenddays</td>";
        $tab .= "<td $tdclass>$absencedays</td><td $tdclass>$absencefines</td>";
        $tab .= "<td $tdclass>$fullmonaward</td>";
        //$tab .= "<td>$projectpay</td>";
        $tab .= "<td $tdclass>$foodpay</td>";
        $tab .= "<td $tdclass>$rtmonthpay</td><td $tdclass>$rtmonths</td><td $tdclass>$rtall</td>";
        $tab .= "<td $tdclass>$utfee</td><td $tdclass>$utallowance</td>";
        $tab .= "<td $tdclass>$otherfee</td><td $tdclass>$inadvance</td><td $tdclass>$salary</td>";
        $tab .= "</tr>";

        $tab .= "</table>";
        return $tab;
    }

    public static function getWorkerRate($salaryrecord) {
        self::getRepos();
        $sr = $salaryrecord;
        $worker = $sr->getWorker();
        $currentrate = $worker->getCurrentrate();
        $inputrate = $sr->getRate();
        $actualrate = ($inputrate != "" && $inputrate != 0) ? $inputrate : $currentrate;
        return $actualrate;
    }

    public static function getWorkerOtRate($salaryrecord) {
        self::getRepos();

        $sr = $salaryrecord;
        $worker = $sr->getWorker();
        $race = infox_worker::getWorkerRace($worker);

        $currentrate = $worker->getCurrentrate();
        $inputrate = $sr->getRate();
        $actualrate = ($inputrate != "" && $inputrate != 0) ? $inputrate : $currentrate;

        $_setting = self::$_setting;
        $otrate = 0;
        switch ($race) {
            case 0:
                //$otrate = $rate;
                $setting = $_setting->findOneBy(array("name" => "cotmultiple"));
                $otrate = $actualrate * $setting->getValue();
                break;
            case 1:
                //$otrate = $rate * 1.5;
                $setting = $_setting->findOneBy(array("name" => "botmultiple"));
                $otrate = $actualrate * $setting->getValue();
                break;
        }

        return $otrate;
    }

    public static function saveMonthSalary($workerobj, $dateobj) {
        self::getRepos();
        $_salaryall = self::$_salaryall;
        $salaryrecord = $_salaryall->findOneBy(array("worker" => $workerobj, "month" => $dateobj));
        if (!$salaryrecord) {
            self::createSalaryRecordByWorkerMonth($workerobj, $dateobj);
        }
        $attend = self::$_siteatten->findOneBy(array("worker" => $workerobj, "month" => $dateobj));
        $summary = self::calcMonthSummaryByWorkerAttendRate($workerobj, $attend, 0, 0);
        self::saveMonthSummary($summary, $workerobj, $dateobj);
    }

    public static function saveMonthSummary($summary, $workerobj, $dateobj) {
        $salaryrecord = self::$_salaryall->findOneBy(array("worker" => $workerobj, "month" => $dateobj));
        $salaryrecord->setAttenddays($summary["totaldays"]);
        $salaryrecord->setNormalhours($summary['normalhours']);
        $salaryrecord->setNormalsalary($summary["normalsalary"]);
        $salaryrecord->setOthours($summary['othours']);
        $salaryrecord->setOtsalary($summary['otsalary']);
        $salaryrecord->setTotalhours($summary['totalhours']);
        $salaryrecord->setTotalsalary($summary["totalsalary"]);
        $salaryrecord->setPiecesalary($summary["piecesalary"]);

        self::$_em->persist($salaryrecord);
        self::$_em->flush();
    }

    public static function getWorkerSummaryTab($workerobj, $dateobj, $sno) {
        self::getRepos();

        $workerid = $workerobj->getId();
        $wpno = $workerobj->getWpno();
        $name = $workerobj->getNamechs();
        $eeeno = $workerobj->getEeeno();

        if (!$name || $name == "") {
            $name = $workerobj->getNameeng();
        }
        $worktype = $workerobj->getWorktype();

        $salaryrecord = self::$_salaryall->findOneBy(array("worker" => $workerobj, "month" => $dateobj));
        $summary = array();
        $totaldays = "";
        $normalhours = "";
        $normalsalary = "";
        $othours = "";
        $otsalary = "";
        $totalhours = "";
        $totalsalary = "";
        $otrate = "";
        $rate = "";
        $piecesalary = "";
        if ($salaryrecord) {
            $totaldays = $summary["totaldays"] = $salaryrecord->getAttenddays();
            $normalhours = $summary["normalhours"] = $salaryrecord->getNormalhours();
            $normalsalary = $summary["normalsalary"] = $salaryrecord->getNormalsalary();
            $othours = $summary["othours"] = $salaryrecord->getOthours();
            $otsalary = $summary["otsalary"] = $salaryrecord->getOtsalary();
            $totalhours = $summary["totalhours"] = $salaryrecord->getTotalhours();
            $totalsalary = $summary["totalsalary"] = $salaryrecord->getTotalsalary();
            $otrate = $salaryrecord->getOtrate();
            $rate = $salaryrecord->getRate();
            //$summary["fooddays"] = ""; 
            $piecesalary = $salaryrecord->getPiecesalary();
        }
        //$sno = "";
        $fooddays = "";

        $table = '<table class="attendsum">';
        // worker info and month summary
        $tr = "";
        $tr .= '<tr><th rowspan="2" class="fixwidthcol">序号</th><th colspan=4>工人信息</th>';
        /*
          $tr .= '<th colspan=2>正常工作</th><th colspan=3>加班工作</th><th colspan=3>总工作</th>
          <th rowspan=2>考勤天数</th><th colspan=2>缺勤罚款</th><th rowspan="2">项目总工资</th>
          <th rowspan="2">伙食费</th></tr>
          ';
         * 
         */
        $tr .= '<th colspan=2>正常工作</th><th colspan=3>加班工作</th><th colspan=3>总工作</th>
            <th rowspan=2>考勤天数</th></tr>
';
        $table .= $tr;
        $tr = '<tr><th>Eee No</th><th>姓名</th><th>单价</th><th>工种</th>';
        $tr .= '<th>小时</th><th>金额</th><th>单价</th><th>小时</th><th>金额</th>';
        //$tr .= '<th>计时工时</th><th>计件工资</th><th>总金额</th><th>天数</th><th>金额</th><th>天数</th><th>金额</th></tr>';
        $tr .= '<th>计时工时</th><th>计件工资</th><th>总金额</th></tr>';
        $table .= $tr;

        $tr = "<tr><td>$sno</td><td>$eeeno</td><td>"
                . '<strong style="color:red;">' . $name . "</strong>"
                . "</td><td>$rate</td><td>$worktype</td>";
        $tr .= "<td>$normalhours</td><td>$normalsalary</td><td>$otrate</td><td>$othours</td><td>$otsalary</td>"
                . "<td>$totalhours</td><td>$piecesalary</td>";
        $tr .= "<td>$totalsalary</td><td>$totaldays</td>";
        $tr .= "</tr>";

        $table .= $tr;
        $table .= "</table>";
        return $table;
    }

    public static function updateSalarySummaryBySalaryRecord($salaryrecord) {
        self::getRepos();
        $worker = $salaryrecord->getWorker();
        $sheet = $worker->getSheet();
        $month = $salaryrecord->getMonth();

        $_salarysummary = self::$_salarysummary;
        $summaryrecord = $_salarysummary->findOneBy(array("month" => $month));
        if (!$summaryrecord) {
            $summaryrecord = new \Synrgic\Infox\Salarysummary();
        }
        $_salaryall = self::$_salaryall;
        $allrecords = $_salaryall->findBy(array("month" => $month));
        $records = array();
        $companysalary = 0;

        foreach ($allrecords as $record) {
            $tmpworker = $record->getWorker();
            $tmpsheet = $tmpworker->getSheet();
            if ($tmpsheet == $sheet) {
                //$records[] = $record;
                $salary = $record->getSalary();
                $companysalary += $salary;
            }
        }

        switch ($sheet) {
            case "HC.C":
                $summaryrecord->setHccsalary($companysalary);
                break;
            case "HC.B":
                $summaryrecord->setHcbsalary($companysalary);
                break;
            case "HT.C":
                $summaryrecord->setHtcsalary($companysalary);
                break;
            case "HT.B":
                $summaryrecord->setHtbsalary($companysalary);
                break;
            case "Others":
                $summaryrecord->setOtherssalary($companysalary);
                break;
            default:
                break;
        }

        $summaryrecord->setMonth($month);

        $em = self::$_em;
        $em->persist($summaryrecord);
        $em->flush();
    }

    public static function generateSalaryTabs($salaryrecords, $inputbtn) {
        $salarytabs = array();
        $sno = 0;

        if (!count($salaryrecords)) {
            echo "No salary records in db, please check.";
            return;
        }
        //$monthstr = $salaryrecords[0]->getMonth()->format("Y-m");

        foreach ($salaryrecords as $record) {
            $tmparr = array();
            $workertab = "";
            $worker = $record->getWorker();
            $sno++;
            $wid = $worker->getId();
            $monthstr = $record->getMonth()->format("Y-m");
            $tmparr = self::getSalarytabsByWidMonthstr($wid, $monthstr, $inputbtn);
            $salarytabs[] = $tmparr;
        }

        return $salarytabs;
    }

    public static function getSalarytabsByWidMonthstr($wid, $monthstr, $inputbtn) {
        self::getRepos();

        $tmparr = array();
        $worker = self::$_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return;
        }

        $month = new Datetime($monthstr . "-01");
        $salaryrepo = self::$_salaryall;
        $salaryrecord = $salaryrepo->findOneBy(array("worker" => $worker, "month" => $month));

        $tab = self::generateWorkerTabBySalary($salaryrecord);
        $tmparr[] = $tab;

        $tab = infox_salary::generatePaymentTabByRecord($salaryrecord, $inputbtn);
        $tmparr[] = $tab;

        $attendance = self::$_siteatten->findOneBy(array("worker" => $worker, "month" => $month));
        $tab = infox_project::generateAttendanceTab2014($attendance, $monthstr, false);
        $tmparr[] = $tab;

        return $tmparr;
    }

    public static function generateWorkerTabBySalary($salaryrecord) {
        $sr = $salaryrecord;
        $worker = $sr->getWorker();
        $name = $worker->getNamechs();
        if (!$name || $name == "") {
            $name = $worker->getNameeng();
        }
        $sno = $wid = $worker->getId();
        $wpno = $worker->getWpno();
        $eeeno = $worker->getEeeno();
        $price = $worker->getCurrentrate();
        $type = $worker->getWorktype();

        $rate = $sr->getRate();
        $actualrate = ($rate != "" && $rate != 0) ? $rate : $price;

        $monthobj = $salaryrecord->getMonth();
        $monthstr = $monthobj->format("Y-m");
        $tab = '<table class="workerinfo">';
        $tab .= "<tr><th>年月</th><th>准证号</th><th>编号</th><th>姓名</th><th>单价</th><th>工种</th></tr>";
        $tab .= "<tr><td>$monthstr</td><td>$wpno</td><td>$eeeno</td>"
                . '<td><strong style="color:red;"> ' . $name . "</strong></td><td>$actualrate</td><td>$type</td></tr>";
        $tab .= "</table>";

        return $tab;
    }

    public static function generateWorkerSalaryTabs($salaryrecords, $inputbtn) {
        $salarytabs = array();
        $sno = 0;

        if (!count($salaryrecords)) {
            echo "No salary records in db, please check.";
            return;
        }
        //$monthstr = $salaryrecords[0]->getMonth()->format("Y-m");

        foreach ($salaryrecords as $record) {
            $tmparr = array();
            $workertab = "";
            $worker = $record->getWorker();
            $sno++;
            $wid = $worker->getId();
            $monthstr = $record->getMonth()->format("Y-m");
            $tmparr = self::getSalarytabsByWidMonthstr($wid, $monthstr, $inputbtn);
            $tmparr[] = self::generateSiteSalaryTabs($record);
            $salarytabs[] = $tmparr;
            //var_dump($tmparr1);
        }

        return $salarytabs;
    }

    public static function generateSiteSalaryTabs($record) {
        $sitedataarr = self::generateSiteData($record);
        $sitetab = self::generateTabsFromData($sitedataarr);
        return $sitetab;
    }

    private static function generateTabsFromData($sitedataarr) {
        /*
         * Array
          (
          [1] => Array
          (
          [0] => 9
          [1] => 470
          [2] => 27,28,29,30,1,2,3,4,5,
          )

          [2] => Array
          (
          [0] => 5
          [1] => 250
          [2] => 11,12,13,14,15,
          )

          )

         */

        self::getRepos();
        $rowhtml = "";
        
        foreach ($sitedataarr as $key => $tmp) {
            $attend = $tmp[0];
            $salary = $tmp[1];
            $daysstr = $tmp[2];
            $siteobj = self::$_site->findOneBy(array("id"=>$key));
            if(!$siteobj)
            {
                continue;
            }
            
            $sitename = $siteobj->getName();
            $rowhtml .= "<tr><td>$sitename</td><td>$attend</td><td>$salary</td><td>$daysstr</td></tr>";
        }
        
        $tabhtml = '<table class="workeronsite">';
        $tabhtml .= "<tr><th>工地</th><th>考勤</th><th>工资</th><th>出勤日期</th></tr>";
        $tabhtml .= $rowhtml;
        $tabhtml .= "</table>";
        
        return $tabhtml;
    }

    public static function generateSiteData($record) {
        $worker = $record->getWorker();
        $month = $record->getMonth();
        $attendance = self::$_siteatten->findOneBy(array("worker" => $worker, "month" => $month));
        $attendresult = infox_project::getAttendFoodData($attendance);

        $attenddata = $attendresult[0];
        //print_r($attenddata);
        // get rate
        $normalrate = self::getWorkerRate($record);
        $otrate = self::getWorkerOtRate($record);

        $sitedataarr = array();
        $lastsid = 0;
        for ($i = 26; $i <= 31; $i++) {
            $j = $i - 1;
            if (!key_exists($j, $attenddata)) {
                continue;
            }
            $value = $attenddata[$j];
            if ($value == "" || $value == ";;0") {
                continue;
            }
            //echo $value . "\n";

            $tmparr = explode(";", $value);
            $hoursdata = key_exists(0, $tmparr) ? $tmparr[0] : "";
            $piecedata = key_exists(1, $tmparr) ? $tmparr[1] : "";
            $sid = $tmparr[2];

            $lastsid = ($sid != 0) ? $sid : $lastsid;

            if (!key_exists($lastsid, $sitedataarr)) {
                $tmparr = array();
                $tmparr[0] = 0; // attendance
                $tmparr[1] = 0; // money
                $tmparr[2] = ""; // days, for example, 26, 27, 28,29, means working these days
                $sitedataarr[$lastsid] = $tmparr;
            }
            //$tmparr = $sitedataarr[$lastsid];
            // attendace and money
            if ($hoursdata != "") {
                $sitedataarr[$lastsid][0] += ($hoursdata >= 8) ? 1 : $hoursdata / 8;
                $normalhours = ($hoursdata > 8) ? 8 : $hoursdata;
                $othours = ($hoursdata > 8) ? ($hoursdata - 8) : 0;
                $daysalary = $normalhours * $normalrate + $othours * $otrate;
                $sitedataarr[$lastsid][1] += $daysalary;

                $sitedataarr[$lastsid][2] .= "$i,";
            } else if ($piecedata != "") {
                $sitedataarr[$lastsid][0] ++;
                $sitedataarr[$lastsid][1] += $piecedata;
                $sitedataarr[$lastsid][2] .= $i . ",";
            }
        }

        for ($i = 0; $i < 25; $i++) {
            $j = $i + 1;
            if (!key_exists($i, $attenddata)) {
                continue;
            }
            $value = $attenddata[$i];

            $tmparr = explode(";", $value);
            $hoursdata = key_exists(0, $tmparr) ? $tmparr[0] : "";
            $piecedata = key_exists(1, $tmparr) ? $tmparr[1] : "";
            //$value = $hoursdata != "" ? $hoursdata : $piecedata;
            $sid = $tmparr[2];

            $lastsid = ($sid != 0) ? $sid : $lastsid;

            if (!key_exists($lastsid, $sitedataarr)) {
                $tmparr = array();
                $tmparr[0] = 0; // attendance
                $tmparr[1] = 0; // money
                $tmparr[2] = ""; // days, for example, 26, 27, 28,29, means working these days
                $sitedataarr[$lastsid] = $tmparr;
            }

            // attendace and money and days string
            if ($hoursdata != "") {
                $sitedataarr[$lastsid][0] += ($hoursdata >= 8) ? 1 : $hoursdata / 8;
                $normalhours = ($hoursdata > 8) ? 8 : $hoursdata;
                $othours = ($hoursdata > 8) ? ($hoursdata - 8) : 0;
                $daysalary = $normalhours * $normalrate + $othours * $otrate;
                $sitedataarr[$lastsid][1] += $daysalary;
                $sitedataarr[$lastsid][2] .= "$j,";
            } else if ($piecedata != "") {
                $sitedataarr[$lastsid][0] ++;
                $sitedataarr[$lastsid][1] += $piecedata;
                $sitedataarr[$lastsid][2] .= $j . ",";
            }
        }

        ksort($sitedataarr);
        //print_r($sitedataarr);
        return $sitedataarr;
    }

}
