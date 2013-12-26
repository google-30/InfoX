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
    }

    public function indexAction() {
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $this->view->sheet = $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        $this->view->workerarr = infox_worker::getworkerlistbysheet($requestsheet);
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
        foreach ($recordsall as $record)
        {
            $salary = $record->getSalary();
            $salaryall += $salary;
        }
        $this->view->salaryall = $salaryall;
        setlocale(LC_MONETARY, 'en_US');
        $salaryallformat = money_format('%i', $salaryall) ;
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
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();

        // TODO: may cause datetime issue here
        $monthstr = $this->getParam("month", "now");
        if ($monthstr == "now") {
            $error .= "salarybymonthAction:monthstr error.";
            $this->view->error = $error;
            return;
        }
        $month = new Datetime($monthstr);
        $this->view->monthstr = $monthstr;

        // get all records in this month        
        $salaryrecords = infox_salary::getSalaryRecordsByMonthSheet($month, $sheet);
        //$attendarr = infox_project::getAttendanceByMonthSheet($monthstr, $sheet);
        //$salarytabs = $this->generateSalaryTabs($salaryrecords, $attendarr);
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
        $tab = infox_project::generateAttendanceTab($attendance, $monthstr, false);
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
            $tab = infox_project::generateAttendanceTab($attendance, $monthstr, false);
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
        $tab = infox_project::generateAttendanceTab($attendance, $month->format("Y-m"), false);
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

        $salaryrecords = array();
        foreach($records as $tmp)
        {
            $worker = $tmp->getWorker();
            if($sheet == $worker->getSheet())
            {
                $salaryrecords[] = $tmp;
            }
        }
        
        $this->view->salaryrecords = $salaryrecords;
        $this->view->monthobj = $monthobj;
        
        $tmparr = explode(".", $sheet);
        $sheetprx = $tmparr[0];
        $cmyobj = $this->_companyinfo->findOneBy(array("sheetprx"=>$sheetprx));
        $this->view->company = $cmyobj;
        
        $defaultdate = new Datetime("now");
        $defaultdatestr = $defaultdate->format("d/m/Y");
        
        $date = $this->getParam("date", "");
        if($date == "")
        {
            $receiptdate = $defaultdatestr;
        }
        else
        {
            $dateobj = new Datetime($date);
            $receiptdate = $dateobj->format("d/m/Y");
        }
        $this->view->receiptdate = $receiptdate;
    }
}
