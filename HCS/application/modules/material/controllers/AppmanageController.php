<?php

class Material_AppmanageController extends Zend_Controller_Action
{
    private $_em;
    private $_material;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        $this->_application = $this->_em->getRepository('Synrgic\Infox\Application');
        $this->_matappdata = $this->_em->getRepository('Synrgic\Infox\Matappdata');
        $this->_supplyprice = $this->_em->getRepository('Synrgic\Infox\Supplyprice');
        $this->_user = $this->_em->getRepository('Synrgic\User');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_humanresource = $this->_em->getRepository('Synrgic\Infox\Humanresource');
    }

    public function indexAction()
    {
        $maindata = $this->_application->findAll();        
        $this->view->maindata = $maindata;
    }

    public function appdetailAction()
    {
        $id = $this->getParam("id");
        $appobj = $this->_application->findOneBy(array("id"=>$id));
        $this->view->application = $appobj; 

        $matapps = $this->_matappdata->findBy(array("application"=>$appobj));
        $this->view->matapps = $matapps;
        $this->view->role = $this->getUserRole();        
        $this->view->sites = $sites = $this->_site->findAll();
        $this->view->humanres = $this->_humanresource->findAll();
        
        $total=0;
        foreach($matapps as $tmp)
        {
            $amount = $tmp->getAmount();
            $price = $tmp->getPrice();
            
            $amount = $amount ? $amount : 0;
            $price = $price ? $price : 0;            
            $total += $amount * $price;
        }      
        $this->view->totalprice = $total;

        $siteobj = $appobj->getSite();
        if($siteobj)
        {
            $this->view->sitename = $siteobj->getName();
            $this->view->siteid = $siteobj->getId(); 

            $company = $siteobj->getCompany();
            $this->view->company = $company;
            if($company)
            {
                $cmynamechs = $company->getNamechs();
                $cmynameeng = $company->getNameeng();
                $cmyname = $cmynamechs . "/" . $cmynameeng;                
                $this->view->cmyname = $cmyname;
            }
        }
    }

    public function appeditAction()
    {
        $id = $this->getParam("id");
        $appobj = $this->_application->findOneBy(array("id"=>$id));
        $this->view->application = $appobj;
        
        $matapps = $this->_matappdata->findBy(array("application"=>$appobj));
        $this->view->matapps = $matapps;

        $matappsInSys = array();
        $matappsNotInSys = array();
        foreach($matapps as $tmp)
        {
            $insys = $tmp->getMaterialinsys();
            if($insys)
            {
                $matappsInSys[] = $tmp;
            }
            else
            {
                $matappsNotInSys[] = $tmp;
            }        
        }    
        
        $this->view->matappsInSys = $matappsInSys;
        $this->view->matappsNotInSys = $matappsNotInSys;

        $this->view->role = $this->getUserRole();
        
        $this->view->sites = $sites = $this->_site->findAll();
        $this->view->humanres = $this->_humanresource->findAll();
    }

    public function appdelAction()
    {
        $this->turnoffview();  

        $id = $this->getParam("id", 0);
        $appobj = $this->_application->findOneBy(array("id"=>$id));
        if($appobj)
        {
            // del matapp first
            $results = $this->_matappdata->findBy(array("application"=>$appobj));
            foreach($results as $tmp)
            {
                $this->_em->remove($tmp);
            }
            $this->_em->remove($appobj);
            $this->_em->flush();
        }

        $this->_redirect("material/manage/appmanage");        
    }

    public function appmatdelAction()
    {
        $this->turnoffview();
    
        $id = $this->getParam("id", 0);
        $appmatobj = $this->_matappdata->findOneBy(array("id"=>$id));

        $appid = 0;
        if($appmatobj)
        {
            $appobj = $appmatobj->getApplication();
            if($appobj)
            {
                $appid = $appobj->getId();
                $this->appUpdateByObject($appobj);
                //$appobj->setUpdatedate(new Datetime('now'));
                //$this->_em->persist($appobj);
            }

            $this->_em->remove($appmatobj);
            $this->_em->flush();
        }

        $url = "material/manage/appmanage";
        if($appid!=0)
        {
            $url = "/material/manage/appedit/id/" . $appid;
        }
        $this->_redirect($url);  
    }

    public function updatematappAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(1)
        {                
            var_dump($requests);
            return;
        }    

        $id = $requests['id'];
        $amount = $requests['amount'];
        //$remark = $requests['remark'];
        $supplierid = $requests['supplierid'];
        $price = $requests['price'];
        // update matappdata, update application
        $matappobj = $this->_matappdata->findOneBy(array("id"=>$id));       
        $matappobj->setAmount($amount); 
        $matappobj->setRemark($remark);
        
        $supplier = $this->_supplier->findOneBy(array("id"=>$supplierid));
        $matappobj->setSupplier($supplier);
        $matappobj->setPrice($price); 
        $this->_em->persist($matappobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        } 

        $appobj = $matappobj->getApplication();      
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        } 

        echo "更新成功";
    }
    
    public function submitmatappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $siteid = $this->getParam("siteid", 0);
        $applicantid = $this->getParam("applicantid", 0);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[0]);
        
        // TODO: update site - this function really needed? 
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));
        if($siteobj)
        {
            $appobj->setSite($siteobj);
        }            
        
        // update applicant
        $applicantobj = $this->_humanresource->findOneBy(array("id"=>$applicantid));
        //if($applicantobj)
        {
            $appobj->setApplicant($applicantobj);
        }  
          
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         
        
        echo "提交成功";
    }

    public function reviewmatappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[2]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         

        echo "该申请设成未审核状态";
    }

    public function rejectmatappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();//array("提交", "审核", "未审核", "批准", "退回");

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[4]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         

        echo "该申请已经退回";
    }

    public function approvematappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus2($statusArr[3]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         

        echo "该申请已经批准";
    }

    public function previewformAction()
    {
        $this->turnofflayout();

        $id = $this->getParam("id", 0);
        if(!$id)
        {
            $this->turnoffrenderer();
            echo "id, please.";
            return;
        }    

        $appobj = $this->_application->findOneBy(array("id"=>$id));
        $siteobj = $appobj->getSite();
        if($siteobj)
        {
            $cmyobj = $siteobj->getCompany();
            if($cmyobj)
            {
                // company info
                $this->view->cmyfullnameeng = $cmyfullnameeng = $cmyobj->getFullnameeng();
                $this->view->cmyaddr = $cmyaddr = $cmyobj->getAddress();
                $this->view->cmyphone = $cmyphone = $cmyobj->getPhone();
                $this->view->cmyfax = $cmyfax = $cmyobj->getFax();
                $this->view->cmyemail = $cmyemail = $cmyobj->getEmail();

                $cmycontact="Tel:" . $cmyphone . "&nbsp;&nbsp;Fax:" . $cmyfax . "&nbsp;&nbsp;Email:" . $cmyemail;
                $this->view->cmycontact=$cmycontact;
            }

            // site
            $this->view->sitename = $sitename = $siteobj->getName();
        }
        
        // date
        $date = new Datetime("now");
        $this->view->date = $date->format("Y-m-d");

        // requested/applied materials
        $this->view->matapps = $this->_matappdata->findBy(array("application"=>$appobj));

        // TODO: contact, manager

        // TODO: ref        
    }

    public function previeworderAction()
    {
        $this->turnofflayout();    
    }


    private function getStatusArr()
    {
        $statusArr = array("提交", "审核", "未审核", "批准", "退回");
        return  $statusArr;
    }

    private function getUsername()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity()->username;
            //echo "username=$username<br>";            
            return $username;
        }
        else
        {
            $ted = "Ted, u here~";
            //echo "username=$ted<br>";            
            return $ted;
        }             
    }    

    private function getUserRole()
    {
        $username = $this->getUsername();
        $user = $this->_user->findOneBy(array("username"=>$username));
        $role = $user ? $user->getRole() : "";
        return $role;
    }    

    private function turnoffview()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    private function turnofflayout()
    {
        $this->_helper->layout->disableLayout();
    }

    private function turnoffrenderer()
    {
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    private function getSuppliers()
    {
        $this->view->suppliers = $this->_supplier->findAll();  
    }    

    private function getSupplyprice($id)
    {
        $supplyprice = array();
        $suppliers = $this->_supplier->findAll();  

        $material = $this->_material->findOneBy(array("id"=>$id));
        if($material)
        {
            $supplyprice = $this->_supplyprice->findBy(array("material"=>$material));
        }

        $this->view->supplyprice = $supplyprice;
    }

    private function getTypes()
    {
        $types = $this->_materialtype->findAll();
        $this->view->types = $types;
    }

    private function appUpdateById($id)
    {
        $appobj = $this->_application->findOneBy(array("id"=>$id));
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);
        $this->_em->flush();
    }

    private function appUpdateByObject($appobj)
    {
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);
        $this->_em->flush();
    }

}



