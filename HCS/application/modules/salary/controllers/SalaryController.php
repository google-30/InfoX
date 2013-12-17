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
    }

    public function indexAction() {
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $this->view->sheet = $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        $this->view->workerarr = infox_worker::getworkerlistbysheet($requestsheet);
    }

    public function personalsalaryAction() {
        
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
        $attendarr = infox_project::getAttendanceByMonthSheet($monthstr, $sheet);

        $salarytabs = $this->generateSalaryTabs($salaryrecords, $attendarr);
        $this->view->salarytabs = $salarytabs;

        $this->view->username = infox_common::getUsername();
    }

    private function generateSalaryTabs($salaryrecords, $attendarr) {
        $salarytabs = array();
        $sno = 0;
        $monthstr = $salaryrecords[0]->getMonth()->format("Y-m");

        foreach ($salaryrecords as $record) {
            $tmparr = array();
            $workertab = "";
            $worker = $record->getWorker();
            $sno++;
            $wid = $worker->getId();
            $tmparr = $this->getSalarytabsByWidMonthstr($wid, $monthstr);
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
        /* echo "rate=$rate";
          echo $rate!="";
          echo $rate!=0; */
        $actualrate = ($rate != "" && $rate != 0) ? $rate : $price;

        $tab = '<table class="workerinfo">';
        $tab .= "<tr><th rowspan=1>序号</th><th>准证号</th><th>编号</th><th>姓名</th><th>单价</th><th>工种</th></tr>";
        $tab .= "<tr><td>$sno</td><td>$wpno</td><td>$eeeno</td><td>$name</td><td>$actualrate</td><td>$type</td></tr>";
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
        if ($rate != "")
            $sr->setRate((float) $rate);
        if ($otherfee != "")
            $sr->setOtherfee((float) $otherfee);
        if ($inadvance != "")
            $sr->setinadvance((float) $inadvance);
        if ($absencedays != "")
            $sr->setAbsencedays((float) $absencedays);
        if ($absencefines != "")
            $sr->setAbsencefines((float) $absencefines);
        if ($rtmonthpay != "")
            $sr->setRtmonthpay((float) $rtmonthpay);
        if ($rtmonths != "")
            $sr->setRtmonths((int) $rtmonths);
        if ($rtall != "")
            $sr->setRtall((float) $rtall);
        if ($utfee != "")
            $sr->setUtfee((float) $utfee);
        if ($utallowance != "")
            $sr->setUtallowance((float) $utallowance);
        if ($fullmonaward != "")
            $sr->setFullmonaward((float) $fullmonaward);
        if ($foodpay != "") {
            $sr->setFoodpay((float) abs($foodpay));
        }
        if ($remark != "") {
            $sr->setRemark($remark);
        }

        $this->_em->persist($sr);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        //$salaryrepo = infox_worker::getSalaryRepoByWorker($worker);
        //$salaryrecord = $salaryrepo->findOneBy(array("worker" => $worker, "month" => $monthobj));
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

    private function getSalarytabsByWidMonthstr($wid, $monthstr) {
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

        $tab = infox_salary::generatePaymentTabByRecord($salaryrecord, true);
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
        $salaryrepo = infox_salary::getReposBySheet($sheet);
        $records = $salaryrepo->findBy(array("month" => $monthobj));

        $this->view->salaryrecords = $records;
        $this->view->monthobj = $monthobj;
    }

    private function getReceiptsByRecords($records) {
        
    }

}
