<?php

class Worker_CustominfoController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');

        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerskill = $this->_em->getRepository('Synrgic\Infox\Workerskill');
        $this->_workercompanyinfo = $this->_em->getRepository('Synrgic\Infox\Workercompanyinfo');
        $this->_workerfamily = $this->_em->getRepository('Synrgic\Infox\Workerfamily');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
        $this->_workerattendance = $this->_em->getRepository('Synrgic\Infox\Workerattendance');

        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction()
    {
        $category = "worker";
        $infos = $this->_miscinfo->findBy(array("category"=>$category));
        $this->view->infos = $infos;
    }    

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

        $this->redirect("/worker/custominfo");
    }

    private function turnoffview()
    {  
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

}
