<?php

include 'InfoX/infox_common.php';
include 'InfoX/infox_worker.php';

define('UPLOAD_WORKER', APPLICATION_PATH . '/data/uploads/workers/images/');

class Worker_ManageController extends Zend_Controller_Action {

    private $_files;

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerskill = $this->_em->getRepository('Synrgic\Infox\Workerskill');
        $this->_workercompanyinfo = $this->_em->getRepository('Synrgic\Infox\Workercompanyinfo');
        $this->_workerfamily = $this->_em->getRepository('Synrgic\Infox\Workerfamily');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_workercustominfo = $this->_em->getRepository('Synrgic\Infox\Workercustominfo');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
        $this->_workerrenew = $this->_em->getRepository('Synrgic\Infox\Workerrenew');
    }

    public function indexAction() {
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        $workerarr = infox_worker::getActiveWorkerdetailsBySheet($requestsheet);

        $this->view->sheet = $requestsheet;
        $this->view->maindata = $workerarr;
        //$this->getworkerlist();
        //$this->getCustominfo(0);    

        /*
          $onswitches = array(
          "renewdate" => "Renew Date",
          "wpexpiry" => "WP Expiry", "issuedate" => "Date of Issue",
          "ppexpiry" => "PP Expiry", "rate" => "RATE", "medicaldate" => "Medical Date",
          "csoc" => "C.S.O.C", "securityexp" => "Security Bond Expiry Date");

          $renewtabs = array();
          $maindata = $workerarr;
          foreach ($maindata as $tmp1) {
          $name1 = $tmp1->getNamechs();
          $nameeng1 = $tmp1->getNameeng();
          $wid1 = $tmp1->getId();
          $eeeno = $tmp1->getEeeno();

          $title = "<h3>Renew Records of $eeeno - $name1</h3>";
          $divid = "renew" . $wid1;
          $gridid = "worker" . $wid1;

          $renewrecords = $this->_workerrenew->findBy(array("worker" => $tmp1));

          // TODO: find current renew info in worker details
          $currentrenew = array();
          $currentrenew['wpexpiry'] = new DateTime("now");

          $renewrecords[] = $tmp1;
          // TODO: sort records

          $wdtab = $this->view->grid($gridid, true);
          foreach ($onswitches as $key => $value) {
          $wdtab = $wdtab->field($key, $value);
          }
          $wdtab = $wdtab->field("renewactions", "Action");
          $wdtab = $wdtab->paginatorEnabled(false)->setSorting(false);
          $wdtab = $wdtab->helper(new GridHelper_Workerdetails());
          $wdtab = $wdtab->data($renewrecords);
          $wdtab = $wdtab->render();

          $renewform1 = '<h3>Add Renew Record</h3>'
          . '<table id="renewinfotab">'
          . '<tr>'
          . '<td>WP Expiry:</td>'
          . '<td><input name="wpexpiry" id="wpexpiry' . $wid1 . '" type="text" placeholder="WP Expiry" class="dateclass"></td>'
          . '<td>Date of Issue:</td>'
          . '<td><input name="issuedate" id="issuedate' . $wid1 . '" type="text" placeholder="Date of Issue" class="dateclass"></td>'
          . '</tr>'
          . '<tr>'
          . '<td>Date of Issue: </td>'
          . '<td><input name="ppexpiry" id="ppexpiry' . $wid1 . '" type="text" placeholder="PP Expiry" class="dateclass"></td>'
          . '<td>Rate: </td>'
          . '<td><input name="rate" id="rate' . $wid1 . '" type="text" placeholder="Rate"></td>'
          . '</tr>'
          . '<tr>'
          . '<td>Medical Date: </td>'
          . '<td><input name="medicaldate" id="medicaldate' . $wid1 . '" type="text" placeholder="Medical Date" class="dateclass"></td>'
          . '<td>C.S.O.C: </td>'
          . '<td><input name="csoc" id="csoc' . $wid1 . '" type="text" placeholder="C.S.O.C" class="dateclass"></td>'
          . '</tr>'
          . '<tr>'
          . '<td>Security Bond Expiry Date: </td>'
          . '<td><input name="securityexp" id="securityexp' . $wid1 . '" type="text" placeholder="Security Bond Expiry Date" class="dateclass"></td>'
          . '<td>Renew Date: </td>'
          . '<td><input name="renewdate" id="renewdate' . $wid1 . '" type="text" placeholder="Renew Date" class="dateclass"></td>'
          . '</tr>'
          . '</table>'
          . '<input type="button" value="提交" data-theme="a" data-mini="true" onclick="postrenewinfo(' . $wid1 . ')">'
          . '';

          $renewform1 ="";
          $renewtabs[] = '<div id="' . $divid . '">' . $title . $wdtab . "<br>" . $renewform1 . "</div>";
          }

          $this->view->rewtabs = $renewtabs;
         * 
         */
    }

    public function renewlistAction() {
        infox_common::turnoffView($this->_helper);
        $wid = $this->getParam("wid", 0);
        //echo "wid=" . $wid;

        $worker = $this->_workerdetails->findOneBy(array("id" => $wid));
        $wpno = $worker->getWpno();
        $eeeno = $worker->getEeeno();
        $name = ($worker->getNamechs() == "") ? $worker->getNameeng() : $worker->getNamechs();
        $workerarr = $this->_workerdetails->findBy(array("wpno" => $wpno));
        $renewrecords = $this->_workerrenew->findBy(array("worker" => $worker));

        //$renewrecords[] = $worker;
        $renewrecords = array_merge($renewrecords, $workerarr);

        $title = "<h3>$eeeno - $name</h3>";

        $onswitches = array(
            "renewdate" => "Renew Date",
            "wpexpiry" => "WP Expiry", "issuedate" => "Date of Issue",
            "ppexpiry" => "PP Expiry", "rate" => "RATE", "medicaldate" => "Medical Date",
            "csoc" => "C.S.O.C", "securityexp" => "Security Bond Expiry Date");

        $wdtab = $this->view->grid("renew", true);
        foreach ($onswitches as $key => $value) {
            $wdtab = $wdtab->field($key, $value);
        }
        //$wdtab = $wdtab->field("renewactions", "Action");
        $wdtab = $wdtab->paginatorEnabled(false)->setSorting(false);
        $wdtab = $wdtab->helper(new GridHelper_Workerdetails());
        $wdtab = $wdtab->data($renewrecords);
        $wdtab = $wdtab->render();
        $renewtab = '<div id="' . 0 . '">' . $title . $wdtab . "</div>";

        echo $renewtab;
    }

    public function previewlistAction() {
        $this->_helper->layout->disableLayout();
        $this->getworkerlist();
    }

    private function getworkerlist() {
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();

        $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        $workerarr = infox_worker::getActiveWorkerdetailsBySheet($requestsheet);

        $this->view->sheet = $requestsheet;
        $this->view->maindata = $workerarr;
        $this->getCustominfo(0);
    }

    public function index1Action() {
        // dql result: http://docs.doctrine-project.org/en/2.1/reference/dql-doctrine-query-language.html
        // embed select: http://msdn.microsoft.com/zh-cn/library/ms189575(v=sql.105).aspx
        /*
          $query = $this->_em->createQuery(
          'select w, wc.hwage, ws.worktype, ws.worklevel,
          (select site.name from Synrgic\Infox\Site site where site.id = wc.site) as sitename,
          (select cinfo.namechs from Synrgic\Infox\Companyinfo cinfo where cinfo.id = wc.company) as companyname
          from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws'

          );
          $result = $query->getResult();
          $this->view->result = $result;

          $query = $this->_em->createQuery(
          'select w, wc, ws from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws'
          );
          $result = $query->getResult();
          $this->view->workersdata = $result;
         */

        $qb = $this->_em->createQueryBuilder();
        $qb->select('w', 'ws')
                ->from('Synrgic\Infox\Worker', 'w')
                ->leftJoin('w.workerskill', 'ws')
                ->leftJoin('w.workercompanyinfo', 'wc');
        $result = $qb->getQuery()->getResult();

        $this->view->workersdata = $result;

        $this->getCustominfo(0);
    }

    public function workerexpireAction() {
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $this->view->sheet = $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        $this->view->paramarr = $paramarr = array("wpexpiry" => "Work Pass",
            "ppexpiry" => "Passport", "csoc" => "Csoc", "securityexp" => "Security Bond Expiry Date",);

        $expiryarr = array();

        $workerarr = infox_worker::getActiveWorkerdetailsBySheet($requestsheet);
        foreach ($paramarr as $param => $title) {
            $tmparr = $this->getExpiryArr($workerarr, $param);
            //var_dump($tmparr); return;
            //ksort($tmparr);
            $expiryarr[$param] = $tmparr;
        }

        $this->view->expiryarr = $expiryarr;
    }

    private function getExpiryArr($workerarr, $param) {
        $returnarr = array();
        $keystr = "";
        foreach ($workerarr as $worker) {
            switch ($param) {
                case "wpexpiry":
                    $date = $worker->getWpexpiry();
                    break;
                case "ppexpiry":
                    $date = $worker->getPpexpiry();
                    break;
                case "csoc":
                    $date = $worker->getCsoc();
                    break;
                case "securityexp":
                    $date = $worker->getSecurityexp();
                    break;
                default:
                    return null;
            }

            $keystr = $date ? $date->format("m/Y") : "日期未定义";
            if (!array_key_exists($keystr, $returnarr)) {
                $returnarr[$keystr] = array();
            }
            $returnarr[$keystr][] = $worker;
        }

        uksort($returnarr, array($this, 'usort_expiredate'));
        return $returnarr;
    }

    private function usort_expiredate($a, $b) {
        if ($a == "日期未定义" || $b == "日期未定义") {
            return 0;
        }

        $aobj = new Datetime("01/" . $a);
        $bobj = new Datetime("01/" . $b);
        if ($aobj > $bobj) {
            return 1;
        } else {
            return 0;
        }
    }

    public function previewexpiryAction() {
        $this->_helper->layout->disableLayout();
        $exp = $this->getParam("expiry", "");
        $this->view->expiry = $exp;
        $this->getallexp();
    }

    private function getallexp() {
        $workers = $this->_workerdetails->findAll();
        if (!count($workers)) {
            echo "Please import worker list.";
            return;
        }
        // wpexpiry, ppexpiry, csoc
        // wpexpiry
        $expiredarr = array();
        $expire1arr = array();
        $expire2arr = array();

        foreach ($workers as $tmp) {
            $date = $tmp->getResignation();
            $resigned = $this->workerresigned($date);
            if ($resigned) {// do not count resigned workers
                //echo "found resigned=" . $tmp->getEeeno();
                continue;
            }

            $date = $tmp->getWpexpiry();
            $now = new DateTime("now");

            if (!$date) {//no date, then just say it's already expired
                $expiredarr[] = $tmp;
                continue;
            }

            $interval = $date->diff($now);
            $invert = $interval->invert;
            $days = $interval->days;
            //$mark = $invert ? "+" : "-"; echo $mark . $days . "<br>";

            if (!$invert) {
                $expiredarr[] = $tmp;
            } else if ($days <= 30) {
                $expire1arr[] = $tmp;
            } else if ($days <= 60) {
                $expire2arr[] = $tmp;
            }
        }

        $this->view->wpexpiryarr = array($expire1arr, $expire2arr, $expiredarr);

        // ppexpiry
        $expiredarr = array();
        $expire1arr = array();
        $expire2arr = array();

        foreach ($workers as $tmp) {
            $date = $tmp->getResignation();
            $resigned = $this->workerresigned($date);
            if ($resigned) {// do not count resigned workers
                //echo "found resigned=" . $tmp->getEeeno();
                continue;
            }

            $date = $tmp->getPpexpiry();
            $now = new DateTime("now");

            if (!$date) {//no date, then just say it's already expired
                $expiredarr[] = $tmp;
                continue;
            }

            $interval = $date->diff($now);
            $invert = $interval->invert;
            $days = $interval->days;
            //$mark = $invert ? "+" : "-"; echo $mark . $days . "<br>";

            if (!$invert) {
                $expiredarr[] = $tmp;
            } else if ($days <= 30) {
                $expire1arr[] = $tmp;
            } else if ($days <= 60) {
                $expire2arr[] = $tmp;
            }
        }

        $this->view->ppexpiryarr = array($expire1arr, $expire2arr, $expiredarr);

        // securityexp
        $expiredarr = array();
        $expire1arr = array();
        $expire2arr = array();

        foreach ($workers as $tmp) {
            $date = $tmp->getResignation();
            $resigned = $this->workerresigned($date);
            if ($resigned) {// do not count resigned workers
                //echo "found resigned=" . $tmp->getEeeno();
                continue;
            }

            //$date = $tmp->getSecurityexp();
            $date = $tmp->getCsoc();
            $now = new DateTime("now");

            if (!$date) {//no date, then just say it's already expired
                $expiredarr[] = $tmp;
                continue;
            }

            $interval = $date->diff($now);
            $invert = $interval->invert;
            $days = $interval->days;
            //$mark = $invert ? "+" : "-"; echo $mark . $days . "<br>";

            if (!$invert) {
                $expiredarr[] = $tmp;
            } else if ($days <= 30) {
                $expire1arr[] = $tmp;
            } else if ($days <= 60) {
                $expire2arr[] = $tmp;
            }
        }

        $this->view->securityexparr = array($expire1arr, $expire2arr, $expiredarr);
    }

    private function workerresigned($date) {
        if (!$date) {
            return false;
        }

        $now = new DateTime("now");
        $interval = $date->diff($now);
        $invert = $interval->invert;
        if (!$invert) {
            return true;
        }

        return false;
    }

    private function getSecurityexp($allworkers) {// get expired, 1 month, 2 monthes, worker list
        // http://stackoverflow.com/questions/10582108/how-can-i-compare-a-date-with-current-date-using-doctrine-2
        // $em->createQuery('SELECT d FROM test d WHERE d.expDate > CURRENT_DATE()');
        if (!count($allworkers)) {
            return;
        }

        $expiredarr = array();
        $expire1arr = array();
        $expire2arr = array();

        foreach ($allworkers as $tmp) {
            $date = $tmp["workerskill"]->getSecurityexp();
            $now = new DateTime("now");

            if (!$date) {
                $expiredarr[] = $tmp;
                continue;
            }

            $interval = $date->diff($now);
            $invert = $interval->invert;
            $days = $interval->days;
            //$mark = $invert ? "+" : "-"; echo $mark . $days . "<br>";

            if (!$invert) {
                $expiredarr[] = $tmp;
            } else if ($days <= 30) {
                $expire1arr[] = $tmp;
            } else if ($days <= 60) {
                $expire2arr[] = $tmp;
            }
        }

        $this->view->expired = $expiredarr;
        $this->view->expire1 = $expire1arr;
        $this->view->expire2 = $expire2arr;
    }

    private function getCustominfo($id) {
        // titles
        $label = "01";
        $category = "worker";
        $infoobj = $this->_miscinfo->findOneBy(array("category" => $category, "label" => $label));
        $values = $infoobj ? $infoobj->getValues() : "";
        $this->view->customtitles = explode(";", $values);

        // data
        $workerobj = $this->_worker->findOneBy(array("id" => $id));
        $custominfoobj = $workerobj ? $workerobj->getWorkercustominfo() : null;
        $this->view->custominfos = $custominfoobj ? $custominfoobj : null;
    }

    public function addAction() {
        $this->view->id = 0;

        $sites = $this->_site->findAll();
        $this->view->sites = $sites;

        $this->findCompanies();
        $this->getWorktypes();

        $this->getCustominfo(0);

        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();

        $workerarr = infox_worker::getAllActiveWorkerdetails();
        $this->view->workerarr = $workerarr;
        //echo "addAction,count=" . count($workerarr);
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

        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();

        $workerarr = infox_worker::getAllActiveWorkerdetails();
        $this->view->workerarr = $workerarr;
    }

    public function loadrenewlistAction() {
        infox_common::turnoffView($this->_helper);

        $id = $this->_getParam("id", 0);
        if (!$id) {
            echo "";
            return;
        }
        // renew records
        $worker = $this->_workerdetails->findOneBy(array("id" => $id));
        $wpno = $worker->getWpno();
        $eeeno = $worker->getEeeno();
        $name = ($worker->getNamechs() == "") ? $worker->getNameeng() : $worker->getNamechs();
        $workerarr = $this->_workerdetails->findBy(array("wpno" => $wpno));
        $renewrecords = $this->_workerrenew->findBy(array("worker" => $worker));
        $renewrecords = array_merge($renewrecords, $workerarr);

        $onswitches = array(
            "renewdate" => "Renew Date",
            "wpexpiry" => "WP Expiry", "issuedate" => "Date of Issue",
            "ppexpiry" => "PP Expiry", "rate" => "RATE", "medicaldate" => "Medical Date",
            "csoc" => "C.S.O.C", "securityexp" => "Security Bond Expiry Date");

        $wdtab = $this->view->grid("renewtab", true);
        foreach ($onswitches as $key => $value) {
            $wdtab = $wdtab->field($key, $value);
        }
        $wdtab = $wdtab->field("renewactions", "Action");
        $wdtab = $wdtab->paginatorEnabled(false)->setSorting(false);
        $wdtab = $wdtab->helper(new GridHelper_Workerdetails());
        $wdtab = $wdtab->data($renewrecords);
        $wdtab = $wdtab->render();
        $renewtab = '<div id="">' . $wdtab . "</div>";

        echo $renewtab;
    }

    public function loadrenewAction() {
        infox_common::turnoffView($this->_helper);

        $id = $this->_getParam("id", 0);
        if (!$id) {
            return;
        }

        $record = $this->_workerrenew->findOneBy(array("id" => $id));
        $dateobj = $record->getWpexpiry();
        $wpexpiry = $dateobj ? $dateobj->format("d/m/Y") : "";
        $dateobj = $record->getIssuedate();
        $issuedate = $dateobj ? $dateobj->format("d/m/Y") : "";
        $dateobj = $record->getPpexpiry();
        $ppexpiry = $dateobj ? $dateobj->format("d/m/Y") : "";

        $rate = $record->getRate();

        $dateobj = $record->getMedicaldate();
        $medicaldate = $dateobj ? $dateobj->format("d/m/Y") : "";
        $dateobj = $record->getCsoc();
        $csoc = $dateobj ? $dateobj->format("d/m/Y") : "";
        $dateobj = $record->getSecurityexp();
        $securityexp = $dateobj ? $dateobj->format("d/m/Y") : "";

        $dateobj = $record->getRenewdate();
        $renewdate = $dateobj ? $dateobj->format("d/m/Y") : "";

        $array = array("wpexpiry" => $wpexpiry, "issuedate" => $issuedate, "ppexpiry" => $ppexpiry, "rate" => $rate,
            "medicaldate" => $medicaldate, "csoc" => $csoc, "securityexp" => $securityexp, "renewdate" => $renewdate);
        echo json_encode($array);
    }

    public function deleteAction() {
        infox_common::turnoffView($this->_helper);
        $id = $this->getParam("id");
        $data = $this->_workerdetails->findOneBy(array('id' => $id));
        $this->_em->remove($data);
        $this->_em->flush();
        $this->_redirect("worker/manage");
    }

    public function submitAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $wid = $this->storeInfo($requests);
        $this->_files = $_FILES;

        $this->storePic($wid);

        //$sheet = trim($this->getParam("sheet", ""));
        //$url = "/worker/manage?sheet=$sheet";
        $url = "/worker/manage/edit/id/" . $wid;
        $this->redirect($url);
    }

    private function storeInfo($requests) {
        $mode = $requests["mode"];

        if ($mode == "Edit") {
            $workerid = $this->getParam("workerid", 0);
            $data = $this->_workerdetails->findOneBy(array('id' => $workerid));

            $customdata = $data->getWorkercustominfo();
            if (!$customdata) {
                $customdata = new \Synrgic\Infox\Workercustominfo();
            }
        } else if ($mode = "Create") {
            $data = new \Synrgic\Infox\Workerdetails();
            $customdata = new \Synrgic\Infox\Workercustominfo();
        }

        $sn = (int) $this->getParam("sn", "");
        $eeeno = $this->getParam("eeeno", "");
        $nameeng = $this->getParam("nameeng", "");
        $namechs = $this->getParam("namechs", "");
        $wpno = $this->getParam("wpno", "");
        $wpexpiry = $this->getParam("wpexpiry", "");
        $wpexpiry = ($wpexpiry == "") ? null : new Datetime($wpexpiry);
        $doa = $this->getParam("doa", "");
        $doa = ($doa == "") ? null : new Datetime($doa);
        $issuedate = $this->getParam("issuedate", "");
        $issuedate = ($issuedate == "") ? null : new Datetime($issuedate);
        $finno = $this->getParam("finno", "");
        $ppno = $this->getParam("ppno", "");
        $dob = $this->getParam("dob", "");
        $dob = ($dob == "") ? null : new Datetime($dob);
        $ppexpiry = $this->getParam("ppexpiry", "");
        $ppexpiry = ($ppexpiry == "") ? null : new Datetime($ppexpiry);
        $rate = $this->getParam("rate", "");
        $securityexp = $this->getParam("securityexp", "");
        $securityexp = ($securityexp == "") ? null : new Datetime($securityexp);
        $worktype = $this->getParam("worktype", "");
        $arrivaldate = $this->getParam("arrivaldate", "");
        $arrivaldate = ($arrivaldate == "") ? null : new Datetime($arrivaldate);
        $medicaldate = $this->getParam("medicaldate", "");
        $medicaldate = ($medicaldate == "") ? null : new Datetime($medicaldate);
        $csoc = $this->getParam("csoc", "");
        $csoc = ($csoc == "") ? null : new Datetime($csoc);
        $medicalinsurance = $this->getParam("medicalinsurance", "");
        $workingsite = $this->getParam("workingsite", "");
        $dormitory = $this->getParam("dormitory", "");
        $hometown = $this->getParam("hometown", "");
        $education = $this->getParam("education", "");
        $age = $this->getParam("age", "");
        $marital = $this->getParam("marital", "");
        $constructionworker = $this->getParam("constructionworker", "");
        $applyfor = $this->getParam("applyfor", "");
        $goodat = $this->getParam("goodat", "");
        $contactno1 = $this->getParam("contactno1", "");
        $contactno2 = $this->getParam("contactno2", "");
        $certificate = $this->getParam("certificate", "");
        $remark = $this->getParam("remark", "");
        $agent = $this->getParam("agent", "");
        $resignation = $this->getParam("resignation", "");
        $resignation = ($resignation == "") ? null : new Datetime($resignation);
        $sheet = trim($this->getParam("sheet", ""));
        /*
          $currentrate = (float) $this->getParam("currentrate", 0);
          $monthrate = (float) $this->getParam("monthrate", 0);
         * 
         */
        /*
          $agent=$this->getParam("sn", "");
          $company=$this->getParam("sn", "");
          $race=$this->getParam("sn", "");
         */

        $data->setSn($sn);
        $data->setEeeno($eeeno);
        $data->setNamechs($namechs);
        $data->setNameeng($nameeng);
        $data->setWpno($wpno);
        $data->setWpexpiry($wpexpiry);
        $data->setDoa($doa);
        $data->setIssuedate($issuedate);
        $data->setFinno($finno);
        $data->setPpno($ppno);
        $data->setDob($dob);
        $data->setPpexpiry($ppexpiry);
        $data->setRate($rate);
        $data->setSecurityexp($securityexp);
        $data->setWorktype($worktype);
        $data->setArrivaldate($arrivaldate);
        $data->setMedicaldate($medicaldate);
        $data->setCsoc($csoc);
        $data->setMedicalinsurance($medicalinsurance);
        $data->setWorkingsite($workingsite);
        $data->setDormitory($dormitory);
        $data->setHometown($hometown);
        $data->setEducation($education);
        $data->setMarital($marital);
        $data->setAge($age);
        $data->setConstructionworker($constructionworker);
        $data->setApplyfor($applyfor);
        $data->setGoodat($goodat);
        $data->setContactno1($contactno1);
        $data->setContactno2($contactno2);
        $data->setCertificate($certificate);
        $data->setRemark($remark);
        $data->setAgent($agent);
        $data->setResignation($resignation);
        $data->setSheet($sheet);
        //$data->setCurrentrate($currentrate);
        //$data->setMonthrate($monthrate);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        return $data->getId();
    }

    private function storePic($workerid) {
        // http://www.w3schools.com/php/php_file_upload.asp
        $files = $this->_files;

        echo "<br>";
        $newfile = "";
        $uploadpath = UPLOAD_WORKER;
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png")) && ($_FILES["file"]["size"] < 10000000)
        //&& in_array($extension, $allowedExts)
        ) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            } else {
                echo "Upload: " . $_FILES["file"]["name"] . "<br>";
                echo "Type: " . $_FILES["file"]["type"] . "<br>";
                echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
                echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

                /*
                  if (file_exists($uploadpath . $_FILES["file"]["name"]))
                  {
                  echo $_FILES["file"]["name"] . " already exists. ";
                  }
                  else
                 */ {
                    $picpath = $uploadpath . $workerid . "." . $extension;
                    move_uploaded_file($_FILES["file"]["tmp_name"], $picpath);
                    echo "Stored in: " . $picpath;
                    $newfile = $workerid . "." . $extension;
                }
            }
        } else {
            //echo "Invalid file";
        }
        echo "<br>";

        if ($newfile != "") {
            $workerdata = $this->_workerdetails->findOneBy(array('id' => $workerid));
            //$pic = '/data/uploads/workers/images/' . $newfile;
            $pic = "/workers-pic/images/" . $newfile;
            $workerdata->setPic($pic);
            $this->_em->persist($workerdata);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
        }
    }

    public function outputAction() {
        $this->_helper->layout->disableLayout();
        $id = $this->getParam("no", 0);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('w', 'ws', 'wc')
                ->from('Synrgic\Infox\Worker', 'w')
                ->leftJoin('w.workerskill', 'ws')
                ->leftJoin('w.workercompanyinfo', 'wc')
                ->where("w.id = ?1")
                ->setParameter(1, $id);
        $result = $qb->getQuery()->getResult();

        $this->view->workerdata = $result;
        //var_dump($result);
        //echo $result ? "got" : "got nothing";

        $this->view->workerdata = $this->_worker->findOneBy(array("id" => $id));
    }

    private function findCompanies() {
        $this->view->companies = $this->_companyinfo->findAll();
    }

    private function getWorktypes() {
        $label = "info04";
        $values = $this->_miscinfo->findOneBy(array("label" => $label))->getValues();

        $this->view->worktypes = explode(";", $values);
    }

    public function resignAction() {
        infox_common::turnoffView($this->_helper);
        $wid = $this->getParam("wid", 0);
        if ($wid) {
            $worker = $this->_workerdetails->findOneBy(array("id" => $wid));

            $resigndate = $this->getParam("resigndate", "");
            $resignremark = $this->getParam("resignremark", "");

            $worker->setResigndate(new Datetime($resigndate));
            $worker->setResignremark($resignremark);
            $worker->setResignation(TRUE);

            $this->_em->persist($worker);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }

            $sheet = $worker->getSheet();
            $url = "/worker/manage?sheet=" . $sheet;
            $this->redirect($url);
        }

        $this->redirect("/worker/manage");
    }

    public function renewAction() {
        infox_common::turnoffView($this->_helper);

        $wid = $this->getParam("wid", 0);
        $worker = $this->_workerdetails->findOneBy(array("id" => $wid));
        if (!$worker) {
            return;
        }

        // store in $this->_workerrenew
        $wpexpiry = $this->getParam("wpexpiry", "");
        $issuedate = $this->getParam("issuedate", "");
        $ppexpiry = $this->getParam("ppexpiry", "");
        $rate = $this->getParam("rate", 0);
        $medicaldate = $this->getParam("medicaldate", "");
        $csoc = $this->getParam("csoc", "");
        $securityexp = $this->getParam("securityexp", "");
        $renewdate = $this->getParam("renewdate", "");
        $rid = $this->getParam("recordid", 0);

        if ($rid != "" && $rid != 0) {
            $data = $this->_workerrenew->findOneBy(array("id" => $rid));
        } else {
            $data = new \Synrgic\Infox\Workerrenew();
            $data->setWorker($worker);
        }

        if ($wpexpiry != "") {
            $tmparr = explode("/", $wpexpiry);
            $wpexpiry = $tmparr[2] . "-" . $tmparr[1] . "-" . $tmparr[0];
            $data->setWpexpiry(new DateTime($wpexpiry));
        }
        if ($issuedate != "") {
            $tmparr = explode("/", $issuedate);
            $issuedate = $tmparr[2] . "-" . $tmparr[1] . "-" . $tmparr[0];
            $data->setIssuedate(new DateTime($issuedate));
        }
        if ($ppexpiry != "") {
            $tmparr = explode("/", $ppexpiry);
            $ppexpiry = $tmparr[2] . "-" . $tmparr[1] . "-" . $tmparr[0];
            $data->setPpexpiry(new DateTime($ppexpiry));
        }

        $data->setRate($rate);

        if ($medicaldate != "") {
            $tmparr = explode("/", $medicaldate);
            $medicaldate = $tmparr[2] . "-" . $tmparr[1] . "-" . $tmparr[0];
            $data->setMedicaldate(new DateTime($medicaldate));
        }
        if ($csoc != "") {
            $tmparr = explode("/", $csoc);
            $csoc = $tmparr[2] . "-" . $tmparr[1] . "-" . $tmparr[0];
            $data->setCsoc(new DateTime($csoc));
        }
        if ($securityexp != "") {
            $tmparr = explode("/", $securityexp);
            $securityexp = $tmparr[2] . "-" . $tmparr[1] . "-" . $tmparr[0];
            $data->setSecurityexp(new DateTime($securityexp));
        }
        if ($renewdate != "") {
            $tmparr = explode("/", $renewdate);
            $renewdate = $tmparr[2] . "-" . $tmparr[1] . "-" . $tmparr[0];
            $data->setRenewdate(new DateTime($renewdate));
        }
        /*
          ($wpexpiry != "") ? $data->setWpexpiry(new DateTime($wpexpiry)) : 0;
          ($issuedate != "") ? $data->setIssuedate(new DateTime($issuedate)) : 0;
          ($ppexpiry != "") ? $data->setPpexpiry(new DateTime($ppexpiry)) : 0;
          ($rate != 0) ? $data->setRate($rate) : 0;
          ($medicaldate != "") ? $data->setMedicaldate(new DateTime($medicaldate)) : 0;
          ($csoc != "") ? $data->setCsoc(new DateTime($csoc)) : 0;
          ($securityexp != "") ? $data->setSecurityexp(new DateTime($securityexp)) : 0;
          ($renewdate != "") ? $data->setRenewdate(new DateTime($renewdate)) : 0;
         * 
         */

        if (1) {
            $this->_em->persist($data);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
        }
    }

    public function deleterenewAction() {
        infox_common::turnoffView($this->_helper);

        $rid = $this->getParam("rid", 0);
        $data = $this->_workerrenew->findOneBy(array("id" => $rid));
        if ($data) {
            $this->_em->remove($data);
            $this->_em->flush();
        }
    }

}
