<?php

include 'InfoX/infox_common.php';
include 'InfoX/infox_worker.php';

define('UPLOAD_WORKER', APPLICATION_PATH . '/data/uploads/workers/images/');

class Worker_ArchiveController extends Zend_Controller_Action {

    private $_files;

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
        $this->_workercustominfo = $this->_em->getRepository('Synrgic\Infox\Workercustominfo');

        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction() {
        //$this->getworkerlist();
        //$this->getCustominfo(0);
        $this->view->sheet = $requestsheet = $this->getParam("sheet", "All");
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetArrAll();
        $this->view->maindata = $workerarr = infox_worker::getResignedWorkersBySheet($requestsheet);
    }

    public function editAction() {
        $id = $this->_getParam("id");
        //echo "id=$id";
        $this->view->workerid = $id;
        $this->view->worker = $worker = $this->_workerdetails->findOneBy(array("id" => $id));

        $sites = $this->_site->findAll();
        $this->view->sites = $sites;

        $this->findCompanies();
        $this->getWorktypes();

        $this->getCustominfo($id);

        $this->getSheetarr();
    }

    public function previewlistAction() {
        $this->_helper->layout->disableLayout();
        $this->getworkerlist();
        $this->getCustominfo($id);
    }

    private function getworkerlist() {
        $requestsheet = $this->getParam("sheet", "HC.C");
        //$allworkers = $this->_workerdetails->findAll();
        $allworkers = $this->_workerdetails->findBy(array("sheet" => $requestsheet));

        $workerarr = array();
        foreach ($allworkers as $tmp) {
            /*
              $sheet = $tmp->getSheet();
              if($sheet != $requestsheet)
              {
              continue;
              }
             */

            $date = $tmp->getResignation();
            $now = new DateTime("now");

            if (!$date) {//no date = still on duty
                continue;
            }

            $interval = $date->diff($now);
            $invert = $interval->invert;
            if (!$invert) {
                $workerarr[] = $tmp;
            }
        }

        $this->view->sheet = $requestsheet;
        $this->view->maindata = $workerarr;
    }

    private function getCustominfo($id) {
        // titles
        $label = "01";
        $category = "worker";
        $infoobj = $this->_miscinfo->findOneBy(array("category" => $category, "label" => $label));
        $values = $infoobj ? $infoobj->getValues() : "";
        $this->view->customtitles = explode(";", $values);

        // data
        $workerobj = $this->_workerdetails->findOneBy(array("id" => $id));
        $custominfoobj = $workerobj ? $workerobj->getWorkercustominfo() : null;
        $this->view->custominfos = $custominfoobj ? $custominfoobj : null;
    }

    private function findCompanies() {
        $this->view->companies = $this->_companyinfo->findAll();
    }

    private function getWorktypes() {
        $label = "info04";
        $values = $this->_miscinfo->findOneBy(array("label" => $label))->getValues();

        $this->view->worktypes = explode(";", $values);
    }

}
