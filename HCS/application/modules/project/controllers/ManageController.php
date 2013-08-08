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
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_role = $this->_em->getRepository('Synrgic\Infox\Role');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_application = $this->_em->getRepository('Synrgic\Infox\Application');

        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction()
    {
        $maindata = $this->_site->findAll();
        $this->view->maindata = $maindata;
    }   

    public function addAction()
    {
        $this->findLeaders();
        $this->getCompanyinfo();
        $this->getGeneralContractors();
        $this->getSiteproperties();
        $this->getPermission1();
    } 

    public function editAction()
    {
        $this->findLeaders();
        $this->getCompanyinfo();
        $this->getGeneralContractors();
        $this->getSiteproperties();
        $this->getPermission1();

        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $maindata = $this->_site->findOneBy(array("id"=>$id));
        $this->view->maindata = $maindata;
        /*    
        $query = $this->_em->createQuery(
        'select w, wc.companylabel, wc.hwage, ws.worktype, ws.worklevel,
        (select site.name from Synrgic\Infox\Site site where site.id = wc.site) as sitename
        from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws 
        where wc.site = ' . $id
         );
        */    
        $query = $this->_em->createQuery(
        'select w.nameeng, w.namechs, w.fin, wc.companylabel, wc.hwage, ws.worktype, ws.worklevel,
        (select site.name from Synrgic\Infox\Site site where site.id = wc.site) as sitename
        from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws 
        where wc.site = ' . $id
         );
        $result = $query->getResult();
        $this->view->workers = $result;                
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
        if(0) { var_dump($requests); return; }        
       
        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "0");
        $name = $this->getParam("name");
        $address = $this->getParam("address", "");        
        $manager = $this->getParam("manager", 0);
        $start = $this->getParam("start", "");
        $stop = $this->getParam("stop", "");
        $remark = $this->getParam("remark", "");
        $workerno = $this->getParam("workerno", "");
        $company = $this->getParam("company", 0);
        $contractor = $this->getParam("contractor", "");
        $property = $this->getParam("property", "");

        $leadersArr = $this->getParam("leaders", null);
        $leadersStr = "";
        if($leadersArr)
        {
            $leadersStr = implode(";", $leadersArr);
        }        

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
        $data->setManager($this->_humanres->findOneBy(array("id"=>$manager)));
        $data->setStart(new Datetime($start));
        $data->setStop(new Datetime($stop));
        $data->setRemark($remark);
        $data->setWorkerno($workerno);

        //$data->setLeader($this->_humanres->findOneBy(array("id"=>$leader)));
        $data->setLeaders($leadersStr);
    
        $companyobj = $this->_companyinfo->findOneBy(array("id"=>intval($company)));
        if(isset($companyobj))
        {
            $data->setCompany($companyobj);
        }

        $data->setContractor($contractor);
        $data->setProperty($property);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        $this->_redirect("project/manage");
    } 

    public function sitedetailAction()
    {
        $this->findLeaders();

        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $siteobj = $this->_site->findOneBy(array("id"=>$id));
        $this->view->maindata = $siteobj;

        $query = $this->_em->createQuery(
        'select w.nameeng, w.namechs, w.fin, wc.companylabel, wc.hwage, ws.worktype, ws.worklevel,
        (select site.name from Synrgic\Infox\Site site where site.id = wc.site) as sitename
        from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws 
        where wc.site = ' . $id
         );
        $result = $query->getResult();
        $this->view->workers = $result;

        $this->view->applications = $this->_application->findBy(array("site"=>$siteobj));

        // leaders names
        $namesStr = "";
        $selLeadersIdStr = $siteobj->getLeaders();
        if($selLeadersIdStr)
        {
            $selLeadersIdArr = explode(";", $selLeadersIdStr);
            foreach($selLeadersIdArr as $tmp)
            {
                $data = $this->_humanres->findOneBy(array("id"=>$tmp));
                $name = $data ? $data->getName() : "&nbsp;";
                $namesStr .= $name .  "；&nbsp;";
            } 
        }       
        $this->view->leaders = $namesStr;
    }

    private function findLeaders()
    {
        $leaderrole = $this->_role->findOneBy(array("role"=>"leader"));        
        $leaders = $this->_humanres->findBy(array("role"=>$leaderrole));
        $this->view->leaders = $leaders;
        //$managers = $this->_humanres->findBy(array("position"=>"manager"));
        //$this->view->managers = $managers;
    }    

    private function getCompanyinfo()
    {
        $companyinfos = $this->_companyinfo->findAll();
        $this->view->companyinfos = $companyinfos;
    }

    public function addpartAction()
    {
        $this->turnoffview();

        $id = $this->getParam("id", 0);
        $partname = $this->getParam("partname", "");
        
        $data = $this->_site->findOneBy(array("id"=>$id));
        $parts = $data->getParts();
        $newparts = $partname . ";" . $parts ;
        $data->setParts($newparts);
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        
        
        echo "添加成功";
    }

    public function delpartAction()
    {
        $this->turnoffview();

        $id = $this->getParam("id", 0);
        $delpart = $this->getParam("delpart", "");

        $data = $this->_site->findOneBy(array("id"=>$id));
        $parts = $data->getParts();

        //$partsarr = explode(";", $parts);
        $pos = strpos($parts, $delpart);
        if($pos===false)
        {
            echo "fail to find this part name";
            return;
        }

        $newparts = substr($parts, 0, $pos) . substr($parts, $pos+strlen($delpart)+1);
        //echo $newparts;  
        $data->setParts($newparts);
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        
        
        echo "删除成功";                               
    }

    private function turnoffview()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    public function partsdefineAction()
    {
        //$this->turnoffview();
        $this->_helper->layout->disableLayout();   
    }

    private function getGeneralContractors()
    {
        $label = "info01"; // ... use info01 to label contractor info
        
        $infoobj = $this->_miscinfo->findOneBy(array("label"=>$label));
        $values = $infoobj ? $infoobj->getValues() : "";
        $valueArr = ($values != "") ? explode(";", $values) : array();

        $this->view->contractors = $valueArr;
    }

    private function getSiteproperties()
    {
        $label = "info02";
        
        $infoobj = $this->_miscinfo->findOneBy(array("label"=>$label));
        $values = $infoobj ? $infoobj->getValues() : "";
        $valueArr = ($values != "") ? explode(";", $values) : array();

        $this->view->siteproperties = $valueArr;
    }

    // allow or not leader to apply materials 
    private function getPermission1()
    {
        $permission1arr = array(0=>"禁止材料申请",1=>"允许材料申请");
        $this->view->permission1arr = $permission1arr;
    }

}
