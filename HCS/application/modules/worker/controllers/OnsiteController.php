<?php

class Worker_OnsiteController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');

        /*
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_workerskill = $this->_em->getRepository('Synrgic\Infox\Workerskill');
        $this->_workercompanyinfo = $this->_em->getRepository('Synrgic\Infox\Workercompanyinfo');
        $this->_workerfamily = $this->_em->getRepository('Synrgic\Infox\Workerfamily');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        */

        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
        $this->_workerattendance = $this->_em->getRepository('Synrgic\Infox\Workerattendance');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
    }

    private function getworkerlist()
    {
        $requestsheet = $this->getParam("sheet","HC.C");     
        $sheetarr = array("HC.C","HT.C","HC.B","HT.B");
        $workerarr = array();
                    
        if(!in_array($requestsheet, $sheetarr))
        {
            $allworkers = $this->_workerdetails->findAll();
            foreach($allworkers as $worker)
            {
                $sheet = $worker->getSheet();
                if(!in_array($sheet, $sheetarr))
                {
                    $workerarr[] = $worker;
                }
            }            
        }
        else
        {
            $workerarr = $this->_workerdetails->findBy(array('sheet'=>$requestsheet));
        }

        $this->view->sheet = $requestsheet;        
        $this->view->sheetarr = $sheetarr;
        $this->view->maindata = $workerarr;
    }

    public function indexAction()
    {
        $this->getworkerlist();
    }

    public function index1Action()
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
        $workerobj = $this->_workerdetails->findOneBy(array("id"=>$id));  
        $this->view->worker = $workerobj ? $workerobj : null;

        $this->view->sites = $this->_site->findAll();
        $this->view->id = $id;

        $records = $this->_workeronsite->findBy(array("worker"=>$workerobj));
        $this->view->records = $records;        
    }

    public function onsiterecord1Action()
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
        $worker = $this->_workerdetails->findOneBy(array("id"=>$id));
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

    public function updaterecordAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    
        
        $id = $this->getParam("id", 0);
        $begin = $this->getParam("begindate", "");
        $end = $this->getParam("enddate", "");
        $siteid = $this->getParam("siteid", 0);

        $record = $this->_workeronsite->findOneBy(array("id"=>$id));
        $record->setBegindate(new DateTime($begin));
        $record->setEnddate(new DateTime($end));
        
        $site = $this->_site->findOneBy(array("id"=>$siteid));
        $record->setSite($site);

        $this->_em->persist($record);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }   

        echo "更新成功";   
    }

    public function deleterecordAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    
        
        $id=$this->getParam("id", 0);
        $record = $this->_workeronsite->findOneBy(array("id"=>$id));        
        $this->_em->remove($record);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }   

        echo "删除成功";        
    }

    public function attendancerecordAction()
    {
        $id = $this->getParam("id", 0);
        $workerobj = $this->_workerdetails->findOneBy(array("id"=>$id));  
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

        $worker = $this->_workerdetails->findOneBy(array("id"=>$id));

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

    public function updateattendancerecordAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    
        
        $id = $this->getParam("id", 0);
        $begin = $this->getParam("begindate", "");
        $end = $this->getParam("enddate", "");
        $days = $this->getParam("days", 0);
        $reason = $this->getParam("reason", "");
        $remark = $this->getParam("remark", "");

        $record = $this->_workerattendance->findOneBy(array("id"=>$id));
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

    public function deleteattendancerecordAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    
        
        $id=$this->getParam("id", 0);
        $record = $this->_workerattendance->findOneBy(array("id"=>$id));        
        $this->_em->remove($record);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }   

        echo "删除成功";
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
