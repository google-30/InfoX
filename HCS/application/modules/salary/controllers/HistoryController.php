<?php

/**
 * Description of Salary_HistoryController
 *
 * @author philip
 */
include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';
include 'InfoX/infox_salary.php';

class Salary_HistoryController extends Zend_Controller_Action {

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
        //$this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        //$this->view->sheet = $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        //$this->view->workerarr = infox_worker::getworkerlistbysheet($requestsheet);
        $requests = $this->getRequest()->getPost();
        if (0) {
            echo "siteAction dump";
            var_dump($requests);
            //return;
        }
    }

    public function siteAction() {
        $requests = $this->getRequest()->getPost();
        if (0) {            
            var_dump($requests);
            return;
        }

        infox_common::turnoffLayout($this->_helper);
        //infox_common::turnoffView($this->_helper);

        $this->view->username = infox_common::getUsername();

        // all sites
        $allsites = $this->_site->findAll();
        $this->view->allsites = $allsites;

        $siteid = $this->getParam("site", 0);
        if (!$siteid) {
            return;
        }
        $siteobj = $this->_site->findOneBy(array("id" => $siteid));
        $this->view->currentsite = $siteobj ? $siteobj : NULL;

        $datefrom = $this->getParam("from", "");
        $dateto = $this->getParam("to", "");
        $fromobj = $datefrom == "" ? new DateTime("2010-01-01") : new DateTime(substr($datefrom, 0, 8) . "01");
        $toobj = $dateto == "" ? new DateTime("2020-01-01") : new DateTime(substr($dateto, 0, 8) . "01");
        $this->view->monthfrom = $fromobj;
        $this->view->monthto = $toobj;

        $siterecords = $this->_summarybysite->findBy(array("site" => $siteobj));
        $sitesummary = array();
        foreach ($siterecords as $tmp) {
            $monthobj = $tmp->getMonth();
            if ($monthobj >= $fromobj && $monthobj <= $toobj) {
                $sitesummary[] = $tmp;
            }
        }
        usort($sitesummary, array($this, 'sortbymonth'));        
        $this->view->sitesummary = $sitesummary;
    }

    private function sortbymonth($a, $b)
    {
        $amonth = $a->getMonth();
        $bmonth = $b->getMonth();
        return ($amonth >= $bmonth);
    }

    public function companyAction() {
        $requests = $this->getRequest()->getPost();
        if (0) {            
            var_dump($requests);
            return;
        }

        infox_common::turnoffLayout($this->_helper);
        $this->view->username = infox_common::getUsername();

        $sheets = array("HC.C", "HC.B", "HT.C", "HT.B", "Others", "HC", "HT", "ALL");
        $this->view->sheets = $sheets;        
        $sheetid = $this->getParam("sheet", 0);
        $this->view->currentsheet = $sheetid;

        $datefrom = $this->getParam("from", "");
        $dateto = $this->getParam("to", "");
        $fromobj = $datefrom == "" ? new DateTime("2010-01-01") : new DateTime(substr($datefrom, 0, 8) . "01");
        $toobj = $dateto == "" ? new DateTime("2020-01-01") : new DateTime(substr($dateto, 0, 8) . "01");
        $this->view->monthfrom = $fromobj;
        $this->view->monthto = $toobj;

        $records = $this->_summarydetails->findBy(array("sheet" => $sheets[$sheetid]));
        $summary = array();
        foreach ($records as $tmp) {
            $monthobj = $tmp->getMonth();
            if ($monthobj >= $fromobj && $monthobj <= $toobj) {
                $summary[] = $tmp;
            }
        }
        usort($summary, array($this, 'sortbymonth'));        
        $this->view->summaryrecords = $summary;
    }
}
