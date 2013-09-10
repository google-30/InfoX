<?php

class Infoxsys_ManageController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction()
    {
        $label = "01";
        $category = "infoxsys";
        $namechs = "使用说明";
        $nameeng = "User Manual";
        $content = "";

        $data = $this->_miscinfo->findOneBy(array("label"=>$label, "category"=>$category));        
        if($data)
        {
            $content = $data->getValues();
        }
    
        $this->view->content = $content;
    }

    public function editAction()
    {
        $label = "01";
        $category = "infoxsys";
        $namechs = "使用说明";
        $nameeng = "User Manual";
        $content = "";
        $data = $this->_miscinfo->findOneBy(array("label"=>$label, "category"=>$category));        
        if($data)
        {
            $content = $data->getValues();
        }
    
        $this->view->content = $content;
    }

    public function submitAction()
    {
        $this->turnoffview();
        $content = $this->getParam("content", "");

        $label = "01";
        $category = "infoxsys";
        $namechs = "使用说明";
        $nameeng = "User Manual";
        $data = $this->_miscinfo->findOneBy(array("label"=>$label, "category"=>$category));        
        if(!$data)
        {
            $data = new \Synrgic\Infox\Miscinfo();            
        }
        $data->setNamechs($namechs);
        $data->setNameeng($nameeng);        
        $data->setValues($content);
        $data->setLabel($label);
        $data->setCategory($category);
        
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }     

        //echo $content;
        $this->redirect("/infoxsys/manage/");
    }

    private function turnoffview()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }
    
    
}
