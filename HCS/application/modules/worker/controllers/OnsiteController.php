<?php

class Worker_OnsiteController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerskill = $this->_em->getRepository('Synrgic\Infox\Workerskill');
        $this->_workercompanyinfo = $this->_em->getRepository('Synrgic\Infox\Workercompanyinfo');
        $this->_workerfamily = $this->_em->getRepository('Synrgic\Infox\Workerfamily');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');

        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
        $this->_workerattendance = $this->_em->getRepository('Synrgic\Infox\Workerattendance');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction()
    {
        //$this->view->maindata = $this->_worker->findAll();
        $query = $this->_em->createQuery(
            'select w, wc, ws from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws'
        );
        $result = $query->getResult();
        $this->view->workersdata = $result;
    }    

    public function onsiterecordAction()
    {
        $id = $this->getParam("id", 0);
        $workerobj = $this->_worker->findOneBy(array("id"=>$id));  
        $this->view->worker = $workerobj ? $workerobj : null;

        $this->view->sites = $this->_site->findAll();
        $this->view->id = $id;

        $records = $this->_workeronsite->findBy(array("worker"=>$workerobj));
        $this->view->records = $records;
        
    }

    public function addrecordAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    

        $id = $this->getParam("id", 0);
        $begin = $this->getParam("begin", "");
        $end = $this->getParam("end", "");
        $siteid = $this->getParam("site", 0);    
        $worker = $this->_worker->findOneBy(array("id"=>$id));
        $site = $this->_site->findOneBy(array("id"=>$siteid));

        $data = new \Synrgic\Infox\Workeronsite();
        $data->setWorker($worker);
        $data->setSite($site);
        $data->setBegindate(new DateTime($begin));
        $data->setEnddate(new DateTime($end));

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }      

        $this->redirect("/worker/onsite/onsiterecord/id/" . $id);  
    }

    public function attendancerecordAction()
    {
        $id = $this->getParam("id", 0);
        $workerobj = $this->_worker->findOneBy(array("id"=>$id));  
        $this->view->worker = $workerobj ? $workerobj : null;

        $this->view->id = $id;

        $records = $this->_workerattendance->findBy(array("worker"=>$workerobj));
        $this->view->records = $records;
                
        $label = "info03"; // for attendance
        $this->view->reasons = $this->getMiscInfo($label);

        $records = $this->_workerattendance->findBy(array("worker"=>$workerobj));
        $this->view->records = $records;
                
    }

    public function addattendancerecordAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    

        $id = $this->getParam("id", 0);
        $begin = $this->getParam("begin", "");
        $end = $this->getParam("end", "");
        $days = $this->getParam("days", 0.0);
        $reason = $this->getParam("reason", 0.0);
        $remark = $this->getParam("remark", 0.0);

        $worker = $this->_worker->findOneBy(array("id"=>$id));

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

    private function getMiscInfo($label)
    {
        $info = $this->_miscinfo->findOneBy(array("label"=>$label));
        $array = explode(";", $info->getValues());
        return $array;
    }

    private function turnoffview()
    {  
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }


}
