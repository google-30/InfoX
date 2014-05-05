<?php

/**
 * Description of WorkerController
 *
 * @author philip
 */
include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';
include 'InfoX/infox_salary.php';

class Salary_WorkerController extends Zend_Controller_Action {

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
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $this->view->sheet = $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        $this->view->workerarr = infox_worker::getworkerlistbysheet($requestsheet);

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
        $rtall = 0;
        $utall = 0;
        $frommonth = new Datetime("2014-01-01");

        foreach ($recordsall as $record) {
            $salary = $record->getSalary();
            $salaryall += $salary;

            $month = $record->getMonth();
            if ($frommonth < $month) {
                //$rtmonthpay = $record->getRtmonthpay();
                //$rtall += $rtmonthpay;
                $utfee = $record->getUtfee();
                $utall += $utfee;
            }
        }

        $this->view->salaryall = $salaryall;
        setlocale(LC_MONETARY, 'en_US');
        $salaryallformat = money_format('%i', $salaryall);
        $this->view->salaryallformat = $salaryallformat;
        $this->view->recordsbyyear = $recordsall;
        $this->view->workerarr = $workerarr;

        $this->view->rtallformat = money_format('%i', $rtall);
        $this->view->utallformat = money_format('%i', $utall);

        //$salarytabs = infox_salary::generateSalaryTabs($recordsall, false);
        $salarytabs = infox_salary::generateWorkerSalaryTabs($recordsall, false);
        $this->view->salarytabs = $salarytabs;

        // worker attendance data by month
        //$sitesalarytabs = infox_salary::generateSiteSalaryTabs($recordsall);
        //$this->view->sitesalarytabs = $sitesalarytabs;        
        // selects for users
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

}
