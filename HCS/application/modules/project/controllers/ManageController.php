<?php

class Project_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_supplier;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_humanres = $this->_em->getRepository('Synrgic\Infox\Humanresource');
    }

    public function indexAction()
    {
        $maindata = $this->_site->findAll();
        $this->view->maindata = $maindata;
    }   

    private function findPIC()
    {
        $leaders = $this->_humanres->findBy(array("position"=>"leader"));
        $this->view->leaders = $leaders;
        $managers = $this->_humanres->findBy(array("position"=>"manager"));
        $this->view->managers = $managers;
    }    

    public function addAction()
    {
        $this->findPIC();
    } 

    public function editAction()
    {
        $this->findPIC();

        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $maindata = $this->_site->findOneBy(array("id"=>$id));
        $this->view->maindata = $maindata;
    } 

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->getParam("id");
        $data = $this->_site->findOneBy(array("id"=>$id));
    	$this->_em->remove($data);
        $this->_em->flush();        

        $this->_redirect("project/manage");    
    } 

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
 
        $requests = $this->getRequest()->getPost();
        if(0)
        {
            var_dump($requests);
            return;
        }        
       
        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "0");
        $name = $this->getParam("name");
        $address = $this->getParam("address", "");
        $leader = $this->getParam("leader", "");
        $manager = $this->getParam("manager", "");
        $start = $this->getParam("start", "");
        $stop = $this->getParam("stop", "");
        $remark = $this->getParam("remark", "");
        $workerno = $this->getParam("workerno", "");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Site(); 
        }
        else
        {
            $data = $this->_site->findOneBy(array("id"=>$id));
        }
 
        $data->setName($name);
        $data->setAddress($address);
        $data->setLeader($this->_humanres->findOneBy(array("id"=>$leader)));
        $data->setManager($this->_humanres->findOneBy(array("id"=>$manager)));
        $data->setStart(new Datetime($start));
        $data->setStop(new Datetime($stop));
        $data->setRemark($remark);
        $data->setWorkerno($workerno);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        //$this->_redirect("project/manage");
    } 
}
