<?php

class Material_ApplyController extends Zend_Controller_Action
{
    private $_em;
    private $_material;
    private $_applysession;
    private $nsName = 'applysession';
    private $_humanres;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_humanres = $this->_em->getRepository('Synrgic\Infox\Humanresource');
        $this->_user = $this->_em->getRepository('Synrgic\User');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
        
        $nsName = 'applysession';
        if (Zend_Session::namespaceIsset($nsName)) {
            //echo $nsName.' exists';
        }
        else
        {
            try {
                $this->_applysession = new Zend_Session_Namespace($nsName);
                //echo "applysession";
                $this->_applysession->testkey = "1";
                $this->_applysession->appmats = array();
            } catch (Zend_Session_Exception $e) {
                echo 'Cannot instantiate this namespace since applysession was created\n';
            }
        }
    }

    public function indexAction()
    {
        //$sites = $this->_site->findAll();
        //$this->view->sites = $sites;     
        $role = $this->getUserRole();        
        //if($role=="leader")
        {
            $this->view->role = $role;
        }
    }

    public function applymaterialsAction1()
    {
        $macro = $this->_getParam("macro", "mechanic");
        $this->view->macro = $macro;                
        $this->view->macrotypes= array("mechanic", "material");

        $machdetails = array("heavy", "electronic");
        $matedetails = array("consumable", "building");
        $macroarr = array();
        $macroarr["mechanic"] = $machdetails;
        $macroarr["material"] = $matedetails;    

        $detailtypes = $macroarr[$macro];
        $this->view->detailtypes = $detailtypes;
       
        $detail = $this->_getParam("detail");
        $detail = ($detail=="") ? $detailtypes[0] : $detail;          
        $this->view->detail = $detail;
        //echo "$detail";  

        $materialobjs = $this->_material->findBy(array("macrotype"=>$macro, "detailtype"=>$detail));
        $this->view->materials = $materialobjs;  

        // sites
        $sites = $this->getUserSites();
        $this->view->sites = $sites;
    }

    public function applymaterialsAction()
    {
        // sites
        $siteid = $this->getParam("siteid", 0);
        $sitename = "";
        $siteparts = array();
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));        
        if($siteobj)
        {
            $parts = $siteobj->getParts();
            $siteparts = explode(";", $parts);
            $sitename = $siteobj->getName();
        }
        $sites = $this->getUserSites();

        $this->view->siteid = $siteid;
        $this->view->sitename = $sitename;        
        $this->view->siteparts = $siteparts;
        $this->view->sites = $sites;                

        //_materialtype
        $mainid = $this->getParam("mainid", 0);
        $subid = $this->getParam("subid", 0);
        
        // find main types
        $query = $this->_em->createQuery(
                'select mtype from Synrgic\Infox\Materialtype mtype where mtype.id = mtype.main'
                );
        $mains = $query->getResult();
        $this->view->maintypes = $mains;
        $maintype = $this->_materialtype->findOneBy(array("id"=>$mainid));
        /*
        $query = $this->_em->createQuery(
                'select mtype from Synrgic\Infox\Materialtype mtype where mtype.id = mtype.main'
                );
        $subs = $query->getResult();
        $this->view->subtypes = $subs;
        */
        if($maintype)
        {
            $this->view->subtypes = $this->_materialtype->findOneBy(array("main"=>$maintype));
        }
        
        /*
        $macro = $this->_getParam("macro", "mechanic");
        $this->view->macro = $macro;                
        $this->view->macrotypes= array("mechanic", "material");

        $machdetails = array("heavy", "electronic");
        $matedetails = array("consumable", "building");
        $macroarr = array();
        $macroarr["mechanic"] = $machdetails;
        $macroarr["material"] = $matedetails;    

        $detailtypes = $macroarr[$macro];
        $this->view->detailtypes = $detailtypes;
       
        $detail = $this->_getParam("detail");
        $detail = ($detail=="") ? $detailtypes[0] : $detail;          
        $this->view->detail = $detail;
        //echo "$detail";  

        $materialobjs = $this->_material->findBy(array("macrotype"=>$macro, "detailtype"=>$detail));
        $this->view->materials = $materialobjs;  
        */
    }

    
    public function postdataAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
  
        $requests = $this->getRequest()->getPost();
        if(0)
        {  
            var_dump($requests);
            return;
        }   
        
        //echo "postdataAction";        
        $id = $requests["id"];
        $ans = new Zend_Session_Namespace($this->nsName);

        if($id != "0")
        {//typechooser
            $matobj = $this->_material->findOneBy(array("id"=>$id));
            $name = $matobj->getName();
            $nameeng = $matobj->getNameeng();
            $longname = $name . "/" . $nameeng;
            $requests["longname"] = $longname;
        }
        else
        {//manualinput
            $id = rand(1000000,2000000);
            //echo "manualinput, id=$id\n";
            $requests["id"] = $id;
            
        }
        $ans->appmats[$id] = $requests;
    }

    public function getselectionsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        //echo "getselectionsAction";
        $ans = new Zend_Session_Namespace($this->nsName);
        $appmats = $ans->appmats;

        echo $this->view->grid("matlist", true)
          ->field('id','材料编号')
          ->field('longname','材料名称')
          ->field('amount', '数量')
          ->field('remark', '工程部位')
          ->actionField(':action', "操作", '&nbsp;|&nbsp;')
          ->itemCountPerPage(30)
          ->paginatorEnabled(false)
          //->helper(new GridHelper_Supplier())
          ->data($appmats)
          ->action(':action', '删除', array( 'url'=>array('action'=>'delselection')));
    }

    public function delselectionAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $this->redirect("/material/apply/applymaterials");
    }
    
    public function submitselectionsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $ans = new Zend_Session_Namespace($this->nsName);
        $appmats = $ans->appmats;        
        if(count($appmats) == 0)
        {
            echo "请选择材料，再进行提交";
            return;    
        }        

        // get site
        $requests = $this->getRequest()->getPost();
        if(0)
        {  
            var_dump($requests);
            return;
        }                  
        $siteid = $requests["siteid"];
        
        $statusArr = array("提交", "审核", "未审核", "批准", "退回");
        // step1. create application
        $appobj = new \Synrgic\Infox\Application(); 
        $appobj->setCreatedate(new Datetime("now"));
        $appobj->setUpdatedate(new Datetime("now"));
        $appobj->setStatus0($statusArr[0]);
        $appobj->setStatus1($statusArr[2]);
        $appobj->setStatus2($statusArr[2]);

        // applicant
        $username = $this->getUserName();
        $curuser = $this->_humanres->findOneBy(array("username"=>$username));
        if(isset($curuser))
        {
            $appobj->setApplicant($curuser);
        }        

        // site
        $site = $this->_site->findOneBy(array("id"=>$siteid));        
        if($site)
        {
            $appobj->setSite($site);
        }
        
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }       
        //echo "Created Application\n";
            
        // step2. insert into infox_matappdata        
        foreach ($appmats as $id => $requests) {            
            //echo "id=$id\n";
            $matobj = new \Synrgic\Infox\Matappdata();
            $matobj->setApplication($appobj);
            $matobj->setMaterialid($id);
            $insys = (intval($id) < 1000000) ? true: false;
            $matobj->setMaterialinsys($insys);
            $matobj->setAmount($requests['amount']);
            $matobj->setRemark($requests['remark']);
            $matobj->setLongname($requests['longname']);            
            $this->_em->persist($matobj);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }               
        }
        
        // step3. truncate array
        $ans->appmats = array();
        
        // step4. reload page
        //$this->redirect("/material/apply/applymaterials");        
        
        echo "提交申请成功";
    } 
    
    private function getUserName()
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
        $username = $this->getUserName();
        $user = $this->_user->findOneBy(array("username"=>$username));
        $role = $user ? $user->getRole() : "";
        return $role;
    }    

    private function getUserSites()
    {
        $role = $this->getUserRole();
        $username = $this->getUserName();
        $user = $this->_user->findOneBy(array("username"=>$username));

        if($role == "leader")
        {
            $sites = $this->_site->findBy(array("leader"=>$user));
        }
        else
        {
            $sites = $this->_site->findAll();
        }

        //$this->view->sites = $sites;
        return $sites;
    }

}
