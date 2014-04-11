<?php
include 'InfoX/infox_common.php';
include 'InfoX/infox_worker.php';

class Worker_OnsiteController extends Zend_Controller_Action {

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
        $this->_workerattendance = $this->_em->getRepository('Synrgic\Infox\Workerattendance');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
    }

    public function indexAction() {
        /*
        $allsheets = array(0 => "All");
        $sheetarr = infox_worker::getSheetarr();
        $this->view->sheetarr = array_merge($allsheets, $sheetarr);
        */
        
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarrAll();
        $sheet = $this->getParam("sheet", "All");

        if ($sheet == "All") {
            $workerlist = infox_worker::getAllworkers();
        } else {
            $workerlist = infox_worker::getworkerlistbysheet($sheet);
        }
        $this->view->maindata = $workerlist;
        $this->view->sheet = $sheet;
    }

    public function onsiterecordAction() {
        $id = $this->getParam("id", 0);
        $workerobj = $this->_workerdetails->findOneBy(array("id" => $id));
        $this->view->worker = $workerobj ? $workerobj : null;

        $this->view->sites = $this->_site->findAll();
        $this->view->id = $id;

        $records = $this->_workeronsite->findBy(array("worker" => $workerobj));
        $this->view->records = $records;
    }

    public function addrecordAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $this->getParam("id", 0);
        $begin = $this->getParam("begin", "");
        $end = $this->getParam("end", "");
        $worker = $this->_workerdetails->findOneBy(array("id" => $id));
        $siteid = $this->getParam("site", 0);
        $site = $this->_site->findOneBy(array("id" => $siteid));
        $payment = $this->getParam("payment", "计时");

        $data = new \Synrgic\Infox\Workeronsite();
        $data->setWorker($worker);
        $data->setSite($site);
        $data->setBegindate(new DateTime($begin));
        $data->setEnddate(new DateTime($end));
        $data->setPayment($payment);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $this->redirect("/worker/onsite/onsiterecord/id/" . $id);
    }

    public function updaterecordAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $this->getParam("id", 0);
        $record = $this->_workeronsite->findOneBy(array("id" => $id));
        $this->storerecord($record);

        echo "更新成功";
    }

    private function storerecord($record) {
        $begin = $this->getParam("begindate", "");
        $end = $this->getParam("enddate", "");
        $siteid = $this->getParam("siteid", 0);
        $payment = $this->getParam("payment", "计时");

        $record->setBegindate(new DateTime($begin));
        $record->setEnddate(new DateTime($end));
        $site = $this->_site->findOneBy(array("id" => $siteid));
        $record->setSite($site);
        $record->setPayment($payment);

        $this->_em->persist($record);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    public function deleterecordAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $this->getParam("id", 0);
        $record = $this->_workeronsite->findOneBy(array("id" => $id));
        $this->_em->remove($record);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "删除成功";
    }

    public function attendancerecordAction() {
        $id = $this->getParam("id", 0);
        $workerobj = $this->_workerdetails->findOneBy(array("id" => $id));
        $this->view->worker = $workerobj ? $workerobj : null;

        $this->view->id = $id;

        $records = $this->_workerattendance->findBy(array("worker" => $workerobj));
        $this->view->records = $records;

        $label = "info03"; // for attendance
        $this->view->reasons = $this->getMiscInfo($label);

        $records = $this->_workerattendance->findBy(array("worker" => $workerobj));
        $this->view->records = $records;
    }

    public function addattendancerecordAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $this->getParam("id", 0);
        $begin = $this->getParam("begin", "");
        $end = $this->getParam("end", "");
        $days = $this->getParam("days", 0.0);
        $reason = $this->getParam("reason", 0.0);
        $remark = $this->getParam("remark", 0.0);

        $worker = $this->_workerdetails->findOneBy(array("id" => $id));

        $data = new \Synrgic\Infox\Workerattendance();
        $data->setWorker($worker);
        $data->setBegindate(new DateTime($begin));
        $data->setEnddate(new DateTime($end));
        $data->setDays($days);
        $data->setReason($reason);
        $data->setRemark($remark);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $this->redirect("/worker/onsite/attendancerecord/id/" . $id);
    }

    public function updateattendancerecordAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $this->getParam("id", 0);
        $begin = $this->getParam("begindate", "");
        $end = $this->getParam("enddate", "");
        $days = $this->getParam("days", 0);
        $reason = $this->getParam("reason", "");
        $remark = $this->getParam("remark", "");

        $record = $this->_workerattendance->findOneBy(array("id" => $id));
        $record->setBegindate(new DateTime($begin));
        $record->setEnddate(new DateTime($end));
        $record->setDays($days);
        $record->setReason($reason);
        $record->setRemark($remark);

        $this->_em->persist($record);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "更新成功";
    }

    public function deleteattendancerecordAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $this->getParam("id", 0);
        $record = $this->_workerattendance->findOneBy(array("id" => $id));
        $this->_em->remove($record);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "删除成功";
    }

    private function getMiscInfo($label) {
        $info = $this->_miscinfo->findOneBy(array("label" => $label));
        $array = explode(";", $info->getValues());
        return $array;
    }
}
