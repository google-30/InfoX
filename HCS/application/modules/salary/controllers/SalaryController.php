<?php

include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';
include 'InfoX/infox_salary.php';

class Salary_SalaryController extends Zend_Controller_Action {

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
        $this->_siteattendance = $this->_em->getRepository('Synrgic\Infox\Siteattendance');
        $this->_salaryall = $this->_em->getRepository('Synrgic\Infox\Workersalaryall');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_salarysummary = $this->_em->getRepository('Synrgic\Infox\Salarysummary');
        $this->_summarydetails = $this->_em->getRepository('Synrgic\Infox\Salarysummarydetails');
        $this->_summarybysite = $this->_em->getRepository('Synrgic\Infox\Salarysummarybysite');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
        $this->_setting = $this->_em->getRepository('Synrgic\Infox\Setting');
    }

    public function indexAction() {
        $sheetarr = infox_worker::getSheetarr();
        $sheetarr[] = "ALL";
        $this->view->sheetarr = $sheetarr;
        $this->view->sheet = $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        //$this->view->workerarr = infox_worker::getworkerlistbysheet($requestsheet);
        // retrieve salary summary
    }

    public function personalAction() {
        infox_common::turnoffLayout($this->_helper);

        $sheetarr = infox_worker::getSheetarr();
        $this->view->sheetarr = $sheetarr;

        $wid_req = $this->getParam("id", 0);
        $workerobj = null;
        $workerarr = array();

        if ($wid_req == 0) { // change sheet
            $sheet_req = $this->getParam("sheet", "HC.C");
        } else {
            $workerobj = $this->_workerdetails->findOneBy(array('id' => $wid_req));
            if (!$workerobj) {
                return;
            }

            $sheet_req = $workerobj->getSheet();
        }

        $salaryrecords = $this->_salaryall->findAll();
        if (!count($salaryrecords)) {
            return;
        }

        foreach ($salaryrecords as $tmp) {
            $workertmp = $tmp->getWorker();
            $sheettmp = $workertmp->getSheet();
            $widtmp = $workertmp->getId();
            if ($sheettmp == $sheet_req) {
                if (!key_exists($widtmp, $workerarr)) {
                    $workerarr[$widtmp] = $workertmp;
                }
            }
        }

        if ($wid_req == 0) { // change sheet
            $workerobj = reset($workerarr);
        }

        $year_req = $this->getParam("year", "all");

        $records = $this->_salaryall->findBy(array('worker' => $workerobj));

        $recordsbyyear = array();
        $yearsarr = array();

        foreach ($records as $tmp) {
            $date = $tmp->getMonth();
            $year = $date->format("Y");
            if (!in_array($year, $yearsarr)) {
                $yearsarr[] = $year;
            }

            if ($year == $year_req) {
                $recordsbyyear[] = $tmp;
            }

            $worker = $tmp->getWorker();
            $wid = $worker->getId();
            if (!key_exists($wid, $workerarr)) {
                $workerarr[$wid] = $worker;
            }
        }

        sort($yearsarr);
        $this->view->worker = $workerobj;
        $this->view->yearsarr = $yearsarr;
        $recordsall = ($year_req == "all") ? $records : $recordsbyyear;
        $salaryall = 0;
        foreach ($recordsall as $record) {
            $salary = $record->getSalary();
            $salaryall += $salary;
        }
        $this->view->salaryall = $salaryall;
        setlocale(LC_MONETARY, 'en_US');
        $salaryallformat = money_format('%i', $salaryall);
        $this->view->salaryallformat = $salaryallformat;
        $this->view->recordsbyyear = $recordsall;
        $this->view->workerarr = $workerarr;

        $salarytabs = $this->generateSalaryTabs($recordsall, false);
        $this->view->salarytabs = $salarytabs;

        $options = '';
        foreach ($sheetarr as $tmp) {
            if ($tmp == $sheet_req) {
                $option = "<option value=$tmp selected>" . $tmp . "</option>";
            } else {
                $option = "<option value=$tmp>" . $tmp . "</option>";
            }
            $options .= $option;
        }
        $sheetsel = '<select id="sheetsel" data-theme="b" data-mini="true">' . $options . "</select>";

        $options = '';
        foreach ($workerarr as $tmp) {
            $wid = $tmp->getId();
            $name = ($tmp->getNamechs() == "" || !$tmp->getNamechs()) ? $tmp->getNameeng : $tmp->getNamechs();

            if ($workerobj->getId() == $wid) {
                $option = "<option value=$wid selected>" . $name . "</option>";
            } else {
                $option = "<option value=$wid>" . $name . "</option>";
            }
            $options .= $option;
        }
        $workersel = '<select id="workersel" data-theme="b" data-mini="true">'
                . '<option value=0>&nbsp;</option>' . $options . "</select>";

        $options = '<option value="all">All</options>';
        foreach ($yearsarr as $year) {
            if ($year_req == $year) {
                $option = "<option value=$year selected>" . $year . "</option>";
            } else {
                $option = "<option value=$year>" . $year . "</option>";
            }
            $options .= $option;
        }
        $yearsel = '<select id="yearsel" data-theme="b" data-mini="true">' . $options . "</select>";

        $this->view->sheetsel = $sheetsel;
        $this->view->workersel = $workersel;
        $this->view->yearsel = $yearsel;
    }

    public function salarybymonthAction() {
        infox_common::turnoffLayout($this->_helper);
        $error = "";

        $sheet = $this->getParam("sheet", "HC.C");
        $this->view->sheet = $sheet;
        $sheetarr = infox_worker::getSheetarr();
        $sheetarr[] = "ALL";
        $this->view->sheetarr = $sheetarr;

        // TODO: may cause datetime issue here
        $monthstr = $this->getParam("month", "now");
        if ($monthstr == "now") {
            $error .= "salarybymonthAction:monthstr error.";
            $this->view->error = $error;
            return;
        }
        $month = new Datetime($monthstr . "-01");
        $this->view->monthstr = $monthstr;

        // get all records in this month        
        $salaryrecords = infox_salary::getSalaryRecordsByMonthSheet($month, $sheet);
        $inputbtn = true;
        $salarytabs = $this->generateSalaryTabs($salaryrecords, $inputbtn);
        $this->view->salarytabs = $salarytabs;
        $this->view->salaryrecords = $salaryrecords;
        $this->view->username = infox_common::getUsername();
    }

    private function generateSalaryTabs($salaryrecords, $inputbtn) {
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
            $tmparr = $this->getSalarytabsByWidMonthstr($wid, $monthstr, $inputbtn);
            $salarytabs[] = $tmparr;
        }

        return $salarytabs;
    }

    private function generateWorkerTab($worker, $sno) {
        $name = $worker->getNamechs();
        if (!$name || $name == "") {
            $name = $worker->getNameeng();
        }

        $wpno = $worker->getWpno();
        $eeeno = $worker->getEeeno();
        $price = $worker->getCurrentrate();
        $type = $worker->getWorktype();

        $tab = '<table class="workerinfo">';
        $tab .= "<tr><th rowspan=1>序号</th><th>准证号</th><th>编号</th><th>姓名</th><th>单价</th><th>工种</th></tr>";
        $tab .= "<tr><td>$sno</td><td>$wpno</td><td>$eeeno</td><td>$name</td><td>$price</td><td>$type</td></tr>";
        $tab .= "</table>";

        return $tab;
    }

    private function generateWorkerTabBySalary($salaryrecord) {
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

    private function generatePaymentTab($record) {
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

        $tab = "<table>";
        /*
          $tab .= "<tr><td colspan=2>正常工作</td><td colspan=3>加班工作</td><td colspan=2>总工作</td>"
          . "<td rowspan=2>考勤天数</td><td colspan=2>缺勤罚款</td><td rowspan=2>项目总工资</td>";
         */
        $tab .= "<tr><td colspan=2>正常工作</td><td colspan=3>加班工作</td><td colspan=2>总工作</td>"
                . "<td rowspan=2>考勤天数</td><td colspan=2>缺勤罚款</td>";

        $tab .= "<td colspan=2>伙食费</td><td colspan=3>预扣税</td><td colspan=2>水电费</td>"
                . "<td rowspan=2>其他补扣</td><td rowspan=2>提前结帐</td><td rowspan=2>当月净工资</td>";

        $url = "/worker/salary/datainput?wid=$wid&month=$monthstr";
        $tab .= '<td rowspan=3><a href="' . $url . '" data-rel="dialog" data-role="button" data-mini="true" data-theme="b">输入</a></td>';
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
        //$tab .= "<td>$projectpay</td>";
        $tab .= "<td $tdclass>$fooddays</td><td $tdclass>$foodpay</td>";
        $tab .= "<td $tdclass>$rtmonthpay</td><td $tdclass>$rtmonths</td><td $tdclass>$rtall</td>";
        $tab .= "<td $tdclass>$utfee</td><td $tdclass>$utallowance</td>";
        $tab .= "<td $tdclass>$otherfee</td><td $tdclass>$inadvance</td><td $tdclass>$salary</td>";
        $tab .= "</tr>";

        $tab .= "</table>";
        return $tab;
    }

    public function gensalaryrecordsAction() {
        infox_common::turnoffView($this->_helper);
        $sheet = $this->getParam("sheet", "HC.C");
        $month = $this->getParam("month", "");
        $date = new Datetime($month . "01");
        $monthstr = $date->format("Y-m");

        infox_worker::createSalaryRecordsByMonthSheet($monthstr, $sheet);
    }

    public function datainputAction() {
        infox_common::turnoffLayout($this->_helper);

        $wid = $this->getParam("wid", 0);
        $monthstr = $this->getParam("month", "");
        $monthobj = new Datetime($monthstr . "-01");

        $tmparr = array();
        $worker = $this->_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return;
        }

        $this->getSalarytabs();
        $this->view->worker = $worker;
        //$this->view->month = $month;

        $salaryrecord = $this->_salaryall->findOneBy(array("worker" => $worker, "month" => $monthobj));
        $this->view->salaryrecord = $salaryrecord;
    }

    public function datapostAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $rate = trim($this->getParam("rate", ""));
        $otherfee = trim($this->getParam("otherfee", ""));
        $inadvance = trim($this->getParam("inadvance", ""));
        $absencedays = trim($this->getParam("absencedays", ""));
        $absencefines = trim($this->getParam("absencefines", ""));
        $rtmonthpay = trim($this->getParam("rtmonthpay", ""));
        $rtmonths = trim($this->getParam("rtmonths", ""));
        $rtall = trim($this->getParam("rtall", ""));
        $utfee = trim($this->getParam("utfee", ""));
        $utallowance = trim($this->getParam("utallowance", ""));
        $fullmonaward = trim($this->getParam("fullmonaward", ""));
        $foodpay = trim($this->getParam("foodpay", ""));
        $remark = trim($this->getParam("remark", ""));

        $wid = $this->getParam("wid", "");
        $month = $this->getParam("month", "");

        $worker = $this->_workerdetails->findOneBy(array('id' => $wid));
        if (!$worker) {
            echo "no worker found for these data post";
            return;
        }
        $salaryrepo = $this->_salaryall; //infox_worker::getSalaryRepoByWorker($worker);
        $monthobj = new Datetime("$month-01");
        $salaryrecord = $salaryrepo->findOneBy(array("worker" => $worker, "month" => $monthobj));
        if (!$salaryrecord) {
            echo "no record found for these data post";
            return;
        }

        $sr = $salaryrecord;
        $rate = ($rate != "") ? abs(floatval($rate)) : 0;

        $sr->setRate((float) $rate);

        $otherfee = ($otherfee != "") ? floatval($otherfee) : 0;
        $sr->setOtherfee((float) $otherfee);

        $inadvance = ($inadvance != "") ? "-" . abs(floatval($inadvance)) : 0;
        $sr->setinadvance((float) $inadvance);

        $otherfee = ($absencedays != "") ? floatval($absencedays) : 0;
        $sr->setAbsencedays((float) $absencedays);

        $absencefines = ($absencefines != "") ? "-" . abs(floatval($absencefines)) : 0;
        $sr->setAbsencefines((float) $absencefines);

        $rtmonthpay = ($rtmonthpay != "") ? "-" . abs(floatval($rtmonthpay)) : 0;
        $sr->setRtmonthpay((float) $rtmonthpay);

        $sr->setRtmonths((int) $rtmonths);
        $sr->setRtall((float) $rtall);

        $utfee = ($utfee != "") ? "-" . abs(floatval($utfee)) : 0;
        $sr->setUtfee((float) $utfee);

        $utallowance = ($utallowance != "") ? abs(floatval($utallowance)) : 0;
        $sr->setUtallowance((float) $utallowance);

        $fullmonaward = ($fullmonaward != "") ? abs(floatval($fullmonaward)) : 0;
        $sr->setFullmonaward($fullmonaward);

        $sr->setFoodpay((float) abs($foodpay));
        $sr->setRemark($remark);

        $this->_em->persist($sr);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        infox_salary::updateOneSalaryRecord($salaryrecord);
        infox_salary::updateSalarySummaryBySalaryRecord($salaryrecord);

        echo "提交成功";
    }

    public function salarysheetAction() {
        infox_common::turnoffLayout($this->_helper);

        $wid = $this->getParam("wid", 0);
        $monthstr = $this->getParam("month", "");
        $worker = $this->_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return;
        }
        $month = new Datetime($monthstr . "-01");
        //$salaryrepo = infox_worker::getSalaryRepoByWorker($worker);
        //$salaryrecord = $salaryrepo->findOneBy(array("worker"=>$worker, "month"=>$month));
        //infox_salary::updateOneSalaryRecord($salaryrecord);

        $tabarr = $this->getSalarytabs();
        //echo $tabarr;
        $this->alltabs = $tabarr;
    }

    private function getSalarytabsByWidMonthstr($wid, $monthstr, $inputbtn) {
        $tmparr = array();
        $worker = $this->_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return;
        }

        $month = new Datetime($monthstr . "-01");
        $salaryrepo = $this->_salaryall; //infox_worker::getSalaryRepoByWorker($worker);
        $salaryrecord = $salaryrepo->findOneBy(array("worker" => $worker, "month" => $month));

        $tab = $this->generateWorkerTabBySalary($salaryrecord);
        $tmparr[] = $tab;

        $tab = infox_salary::generatePaymentTabByRecord($salaryrecord, $inputbtn);
        $tmparr[] = $tab;

        $attendance = $this->_siteattendance->findOneBy(array("worker" => $worker, "month" => $month));
        $tab = infox_project::generateAttendanceTab2014($attendance, $monthstr, false);
        $tmparr[] = $tab;

        return $tmparr;
    }

    private function getSalarytabs() {
        $wid = $this->getParam("wid", 0);
        $monthstr = $this->getParam("month", "");

        $worker = $this->_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return;
        }

        $month = new Datetime($monthstr . "-01");

        if (1) {
            $tmparr = $this->getAllTabsByWorkerMonth($worker, $month);
        } else {
            $salaryrepo = infox_worker::getSalaryRepoByWorker($worker);
            $salaryrecord = $salaryrepo->findOneBy(array("worker" => $worker, "month" => $month));

            $tab = $this->generateWorkerTabBySalary($salaryrecord);
            $tmparr[] = $tab;

            $tab = infox_salary::generatePaymentTabByRecord($salaryrecord, false);
            $tmparr[] = $tab;

            $attendance = $this->_siteattendance->findOneBy(array("worker" => $worker, "month" => $month));
            $tab = infox_project::generateAttendanceTab2014($attendance, $monthstr, false);
            $tmparr[] = $tab;
        }

        $this->view->alltabs = $tmparr;
        $this->view->month = $month;
        return $tmparr;
    }

    private function getAllTabsByWorkerMonth($worker, $month) {
        $salaryrepo = $this->_salaryall;
        $salaryrecord = $salaryrepo->findOneBy(array("worker" => $worker, "month" => $month));

        $tmparr = array();

        $tab = $this->generateWorkerTabBySalary($salaryrecord);
        $tmparr[] = $tab;

        $tab = infox_salary::generatePaymentTabByRecord($salaryrecord, false);
        $tmparr[] = $tab;

        $attendance = $this->_siteattendance->findOneBy(array("worker" => $worker, "month" => $month));
        $tab = infox_project::generateAttendanceTab2014($attendance, $month->format("Y-m"), false);
        $tmparr[] = $tab;

        return $tmparr;
    }

    public function salaryreceiptsAction() {
        infox_common::turnoffLayout($this->_helper);

        $monthstr = $this->getParam("month", "now");
        if ("now" == $monthstr) {
            return;
        }
        $monthobj = new Datetime($monthstr . "-01");

        $sheet = $this->getParam("sheet", "HC.C");
        //$this->view->sheet = $sheet;
        //$this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $salaryrepo = $this->_salaryall; //infox_salary::getReposBySheet($sheet);
        $records = $salaryrepo->findBy(array("month" => $monthobj));

        if ($sheet = "ALL") {
            $salaryrecords = $records;
        } else {
            $salaryrecords = array();
            foreach ($records as $tmp) {
                $worker = $tmp->getWorker();
                $eeeno = $worker->getEeeno();
                if ($sheet == $worker->getSheet()) {
                    //$salaryrecords[] = $tmp;
                    $salaryrecords[$eeeno] = $tmp;
                }
            }
        }
        ksort($salaryrecords);

        $this->view->salaryrecords = $salaryrecords;
        $this->view->monthobj = $monthobj;

        $tmparr = explode(".", $sheet);
        $sheetprx = $tmparr[0];
        $cmyobj = $this->_companyinfo->findOneBy(array("sheetprx" => $sheetprx));
        $this->view->company = $cmyobj;

        $defaultdate = new Datetime("now");
        $defaultdatestr = $defaultdate->format("d/m/Y");

        $date = $this->getParam("date", "");
        if ($date == "") {
            $receiptdate = $defaultdatestr;
            $receiptdateobj = $defaultdate;
        } else {
            $dateobj = new Datetime($date);
            $receiptdate = $dateobj->format("d/m/Y");
            $receiptdateobj = $dateobj;
        }
        $this->view->receiptdate = $receiptdate;

        // store receipt date
        $receiptobj = $this->_salarysummary->findOneBy(array("month" => $monthobj));
        if (!$receiptobj) {
            $receiptobj = new \Synrgic\Infox\Salarysummary();
        }
        $receiptobj->setDate($receiptdateobj);
        $receiptobj->setMonth($monthobj);
        $this->_em->persist($receiptobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    public function salaryreceiptsbyworkerAction() {
        infox_common::turnoffLayout($this->_helper);

        $wid_req = $this->getParam("id", 0);
        $workerobj = $this->_workerdetails->findOneBy(array('id' => $wid_req));
        if (!$workerobj) {
            return;
        }
        $sheet = $workerobj->getSheet();

        $salaryrecords = $this->_salaryall->findBy(array("worker" => $workerobj));
        if (!count($salaryrecords)) {
            return;
        }
        $year_req = $this->getParam("year", "all");

        $workerrecords = array();
        if ($year_req == "all") {
            $workerrecords = $salaryrecords;
        } else {
            foreach ($salaryrecords as $tmp) {
                $workertmp = $tmp->getWorker();
                $monthobj = $tmp->getMonth();
                $year = $monthobj->format("Y");
                if ($year == $year_req) {
                    $workerrecords[] = $tmp;
                }
            }
        }

        $this->view->worker = $workerobj;

        $tmparr = explode(".", $sheet);
        $sheetprx = $tmparr[0];
        $cmyobj = $this->_companyinfo->findOneBy(array("sheetprx" => $sheetprx));
        $this->view->company = $cmyobj;

        $workerrecordswdate = array();
        foreach ($workerrecords as $tmp) {
            $monthobj = $tmp->getMonth();
            $receiptobj = $this->_salarysummary->findOneBy(array("month" => $monthobj));
            $receiptdate = $receiptobj ? $receiptobj->getDate() : null;
            $tmparr = array();
            $tmparr["receiptdate"] = $receiptdate;
            $tmparr["record"] = $tmp;

            $workerrecordswdate[] = $tmparr;
        }

        usort($workerrecordswdate, $this->sortWorkerrecordswdate);
        $this->view->salaryrecords = $workerrecordswdate;
    }

    private function sortWorkerrecordswdate($a, $b) {
        return strcmp($a['record']->getMonth()->format("Y-m-d"), $b['record']->getMonth()->format("Y-m-d"));
    }

    public function summaryAction() {
        infox_common::turnoffLayout($this->_helper);

        $monthstr = $this->getParam("month", "");

        if ($monthstr == "") {
            // default month should be last one
            $monthstr = $lastmonth = date('Y-m', strtotime("last month"));
            //echo $lastmonth;
        }
        $monthobj = new DateTime($monthstr . "-01");

        // sheets
        $sheets = infox_worker::getSheetarr();

        // find and create records
        $summaryrecords = array();
        foreach ($sheets as $tmp) {
            $record = $this->_summarydetails->findOneBy(array("month" => $monthobj, "sheet" => $tmp));
            if (!$record) {// create
                $record = new \Synrgic\Infox\Salarysummarydetails();
            }

            $record->setMonth($monthobj);
            $record->setSheet($tmp);
            $this->_em->persist($record);

            $summaryrecords[$tmp] = $record;
        }
        $this->_em->flush();

        $allsalaryrecords = $this->_salaryall->findBy(array("month" => $monthobj));

        foreach ($summaryrecords as $key => $value) {
            $normalhours = 0;
            $normalsalary = 0;
            $othours = 0;
            $otsalary = 0;
            $totalhours = 0;
            $piecesalary = 0;
            $totalsalary = 0;
            $attenddays = 0;
            $absencefines = 0;
            $foodpay = 0;
            $rtmonthpay = 0;
            $utfee = 0;
            $utallowance = 0;
            $otherfee = 0;
            $inadvance = 0;
            $fullmonaward = 0;
            $salary = 0;

            foreach ($allsalaryrecords as $record) {
                $worker = $record->getWorker();
                $workersheet = $worker->getSheet();
                if ($workersheet == $key) {
                    $normalhours += $record->getNormalhours();
                    $normalsalary += $record->getNormalsalary();
                    $othours += $record->getOthours();
                    $otsalary += $record->getOtsalary();
                    $totalhours += $record->getTotalhours();
                    $piecesalary += $record->getPiecesalary();
                    $totalsalary += $record->getTotalsalary();
                    $attenddays += $record->getAttenddays();
                    $absencefines += $record->getAbsencefines();
                    $foodpay += $record->getFoodpay();
                    $rtmonthpay += $record->getRtmonthpay();
                    $utfee += $record->getUtfee();
                    $utallowance += $record->getUtallowance();
                    $otherfee += $record->getOtherfee();
                    $inadvance += $record->getInadvance();
                    $fullmonaward += $record->getFullmonaward();
                    $salary += $record->getSalary();
                }
            }

            $value->setNormalhours($normalhours);
            $value->setNormalsalary($normalsalary);
            $value->setOthours($othours);
            $value->setOtsalary($otsalary);
            $value->setTotalhours($totalhours);
            $value->setPiecesalary($piecesalary);
            $value->setTotalsalary($totalsalary);
            $value->setAttenddays($attenddays);
            $value->setAbsencefines($absencefines);
            $value->setFoodpay($foodpay);
            $value->setRtmonthpay($rtmonthpay);
            $value->setUtfee($utfee);
            $value->setUtallowance($utallowance);
            $value->setOtherfee($otherfee);
            $value->setInadvance($inadvance);
            $value->setFullmonaward($fullmonaward);
            $value->setSalary($salary);

            $this->_em->persist($value);
        }

        $this->_em->flush();

        // store hc, ht, all

        $record = $this->_summarydetails->findOneBy(array("month" => $monthobj, "sheet" => "HC"));
        if (!$record) {
            $record = new \Synrgic\Infox\Salarysummarydetails();
        }
        $normalhours = $summaryrecords["HC.C"]->getNormalhours() + $summaryrecords["HC.B"]->getNormalhours();
        $record->setNormalhours($normalhours);
        $normalsalary = $summaryrecords["HC.C"]->getNormalsalary() + $summaryrecords["HC.B"]->getNormalsalary();
        $record->setNormalsalary($normalsalary);
        $othours = $summaryrecords["HC.C"]->getOthours() + $summaryrecords["HC.B"]->getOthours();
        $record->setOthours($othours);
        $otsalary = $summaryrecords["HC.C"]->getOtsalary() + $summaryrecords["HC.B"]->getOtsalary();
        $record->setOtsalary($otsalary);
        $totalhours = $summaryrecords["HC.C"]->getTotalhours() + $summaryrecords["HC.B"]->getTotalhours();
        $record->setTotalhours($totalhours);

        $piecesalary = $summaryrecords["HC.C"]->getPiecesalary() + $summaryrecords["HC.B"]->getPiecesalary();
        $record->setPiecesalary($piecesalary);

        $totalsalary = $summaryrecords["HC.C"]->getTotalsalary() + $summaryrecords["HC.B"]->getTotalsalary();
        $record->setTotalsalary($totalsalary);

        $attenddays = $summaryrecords["HC.C"]->getAttenddays() + $summaryrecords["HC.B"]->getAttenddays();
        $record->setAttenddays($attenddays);

        $absencefines = $summaryrecords["HC.C"]->getAbsencefines() + $summaryrecords["HC.B"]->getAbsencefines();
        $record->setAbsencefines($absencefines);

        $foodpay = $summaryrecords["HC.C"]->getFoodpay() + $summaryrecords["HC.B"]->getFoodpay();
        $record->setFoodpay($foodpay);

        $rtmonthpay = $summaryrecords["HC.C"]->getRtmonthpay() + $summaryrecords["HC.B"]->getRtmonthpay();
        $record->setRtmonthpay($rtmonthpay);

        $utfee = $summaryrecords["HC.C"]->getUtfee() + $summaryrecords["HC.B"]->getUtfee();
        $record->setUtfee($utfee);

        $utallowance = $summaryrecords["HC.C"]->getUtallowance() + $summaryrecords["HC.B"]->getUtallowance();
        $record->setUtallowance($utallowance);

        $otherfee = $summaryrecords["HC.C"]->getOtherfee() + $summaryrecords["HC.B"]->getOtherfee();
        $record->setOtherfee($otherfee);

        $inadvance = $summaryrecords["HC.C"]->getInadvance() + $summaryrecords["HC.B"]->getInadvance();
        $record->setInadvance($inadvance);

        $fullmonaward = $summaryrecords["HC.C"]->getFullmonaward() + $summaryrecords["HC.B"]->getFullmonaward();
        $record->setFullmonaward($fullmonaward);

        $salary = $summaryrecords["HC.C"]->getSalary() + $summaryrecords["HC.B"]->getSalary();
        $record->setSalary($salary);

        $record->setMonth($monthobj);
        $record->setSheet("HC");
        $this->_em->persist($record);
        $summaryrecords["HC"] = $record;
        // HT
        $record = $this->_summarydetails->findOneBy(array("month" => $monthobj, "sheet" => "HT"));
        if (!$record) {
            $record = new \Synrgic\Infox\Salarysummarydetails();
        }
        $normalhours = $summaryrecords["HT.C"]->getNormalhours() + $summaryrecords["HT.B"]->getNormalhours();
        $record->setNormalhours($normalhours);
        $normalsalary = $summaryrecords["HT.C"]->getNormalsalary() + $summaryrecords["HT.B"]->getNormalsalary();
        $record->setNormalsalary($normalsalary);
        $othours = $summaryrecords["HT.C"]->getOthours() + $summaryrecords["HT.B"]->getOthours();
        $record->setOthours($othours);
        $otsalary = $summaryrecords["HT.C"]->getOtsalary() + $summaryrecords["HT.B"]->getOtsalary();
        $record->setOtsalary($otsalary);
        $totalhours = $summaryrecords["HT.C"]->getTotalhours() + $summaryrecords["HT.B"]->getTotalhours();
        $record->setTotalhours($totalhours);
        $piecesalary = $summaryrecords["HT.C"]->getPiecesalary() + $summaryrecords["HT.B"]->getPiecesalary();
        $record->setPiecesalary($piecesalary);
        $totalsalary = $summaryrecords["HT.C"]->getTotalsalary() + $summaryrecords["HT.B"]->getTotalsalary();
        $record->setTotalsalary($totalsalary);
        $attenddays = $summaryrecords["HT.C"]->getAttenddays() + $summaryrecords["HT.B"]->getAttenddays();
        $record->setAttenddays($attenddays);
        $absencefines = $summaryrecords["HT.C"]->getAbsencefines() + $summaryrecords["HT.B"]->getAbsencefines();
        $record->setAbsencefines($absencefines);
        $foodpay = $summaryrecords["HT.C"]->getFoodpay() + $summaryrecords["HT.B"]->getFoodpay();
        $record->setFoodpay($foodpay);
        $rtmonthpay = $summaryrecords["HT.C"]->getRtmonthpay() + $summaryrecords["HT.B"]->getRtmonthpay();
        $record->setRtmonthpay($rtmonthpay);
        $utfee = $summaryrecords["HT.C"]->getUtfee() + $summaryrecords["HT.B"]->getUtfee();
        $record->setUtfee($utfee);
        $utallowance = $summaryrecords["HT.C"]->getUtallowance() + $summaryrecords["HT.B"]->getUtallowance();
        $record->setUtallowance($utallowance);
        $otherfee = $summaryrecords["HT.C"]->getOtherfee() + $summaryrecords["HT.B"]->getOtherfee();
        $record->setOtherfee($otherfee);
        $inadvance = $summaryrecords["HT.C"]->getInadvance() + $summaryrecords["HT.B"]->getInadvance();
        $record->setInadvance($inadvance);
        $fullmonaward = $summaryrecords["HT.C"]->getFullmonaward() + $summaryrecords["HT.B"]->getFullmonaward();
        $record->setFullmonaward($fullmonaward);
        $salary = $summaryrecords["HT.C"]->getSalary() + $summaryrecords["HT.B"]->getSalary();
        $record->setSalary($salary);

        $record->setMonth($monthobj);
        $record->setSheet("HT");
        $this->_em->persist($record);
        $summaryrecords["HT"] = $record;

        // others
        $record = $this->_summarydetails->findOneBy(array("month" => $monthobj, "sheet" => "Others"));
        if (!$record) {
            $record = new \Synrgic\Infox\Salarysummarydetails();
        }
        $normalhours = $summaryrecords["Others.C"]->getNormalhours() + $summaryrecords["Others.B"]->getNormalhours();
        $record->setNormalhours($normalhours);
        $normalsalary = $summaryrecords["Others.C"]->getNormalsalary() + $summaryrecords["Others.B"]->getNormalsalary();
        $record->setNormalsalary($normalsalary);
        $othours = $summaryrecords["Others.C"]->getOthours() + $summaryrecords["Others.B"]->getOthours();
        $record->setOthours($othours);
        $otsalary = $summaryrecords["Others.C"]->getOtsalary() + $summaryrecords["Others.B"]->getOtsalary();
        $record->setOtsalary($otsalary);
        $totalhours = $summaryrecords["Others.C"]->getTotalhours() + $summaryrecords["Others.B"]->getTotalhours();
        $record->setTotalhours($totalhours);
        $piecesalary = $summaryrecords["Others.C"]->getPiecesalary() + $summaryrecords["Others.B"]->getPiecesalary();
        $record->setPiecesalary($piecesalary);
        $totalsalary = $summaryrecords["Others.C"]->getTotalsalary() + $summaryrecords["Others.B"]->getTotalsalary();
        $record->setTotalsalary($totalsalary);
        $attenddays = $summaryrecords["Others.C"]->getAttenddays() + $summaryrecords["Others.B"]->getAttenddays();
        $record->setAttenddays($attenddays);
        $absencefines = $summaryrecords["Others.C"]->getAbsencefines() + $summaryrecords["Others.B"]->getAbsencefines();
        $record->setAbsencefines($absencefines);
        $foodpay = $summaryrecords["Others.C"]->getFoodpay() + $summaryrecords["Others.B"]->getFoodpay();
        $record->setFoodpay($foodpay);
        $rtmonthpay = $summaryrecords["Others.C"]->getRtmonthpay() + $summaryrecords["Others.B"]->getRtmonthpay();
        $record->setRtmonthpay($rtmonthpay);
        $utfee = $summaryrecords["Others.C"]->getUtfee() + $summaryrecords["Others.B"]->getUtfee();
        $record->setUtfee($utfee);
        $utallowance = $summaryrecords["Others.C"]->getUtallowance() + $summaryrecords["Others.B"]->getUtallowance();
        $record->setUtallowance($utallowance);
        $otherfee = $summaryrecords["Others.C"]->getOtherfee() + $summaryrecords["Others.B"]->getOtherfee();
        $record->setOtherfee($otherfee);
        $inadvance = $summaryrecords["Others.C"]->getInadvance() + $summaryrecords["Others.B"]->getInadvance();
        $record->setInadvance($inadvance);
        $fullmonaward = $summaryrecords["Others.C"]->getFullmonaward() + $summaryrecords["Others.B"]->getFullmonaward();
        $record->setFullmonaward($fullmonaward);
        $salary = $summaryrecords["Others.C"]->getSalary() + $summaryrecords["Others.B"]->getSalary();
        $record->setSalary($salary);

        $record->setMonth($monthobj);
        $record->setSheet("Others");
        $this->_em->persist($record);
        $summaryrecords["Others"] = $record;

        // ALL
        $record = $this->_summarydetails->findOneBy(array("month" => $monthobj, "sheet" => "ALL"));
        if (!$record) {
            $record = new \Synrgic\Infox\Salarysummarydetails();
        }
        $normalhours = $summaryrecords["HC"]->getNormalhours() + $summaryrecords["HT"]->getNormalhours() + $summaryrecords["Others"]->getNormalhours();
        $record->setNormalhours($normalhours);
        $normalsalary = $summaryrecords["HC"]->getNormalsalary() + $summaryrecords["HT"]->getNormalsalary() + $summaryrecords["Others"]->getNormalsalary();
        $record->setNormalsalary($normalsalary);
        $othours = $summaryrecords["HC"]->getOthours() + $summaryrecords["HT"]->getOthours() + $summaryrecords["Others"]->getOthours();
        ;
        $record->setOthours($othours);
        $otsalary = $summaryrecords["HC"]->getOtsalary() + $summaryrecords["HT"]->getOtsalary() + $summaryrecords["Others"]->getOtsalary();
        ;
        $record->setOtsalary($otsalary);
        $totalhours = $summaryrecords["HC"]->getTotalhours() + $summaryrecords["HT"]->getTotalhours() + $summaryrecords["Others"]->getTotalhours();
        $record->setTotalhours($totalhours);
        $piecesalary = $summaryrecords["HC"]->getPiecesalary() + $summaryrecords["HT"]->getPiecesalary() + $summaryrecords["Others"]->getPiecesalary();
        $record->setPiecesalary($piecesalary);
        $totalsalary = $summaryrecords["HC"]->getTotalsalary() + $summaryrecords["HT"]->getTotalsalary() + $summaryrecords["Others"]->getTotalsalary();
        $record->setTotalsalary($totalsalary);
        $attenddays = $summaryrecords["HC"]->getAttenddays() + $summaryrecords["HT"]->getAttenddays() + $summaryrecords["Others"]->getAttenddays();
        $record->setAttenddays($attenddays);
        $absencefines = $summaryrecords["HC"]->getAbsencefines() + $summaryrecords["HT"]->getAbsencefines() + $summaryrecords["Others"]->getAbsencefines();
        $record->setAbsencefines($absencefines);
        $foodpay = $summaryrecords["HC"]->getFoodpay() + $summaryrecords["HT"]->getFoodpay() + $summaryrecords["Others"]->getFoodpay();
        $record->setFoodpay($foodpay);
        $rtmonthpay = $summaryrecords["HC"]->getRtmonthpay() + $summaryrecords["HT"]->getRtmonthpay() + $summaryrecords["Others"]->getRtmonthpay();
        $record->setRtmonthpay($rtmonthpay);
        $utfee = $summaryrecords["HC"]->getUtfee() + $summaryrecords["HT"]->getUtfee() + $summaryrecords["Others"]->getUtfee();
        $record->setUtfee($utfee);
        $utallowance = $summaryrecords["HC"]->getUtallowance() + $summaryrecords["HT"]->getUtallowance() + $summaryrecords["Others"]->getUtallowance();
        $record->setUtallowance($utallowance);
        $otherfee = $summaryrecords["HC"]->getOtherfee() + $summaryrecords["HT"]->getOtherfee() + $summaryrecords["Others"]->getOtherfee();
        $record->setOtherfee($otherfee);
        $inadvance = $summaryrecords["HC"]->getInadvance() + $summaryrecords["HT"]->getInadvance() + $summaryrecords["Others"]->getInadvance();
        $record->setInadvance($inadvance);
        $fullmonaward = $summaryrecords["HC"]->getFullmonaward() + $summaryrecords["HT"]->getFullmonaward() + $summaryrecords["Others"]->getFullmonaward();
        $record->setFullmonaward($fullmonaward);
        $salary = $summaryrecords["HC"]->getSalary() + $summaryrecords["HT"]->getSalary() + $summaryrecords["Others"]->getSalary();
        $record->setSalary($salary);

        $record->setMonth($monthobj);
        $record->setSheet("ALL");
        $this->_em->persist($record);

        $this->_em->flush();
        $summaryrecords["ALL"] = $record;

        // All C
        $record = $this->_summarydetails->findOneBy(array("month" => $monthobj, "sheet" => "ALL.C"));
        if (!$record) {
            $record = new \Synrgic\Infox\Salarysummarydetails();
        }
        $normalhours = $summaryrecords["Others.C"]->getNormalhours() + $summaryrecords["HC.C"]->getNormalhours() + $summaryrecords["HT.C"]->getNormalhours();
        $record->setNormalhours($normalhours);
        $normalsalary = $summaryrecords["Others.C"]->getNormalsalary() + $summaryrecords["HC.C"]->getNormalsalary() + $summaryrecords["HT.C"]->getNormalsalary();
        $record->setNormalsalary($normalsalary);
        $othours = $summaryrecords["Others.C"]->getOthours() + $summaryrecords["HC.C"]->getOthours() + $summaryrecords["HT.C"]->getOthours();
        $record->setOthours($othours);
        $otsalary = $summaryrecords["Others.C"]->getOtsalary() + $summaryrecords["HC.C"]->getOtsalary() + $summaryrecords["HT.C"]->getOtsalary();
        $record->setOtsalary($otsalary);
        $totalhours = $summaryrecords["Others.C"]->getTotalhours() + $summaryrecords["HC.C"]->getTotalhours() + $summaryrecords["HT.C"]->getTotalhours();
        $record->setTotalhours($totalhours);
        $piecesalary = $summaryrecords["Others.C"]->getPiecesalary() + $summaryrecords["HC.C"]->getPiecesalary() + $summaryrecords["HT.C"]->getPiecesalary();
        $record->setPiecesalary($piecesalary);
        $totalsalary = $summaryrecords["Others.C"]->getTotalsalary() + $summaryrecords["HC.C"]->getTotalsalary() + $summaryrecords["HT.C"]->getTotalsalary();
        $record->setTotalsalary($totalsalary);
        $attenddays = $summaryrecords["Others.C"]->getAttenddays() + $summaryrecords["HC.C"]->getAttenddays() + $summaryrecords["HT.C"]->getAttenddays();
        $record->setAttenddays($attenddays);
        $absencefines = $summaryrecords["Others.C"]->getAbsencefines() + $summaryrecords["HC.C"]->getAbsencefines() + $summaryrecords["HT.C"]->getAbsencefines();
        $record->setAbsencefines($absencefines);
        $foodpay = $summaryrecords["Others.C"]->getFoodpay() + $summaryrecords["HC.C"]->getFoodpay() + $summaryrecords["HT.C"]->getFoodpay();
        $record->setFoodpay($foodpay);
        $rtmonthpay = $summaryrecords["Others.C"]->getRtmonthpay() + $summaryrecords["HC.C"]->getRtmonthpay() + $summaryrecords["HT.C"]->getRtmonthpay();
        $record->setRtmonthpay($rtmonthpay);
        $utfee = $summaryrecords["Others.C"]->getUtfee() + $summaryrecords["HC.C"]->getUtfee() + $summaryrecords["HT.C"]->getUtfee();
        $record->setUtfee($utfee);
        $utallowance = $summaryrecords["Others.C"]->getUtallowance() + $summaryrecords["HC.C"]->getUtallowance() + $summaryrecords["HT.C"]->getUtallowance();
        $record->setUtallowance($utallowance);
        $otherfee = $summaryrecords["Others.C"]->getOtherfee() + $summaryrecords["HC.C"]->getOtherfee() + $summaryrecords["HT.C"]->getOtherfee();
        $record->setOtherfee($otherfee);
        $inadvance = $summaryrecords["Others.C"]->getInadvance() + $summaryrecords["HC.C"]->getInadvance() + $summaryrecords["HT.C"]->getInadvance();
        $record->setInadvance($inadvance);
        $fullmonaward = $summaryrecords["Others.C"]->getFullmonaward() + $summaryrecords["HC.C"]->getFullmonaward() + $summaryrecords["HT.C"]->getFullmonaward();
        $record->setFullmonaward($fullmonaward);
        $salary = $summaryrecords["Others.C"]->getSalary() + $summaryrecords["HC.C"]->getSalary() + $summaryrecords["HT.C"]->getSalary();
        $record->setSalary($salary);

        $record->setMonth($monthobj);
        $record->setSheet("Others");
        $this->_em->persist($record);
        $summaryrecords["ALL.C"] = $record;

        // All B
        $record = $this->_summarydetails->findOneBy(array("month" => $monthobj, "sheet" => "ALL.B"));
        if (!$record) {
            $record = new \Synrgic\Infox\Salarysummarydetails();
        }
        $normalhours = $summaryrecords["Others.B"]->getNormalhours() + $summaryrecords["HC.B"]->getNormalhours() + $summaryrecords["HT.B"]->getNormalhours();
        $record->setNormalhours($normalhours);
        $normalsalary = $summaryrecords["Others.B"]->getNormalsalary() + $summaryrecords["HC.B"]->getNormalsalary() + $summaryrecords["HT.B"]->getNormalsalary();
        $record->setNormalsalary($normalsalary);
        $othours = $summaryrecords["Others.B"]->getOthours() + $summaryrecords["HC.B"]->getOthours() + $summaryrecords["HT.B"]->getOthours();
        $record->setOthours($othours);
        $otsalary = $summaryrecords["Others.B"]->getOtsalary() + $summaryrecords["HC.B"]->getOtsalary() + $summaryrecords["HT.B"]->getOtsalary();
        $record->setOtsalary($otsalary);
        $totalhours = $summaryrecords["Others.B"]->getTotalhours() + $summaryrecords["HC.B"]->getTotalhours() + $summaryrecords["HT.B"]->getTotalhours();
        $record->setTotalhours($totalhours);
        $piecesalary = $summaryrecords["Others.B"]->getPiecesalary() + $summaryrecords["HC.B"]->getPiecesalary() + $summaryrecords["HT.B"]->getPiecesalary();
        $record->setPiecesalary($piecesalary);
        $totalsalary = $summaryrecords["Others.B"]->getTotalsalary() + $summaryrecords["HC.B"]->getTotalsalary() + $summaryrecords["HT.B"]->getTotalsalary();
        $record->setTotalsalary($totalsalary);
        $attenddays = $summaryrecords["Others.B"]->getAttenddays() + $summaryrecords["HC.B"]->getAttenddays() + $summaryrecords["HT.B"]->getAttenddays();
        $record->setAttenddays($attenddays);
        $absencefines = $summaryrecords["Others.B"]->getAbsencefines() + $summaryrecords["HC.B"]->getAbsencefines() + $summaryrecords["HT.B"]->getAbsencefines();
        $record->setAbsencefines($absencefines);
        $foodpay = $summaryrecords["Others.B"]->getFoodpay() + $summaryrecords["HC.B"]->getFoodpay() + $summaryrecords["HT.B"]->getFoodpay();
        $record->setFoodpay($foodpay);
        $rtmonthpay = $summaryrecords["Others.B"]->getRtmonthpay() + $summaryrecords["HC.B"]->getRtmonthpay() + $summaryrecords["HT.B"]->getRtmonthpay();
        $record->setRtmonthpay($rtmonthpay);
        $utfee = $summaryrecords["Others.B"]->getUtfee() + $summaryrecords["HC.B"]->getUtfee() + $summaryrecords["HT.B"]->getUtfee();
        $record->setUtfee($utfee);
        $utallowance = $summaryrecords["Others.B"]->getUtallowance() + $summaryrecords["HC.B"]->getUtallowance() + $summaryrecords["HT.B"]->getUtallowance();
        $record->setUtallowance($utallowance);
        $otherfee = $summaryrecords["Others.B"]->getOtherfee() + $summaryrecords["HC.B"]->getOtherfee() + $summaryrecords["HT.B"]->getOtherfee();
        $record->setOtherfee($otherfee);
        $inadvance = $summaryrecords["Others.B"]->getInadvance() + $summaryrecords["HC.B"]->getInadvance() + $summaryrecords["HT.B"]->getInadvance();
        $record->setInadvance($inadvance);
        $fullmonaward = $summaryrecords["Others.B"]->getFullmonaward() + $summaryrecords["HC.B"]->getFullmonaward() + $summaryrecords["HT.B"]->getFullmonaward();
        $record->setFullmonaward($fullmonaward);
        $salary = $summaryrecords["Others.B"]->getSalary() + $summaryrecords["HC.B"]->getSalary() + $summaryrecords["HT.B"]->getSalary();
        $record->setSalary($salary);

        $record->setMonth($monthobj);
        $record->setSheet("Others");
        $this->_em->persist($record);
        $summaryrecords["ALL.B"] = $record;


        // query from summarydetails
        $this->view->month = $monthobj;
        $this->view->summaryrecords = $summaryrecords;
        $this->view->username = infox_common::getUsername();

        $this->summarybysite($monthobj);
    }

    private function summarybysite($monthobj) {

        // find active sites
        $sites1 = $this->_site->findBy(array("completed" => FALSE));
        $sites2 = $this->_site->findBy(array("completed" => NULL));
        $sites = array_merge($sites1, $sites2);

        $summarybysite = array();
        foreach ($sites as $site) {
            $siteid = $site->getId();
            if (!key_exists($siteid, $summarybysite)) {
                $summarydata = array("attendance" => 0, "salary" => 0, "site" => NULL);
                $summarybysite[$siteid] = $summarydata;
            }
        }
        //var_dump($summarybysite);            return;            

        $days = "";
        for ($i = 26; $i <= 31; $i++) {
            $day = "s.day$i,";
            $days .= $day;
        }

        for ($i = 1; $i <= 25; $i++) {
            $day = "s.day$i";
            if ($i != 25) {
                $day .= ",";
            }

            $days .= $day;
        }

        $cotmultiple = $this->_setting->findOneBy(array("name" => "cotmultiple"));
        $cotmultipleVal = $cotmultiple->getValue();
        $botmultiple = $this->_setting->findOneBy(array("name" => "botmultiple"));
        $botmultipleVal = $botmultiple->getValue();

        $records = $this->_salaryall->findBy(array("month" => $monthobj));
        foreach ($records as $record) {
            $rate = $record->getRate();
            $worker = $record->getWorker();
            $wid = $worker->getId();
            $month = $record->getMonth()->format("Y-m-d");

            $query = "SELECT $days FROM Synrgic\Infox\Siteattendance s WHERE s.worker=$wid and s.month='$month'";
            $result = $this->_em->createQuery($query)->getResult();
            //print_r($result);

            $totaldays = 0;
            $sitelatest = 0;
            foreach ($result[0] as $tmp) {
                if ($tmp) {
                    $tmparr = explode(";", $tmp);
                    $hoursdata = key_exists(0, $tmparr) ? $tmparr[0] : "";
                    $piecedata = key_exists(1, $tmparr) ? $tmparr[1] : "";
                    $sitedata = key_exists(2, $tmparr) ? $tmparr[2] : "";

                    $sitelatest = $sitedata = ($sitedata == 0) ? $sitelatest : $sitedata;

                    if (key_exists($sitedata, $summarybysite)) {
                        $summarydata = $summarybysite[$sitedata];
                    } else {
                        continue;
                    }

                    $totalsalary = $summarydata['salary'];
                    $totalattendance = $summarydata['attendance'];

                    if ($hoursdata && $hoursdata != "") {
                        $normalhours = ($hoursdata <= 8) ? $hoursdata : 8;
                        $othours = ($hoursdata > 8) ? ($hoursdata - 8) : 0;
                        $totalsalary += $normalhours * $rate + $othours * $cotmultipleVal * $rate;
                        $totalattendance += ($hoursdata >= 8) ? 1 : ($hoursdata/8);
                    } else if ($piecedata && $piecedata != "") {
                        $totalsalary += $piecedata;
                        $totalattendance++;
                    }

                    $summarydata['salary'] = $totalsalary;
                    $summarydata['attendance'] = $totalattendance;
                    $summarybysite[$sitedata] = $summarydata;
                }               
            }
            echo "totalattendance=$totalattendance\n";
        }

        //var_dump($summarybysite);   //return;
        // write data into db
        foreach ($summarybysite as $siteid => $summary) {
            $siteobj = $this->_site->findOneBy(array("id" => $siteid));
            $summarybysite[$siteid]["site"] = $siteobj;

            $summaryrecord = $this->_summarybysite
                    ->findOneBy(array("site" => $siteobj, "month" => $monthobj));
            if (!$summaryrecord) {
                $summaryrecord = new \Synrgic\Infox\Salarysummarybysite();
            }
            $summaryrecord->setSite($siteobj);
            $summaryrecord->setMonth($monthobj);
            $summaryrecord->setTotalsalary($summary["salary"]);
            $summaryrecord->setAttendance($summary["attendance"]);
            $this->_em->persist($summaryrecord);
        }
        $this->_em->flush();

        $this->view->summarybysite = $summarybysite;
    }

}
