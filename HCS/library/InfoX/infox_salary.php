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
        $salaryrepos = self::getReposBySheet($sheet);
        $records = $salaryrepos->findBy(array("month" => $month));

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
            foreach ($result[0] as $tmp) {
                if ($tmp) {
                    $totaldays++;

                    // TODO: to confirm ...
                    // <=16, = hours; >16, = pay 
                    if ($tmp <= 16) {
                        $normalhours += ($tmp <= 8) ? $tmp : 8;
                        $othours += ($tmp > 8) ? ($tmp - 8) : 0;
                    } else {
                        $totalsalary += $tmp;
                    }
                }
            }

            $totalsalary += $normalhours * $rate + $othours * $otrate;

            // TODO: get basic salary from salary settings
            $normalsalary = ($totalsalary > 600) ? 600 : $totalsalary;
            $otsalary = $totalsalary - $normalsalary;

            $summary["totaldays"] = $totaldays;
            $summary["normalhours"] = $normalhours;
            $summary["normalsalary"] = $normalsalary;
            $summary["othours"] = $othours;
            $summary["otsalary"] = $otsalary;
            $summary["totalhours"] = $othours + $normalhours;
            $summary["totalsalary"] = $totalsalary;
            //$summary["fooddays"] = $fooddays;
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
        $normalhours = 0;
        $normalpay = 0;
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

        //echo "normalhours=$normalhours<br>";
        $normalpay = $currentrate * $normalhours;
        $otpay = $otrate * $othours;
        $allhours = $normalhours + $othours;
        $allpay = $normalpay + $otpay;

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
        $currentrate = self::getWorkerRate($salaryrecord); //$worker->getCurrentrate();
        $otrate = self::getWorkerOtRate($salaryrecord); //infox_worker::getWorkerOtRate($worker);

        $wid = $worker->getId();
        $monthobj = $salaryrecord->getMonth();
        $month = $monthobj->format("Y-m-d");
        ;
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
        if (array_key_exists(0, $result)) {
            foreach ($result[0] as $tmp) {
                if ($tmp) {
                    $totaldays++;

                    // normal work
                    $tmparr = explode(";", $tmp);
                    if (array_key_exists(0, $tmparr)) {
                        $daydata = $tmparr[0];
                        // this '20' needs to be confirmed
                        // > 20, this data is pay for whole day
                        // <=20, this is working hours
                        if ($daydata > 20) {
                            $daypay = $daydata;
                            //$normalhours += 8;
                            $normalpay += $daypay;
                            $salarybydaycount++;
                            $salarybyday += $daydata;
                        } else {
                            if ($daydata > 8) {
                                $normalhours += 8;
                                $othours += ($daydata - 8);
                            } else {
                                $normalhours += $daydata;
                            }
                        }
                    }

                    if (array_key_exists(1, $tmparr)) {
                        $food = $tmparr[1];
                        $fooddays += ($food === "1") ? 1 : 0;
                    }
                }
            }
        }
        //echo "normalhours=$normalhours<br>";
        $workersheet = $worker->getSheet();
        $basicsalary = 500;
        if ($workersheet == "HC.C" || $workersheet == "HT.C") {
            $basicsalary = self::getSettingBySectionName("salary", "cbasic");
        } else {
            $basicsalary = self::getSettingBySectionName("salary", "bbasic");
        }

        //$normalpay += $currentrate * $normalhours;        
        $normalpay = $basicsalary;
        //$otpay = $otrate * $othours;
        $otpay = $otrate * $othours - $basicsalary * $salarybydaycount / $daysinmonth + $salarybyday;
        $otpay = round($otpay, 2);
        $allhours = $normalhours + $othours;
        $allpay = $normalpay + $otpay;

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

        $otherfee = $salaryrecord->getOtherfee();
        $absencefines = $salaryrecord->getAbsencefines();
        $rtmonthpay = $salaryrecord->getRtmonthpay();
        $utfee = $salaryrecord->getUtfee();
        $utallowance = $salaryrecord->getUtallowance();
        $fullmonaward = $salaryrecord->getFullmonaward();

        $finalsalary = $normalpay + $otpay - abs($foodpay) - abs($absencefines) - abs($rtmonthpay) - abs($utfee) + abs($utallowance) + $otherfee + abs($fullmonaward);
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
        ;

        $normalhours = $record->getNormalhours();
        $normalpay = $record->getNormalpay();

        $othours = $record->getOthours();
        $otpay = $record->getOtpay();
        //$otprice = $record->getOtprice();
        $otprice = infox_salary::getWorkerOtRate($record);

        $allhours = $record->getAllhours();
        $allpay = $record->getAllpay();

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

        $fullmonaward = $record->getFullmonaward();

        $workersheet = $worker->getSheet();
        $basicsalary = 500;
        if ($workersheet == "HC.C" || $workersheet == "HT.C") {
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
        /*
          $tab .= "<tr><td colspan=2>正常工作</td><td colspan=3>加班工作</td><td colspan=2>总工作</td>"
          . "<td rowspan=2>考勤天数</td><td colspan=2>缺勤罚款</td><td rowspan=2>项目总工资</td>";
         */
        $tab .= "<tr><td colspan=2>正常工作</td><td colspan=3>加班工作</td><td colspan=2>总工作</td>"
                . "<td rowspan=2>考勤天数</td><td colspan=2>缺勤罚款</td>";
        $tab .="<td rowspan=2>满勤奖</td>";
        $tab .= "<td colspan=2>伙食费</td><td colspan=3>预扣税</td><td colspan=2>水电费</td>"
                . "<td rowspan=2>其他补扣</td><td rowspan=2>提前结帐</td><td rowspan=2>当月净工资</td>";

        if ($inputbtn) {
            $url = "/salary/salary/datainput?wid=$wid&month=$monthstr";
            $tab .= '<td rowspan=3><a href="' . $url . '" data-rel="dialog" data-role="button" data-mini="true" data-theme="b">输入</a></td>';
        }
        $tab .= '</tr>';
        $tab .= "<tr><td>小时</td><td>金额</td><td>单价</td><td>小时</td><td>金额</td><td>小时</td>"
                . "<td>金额</td><td>天数</td><td>金额</td>";

        $tab .= "<td>天数</td><td>金额</td>";
        $tab .= "<td>当月</td><td>月数</td><td>累计</td>";
        $tab .= "<td>扣款</td><td>补助</td>";
        $tab .= "</tr>";

        // insert value
        $tdclass = 'class="workerpay"';
        $tab .= "<tr><td $tdclass>$normalhours</td><td $tdclass>$normalpay</td>";
        $tab .= "<td $tdclass>$otprice</td><td $tdclass>$othours</td><td $tdclass>$otpay</td>";
        $tab .= "<td $tdclass>$allhours</td><td $tdclass>$allpay</td><td $tdclass>$attenddays</td>";
        $tab .= "<td $tdclass>$absencedays</td><td $tdclass>$absencefines</td>";
        $tab .= "<td $tdclass>$fullmonaward</td>";
        //$tab .= "<td>$projectpay</td>";
        $tab .= "<td $tdclass>$fooddays</td><td $tdclass>$foodpay</td>";
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
        /*
         *         
          $summary["totaldays"] = "";
          $summary["normalhours"] = "";
          $summary["normalsalary"] = "";
          $summary["othours"] = "";
          $summary["otsalary"] = "";
          $summary["totalhours"] = "";
          $summary["totalsalary"] = "";
          $summary["fooddays"] = "";

         */
        $salaryrecord->setAttenddays($summary["totaldays"]);
        $salaryrecord->setNormalhours($summary['normalhours']);
        $salaryrecord->setNormalsalary($summary["normalsalary"]);
        $salaryrecord->setOthours($summary['othours']);
        $salaryrecord->setOtsalary($summary['otsalary']);
        $salaryrecord->setTotalhours($summary['totalhours']);
        $salaryrecord->setTotalsalary($summary["totalsalary"]);

        self::$_em->persist($salaryrecord);
        self::$_em->flush();
    }

    public static function getWorkerSummaryTab($workerobj, $dateobj) {
        self::getRepos();
        
        $workerid = $workerobj->getId();
        $wpno = $workerobj->getWpno();
        $name = $workerobj->getNamechs();
        if (!$name || $name == "") {
            $name = $workerobj->getNameeng();
        }
        $worktype = $workerobj->getWorktype();
        
        $salaryrecord = self::$_salaryall->findOneBy(array("worker" => $workerobj, "month" => $dateobj));        
        $summary = array();
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
        
        $sno = "";
        $fooddays = "";
        
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

        $tr = "<tr><td>$sno</td><td>$wpno</td><td>$name</td><td>$rate</td><td>$worktype</td>";
        $tr .= "<td>$normalhours</td><td>$normalsalary</td><td>$otrate</td><td>$othours</td><td>$otsalary</td><td>$totalhours</td>";
        $tr .= "<td>$totalsalary</td><td>$totaldays</td><td></td><td></td><td></td><td>$fooddays</td><td></td>";
        $tr .= "</tr>";
        
        $table .= $tr;
        $table .= "</table>";
        return $table;
    }

}
