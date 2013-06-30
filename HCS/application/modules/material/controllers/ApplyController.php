<?php

class Material_ApplyController extends Zend_Controller_Action
{
    private $_em;
    private $_material;
    private $_applysession;

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
                $this->_applysession = new Zend_Session_Namespace('applysession');
                //echo "applysession";
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

        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }   
        
        echo "postdataAction";

        
    }
}
