<?php

class Material_ApplyController extends Zend_Controller_Action
{
    private $_em;
    private $_material;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
    }

    public function indexAction()
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

        if(1)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }   
        echo "postdataAction";
    }
}
