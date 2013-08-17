<?php

class Material_EmachineryController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_emachinery = $this->_em->getRepository('Synrgic\Infox\Emachinery');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction()
    {// get all materials in table
        $maindata = $this->_emachinery->findAll();
        $this->view->maindata = $maindata;
    }

    public function addAction()
    {
        $this->getEmachineryMaterials();
        $this->getSites();
        $this->getStatus();
    }

    public function editAction()
    {
        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            if(1) return;
        }
        $id = $this->_getParam("id", 0);
        $data = $this->_emachinery->findOneBy(array('id' => $id));
        $this->view->data = $data;

        $this->getEmachineryMaterials();
        $this->getSites();
        $this->getStatus();
    }

    public function deleteAction()
    {
        $this->turnoffview();
        $id = $this->_getParam("id");
        $data = $this->_emachinery->findOneBy(array('id' => $id));

        $this->_em->remove($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $this->redirect("/material/emachinery/");   
    }

    public function submitAction()
    {
        $this->turnoffview();

        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            if(1) return;
        }

        $id= $this->getParam("id", 0);
        $mode = $this->getParam("mode", "Create");

        $materialid = $this->getParam("material", 0);
        $purchase = $this->getParam("purchasedate", "");
        $sn = $this->getParam("sn", "");
        $status = $this->getParam("status", "");
        $remark = $this->getParam("remark", "");
        $siteid = $this->getParam("site", 0);

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Emachinery();
        }    
        else
        {
            $data = $this->_emachinery->findOneBy(array("id"=>$id));
        }    

        $material = $this->_material->findOneBy(array("id"=>$materialid));
        $data->setMaterial($material);
        $data->setPurchasedate(new DateTime($purchase));
        $data->setSn($sn);
        $data->setStatus($status);
        $data->setRemark($remark);
        $site = $this->_site->findOneBy(array("id"=>$siteid));
        $data->setSite($site);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }     

        $this->redirect("/material/emachinery/");   
    }

    private function getEmachineryMaterials()
    {
        // parent type
        $typeobj = $this->_materialtype->findOneBy(array("typechs"=>"机械"));
        if(!$typeobj)
        {
            echo "错误：类型不匹配，请检查！";
            return;
        }
        $parentid = $typeobj->getId();
       
        // children
        $typeobjs = $this->_materialtype->findBy(array("main"=>$typeobj));
        $ids = "";
        $idArr = array();
        foreach($typeobjs as $tmp)
        {
            $id = $tmp->getId();
            $ids .= $id . ",";
            $idArr[] = $id;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->add('select', 'm')
           ->add('from', 'Synrgic\Infox\Material m');
        $qb->add('where', $qb->expr()->in('m.type', $idArr));
        $query = $qb->getQuery();
        $result = $query->getResult();
        $this->view->materials = $result;
    }

    private function getSites()
    {
        $sites = $this->_site->findAll();
        $this->view->sites = $sites;
    }

    private function getStatus()
    {
        $statuses = $this->_miscinfo->findOneBy(array("label"=>"info05"))->getValues();
        $statusArr = explode(";", $statuses);
        $this->view->statusArr = $statusArr;
    }

    private function turnoffview()
    {  
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }    

}
