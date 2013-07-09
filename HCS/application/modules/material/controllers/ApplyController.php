<?php

class Material_ApplyController extends Zend_Controller_Action
{
    private $_em;
    private $_material;
    private $_applysession;
    private $nsName = 'applysession';

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');

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
        $sites = $this->_site->findAll();
        $this->view->sites = $sites;  

        
    }

    public function applymaterialsAction()
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

    }

    public function submitAction()
    {

        
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
        
        $id = $requests["id"];
        $amount = $requests["amount"];        

        //echo "postdataAction";
        $ans = new Zend_Session_Namespace($this->nsName);

        if($id != "0")
        {//typechooser
            $matobj = $this->_material->findOneBy(array("id"=>$id));
            $name = $matobj->getName();
            $nameeng = $matobj->getNameeng();
            $longname = $name . "/" . $nameeng;
            $requests["longname"] = $longname;
            //$ans->appmats[$id] = $requests;

            /*
            $array = $ans->appmats;
            foreach ($array as $index => $value) {
                echo "aNamespace->$index = '$value';\n";
            }
            */
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
          ->field('remark', '补充说明')
          ->actionField(':action', "操作", '&nbsp;|&nbsp;')
          ->itemCountPerPage(30)
          ->paginatorEnabled(false)
          //->helper(new GridHelper_Supplier())
          ->data($appmats)
          ->action(':action', '删除', array( 'url'=>array('action'=>'delselection')));

        /*
        if(count($appmats) != 0)
        {
            echo '<input type="submit" value="提交至材料审核人员" onclick="submitselections()">';
        } 
        */   
    }

    public function delselectionAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $this->redirect("/material/apply/applymaterials");
    }
}
