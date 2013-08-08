<?php

class Miscinfo_ManageController extends Zend_Controller_Action
{
    private $_em;

    public function init()
    {

        $this->_em = Zend_Registry::get('em');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction()
    {
        $infos = $this->_miscinfo->findAll();
        $this->view->infos = $infos;        
    }    

    public function addinfoAction()
    {
        $this->turnoffview();        
        if(0)
        {
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }              

        $namechs = $this->getParam("namechs", "");
        $nameeng = $this->getParam("nameeng", "");
    
        $data = new \Synrgic\Infox\Miscinfo(); 
        $data->setNamechs($namechs);
        $data->setNameeng($nameeng);
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        
        echo "添加成功";        
    }

    // this is a general method to add string type data
    // data stores like this: aaa;bbb;ccc;
    public function postinfoAction()
    {
        $this->turnoffview();        
        $requests = $this->getRequest()->getPost();
        if(0)
        {
            var_dump($requests);
            return;
        }           
        
        $label = $this->getParam("label", "");
        $namechs = $this->getParam("namechs", "");
        $nameeng = $this->getParam("nameeng", "");
        $count = intval($this->getParam("count", 0));

        $infoobj = $this->_miscinfo->findOneBy(array("label"=>$label));
        if(!$infoobj)
        {
            $infoobj = new \Synrgic\Infox\Miscinfo(); 
        }
        
        $infoobj->setLabel($label);
        $infoobj->setNamechs($namechs);
        $infoobj->setNameeng($nameeng);        

        $values="";
        for($i=0;$i<$count;$i++)
        {
            $j = $i+1;
            $key = "value" . $j;
            $value = $this->getParam($key, "");
            if($value != "")
            {
                $values .= $value . ";";
            }
        }
        //echo "values=" . $values;
        $infoobj->setValues($values);
        $this->_em->persist($infoobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $this->redirect("/miscinfo/manage");
    }

    public function postinfoAction1()
    {
        $this->turnoffview();        
        $requests = $this->getRequest()->getPost();
        if(0)
        {
            var_dump($requests);
            return;
        }              

        $id = $this->getParam("id", 0);
        $data = $this->_miscinfo->findOneBy(array("id"=>$id));
        if(!$data)
        {
            echo "更新失败";
            return;
        }

        $values = "";
        $count = $this->getParam("id", 0);                
        for($i=1; $i<$count; $i++)
        {
            $value = $this->getParam("value" . $i , "");            
            if($value!="")
            {
                $values .= $value . ";";
            }
        }    

        //echo "values=" . $values; return;

        // TODO: support change names    
        //$data->setNamechs($namechs);
        //$data->setNameeng($nameeng);

        if($values != "")
        {
            $data->setValues($values);
            $this->_em->persist($data);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
            
            //echo "更新成功";        
        }

        $this->redirect("/miscinfo/manage");
    }
    

    private function turnoffview()
    {  
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

}
