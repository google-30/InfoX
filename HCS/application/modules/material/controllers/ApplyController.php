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
        $this->_application = $this->_em->getRepository('Synrgic\Infox\Application');
        $this->_matappdata = $this->_em->getRepository('Synrgic\Infox\Matappdata');
        $this->_humanresource = $this->_em->getRepository('Synrgic\Infox\Humanresource');
        
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
        //$role = $this->getUserRole();
        //$this->view->role = $role;
        $this->getUserRole();
    }

    public function applymaterialsAction()
    {
        // user & role
        $this->getUserRole();

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

        $query = $this->_em->createQuery('select mtype from Synrgic\Infox\Materialtype mtype where mtype.id = mtype.main');
        $mains = $query->getResult();

        if($mainid && $subid)
        {// define main and sub
            $maintype = $this->_materialtype->findOneBy(array("id"=>$mainid));
            if($maintype)
            {
                $subs = $this->_materialtype->findBy(array("main"=>$maintype));
            }            
        }    
        else if($mainid)
        {   //change main type
            $maintype = $this->_materialtype->findOneBy(array("id"=>$mainid));
            if($maintype)
            {
                $subs = $this->_materialtype->findBy(array("main"=>$maintype));
                if($subs)
                {
                    $subfirstid = $subs[0]->getId();
                }
            }

            $subid = $subfirstid;
        }
        else if($subid)
        {   // change sub type
            // find subtype
            $subtype = $this->_materialtype->findOneBy(array("id"=>$subid));

            // find main of sub
            $maintype = $subtype->getMain();
            $mainid = $maintype->getid();

            $subs = $this->_materialtype->findBy(array("main"=>$maintype));           
        }
        else
        {
            $mainid = $mains[0]->getId();
            $maintype = $this->_materialtype->findOneBy(array("id"=>$mainid));
            $subs = $this->_materialtype->findBy(array("main"=>$maintype));
            $subid = $subs[0]->getId();    
        }

        $this->view->maintypes = $mains;
        $this->view->subtypes = $subs;
        $this->view->mainid = $mainid;
        $this->view->subid = $subid;

        $subtype = $this->_materialtype->findOneBy(array("id"=>$subid));
        $materialobjs = $this->_material->findBy(array("type"=>$subtype));
        $this->view->materials = $materialobjs;  

        $this->view->open = $this->getParam("open", 0);
    }

    public function appeditAction()
    {
        $this->appsheet();
    }
    
    private function appsheet()
    {
        // user & role
        $this->getUserRole();

        // application
        $id = $this->getParam("id", 0);
        $appobj = $this->_application->findOneBy(array("id"=>$id));       
        if($appobj)
        {
            $this->view->application = $appobj;
            $matapps = $this->_matappdata->findBy(array("application"=>$appobj));
            $this->view->matapps = $matapps;
        }

        // sites
        if($id == 0)
        {// create
            $siteid = $this->getParam("siteid", 0);
            $sitename = "";
            $siteparts = array();
            $siteobj = $this->_site->findOneBy(array("id"=>$siteid));
            echo "siteid=" . $siteid;        
            if($siteobj)
            {
                $parts = $siteobj->getParts();
                $siteparts = explode(";", $parts);
                $sitename = $siteobj->getName();
            }
        }
        else
        {// edit
            $siteid = $this->getParam("siteid", 0);
            if($siteid)
            {
                $siteobj = $this->_site->findOneBy(array("id"=>$siteid));                
            }
            else
            {
                $siteobj = $appobj->getSite();
                $siteid = $siteobj->getId();
            }
        }

        $sitename = "";
        $siteparts = array();
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
        //echo "siteid=" . $siteid;   

        //_materialtype
        $mainid = $this->getParam("mainid", 0);
        $subid = $this->getParam("subid", 0);

        $query = $this->_em->createQuery('select mtype from Synrgic\Infox\Materialtype mtype where mtype.id = mtype.main');
        $mains = $query->getResult();

        if($mainid && $subid)
        {// define main and sub
            $maintype = $this->_materialtype->findOneBy(array("id"=>$mainid));
            if($maintype)
            {
                $subs = $this->_materialtype->findBy(array("main"=>$maintype));
            }            
        }    
        else if($mainid)
        {   //change main type
            $maintype = $this->_materialtype->findOneBy(array("id"=>$mainid));
            if($maintype)
            {
                $subs = $this->_materialtype->findBy(array("main"=>$maintype));
                if($subs)
                {
                    $subfirstid = $subs[0]->getId();
                }
            }

            $subid = $subfirstid;
        }
        else if($subid)
        {   // change sub type
            // find subtype
            $subtype = $this->_materialtype->findOneBy(array("id"=>$subid));

            // find main of sub
            $maintype = $subtype->getMain();
            $mainid = $maintype->getid();

            $subs = $this->_materialtype->findBy(array("main"=>$maintype));           
        }
        else
        {
            $mainid = $mains[0]->getId();
            $maintype = $this->_materialtype->findOneBy(array("id"=>$mainid));
            $subs = $this->_materialtype->findBy(array("main"=>$maintype));
            $subid = $subs[0]->getId();    
        }

        $this->view->maintypes = $mains;
        $this->view->subtypes = $subs;
        $this->view->mainid = $mainid;
        $this->view->subid = $subid;

        $subtype = $this->_materialtype->findOneBy(array("id"=>$subid));
        $materialobjs = $this->_material->findBy(array("type"=>$subtype));
        $this->view->materials = $materialobjs;  

        $this->view->open = $this->getParam("open", 0);        
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
          ->field('sitepart', '工程部位')
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

        $ans = new Zend_Session_Namespace($this->nsName);
        $appmats = $ans->appmats;

        $id = $this->getParam("id", 0);
        unset($appmats[$id]);

        $ans->appmats = $appmats;

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
            if(array_key_exists('remark', $requests))
            { 
                $matobj->setRemark($requests['remark']);
            }
            $matobj->setLongname($requests['longname']);
            $matobj->setSitepart($requests['sitepart']);            
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
    
    // list all applications which has not been submitted; 
    public function applistAction()
    {
        $role = $this->getUserRole();
        $username = $this->getUserName();
    
        if($role == "leader")
        {//TODO: not working
            $querystr = "select app from Synrgic\Infox\Application app 
                        LEFT JOIN app.applicant applicant where app.status1 != '提交' and applicant.username='$username'";
        }    
        else if ($role == "staff")
        {
            $querystr = "select app from Synrgic\Infox\Application app where app.status1='未审核'";            
        }
        
        $query = $this->_em->createQuery($querystr);
        $result = $query->getResult();    
        //$maindata = $this->_application->findAll();
        $this->view->maindata = $result;       
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
        //echo "XXXXXXXXXX=" . $role;
        $this->view->role = $role;
        return $role;
    }    

    private function getUserSites()
    {
        $role = $this->getUserRole();
        $username = $this->getUserName();
        //$user = $this->_user->findOneBy(array("username"=>$username));
        $user = $this->_humanres->findOneBy(array("username"=>$username));
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
