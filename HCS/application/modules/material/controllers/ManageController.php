<?php

class Material_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_material;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
    }

    public function indexAction()
    {// get all materials in table
        $materialobjs = $this->_material->findAll();
        $this->view->materials = $materialobjs;
    }

    public function addAction()
    {
        $this->getSuppliers();    
    }

    public function editAction()
    {
        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            if(1) return;
        }
        $id = $this->_getParam("id");
        $data = $this->_material->findOneBy(array('id' => $id));

        $this->view->maindata = $data;
        $this->getSuppliers();
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->getParam("id");

        $data = $this->_material->findOneBy(array('id' => $id));
        $this->_em->remove($data);
        $this->_em->flush();

        $this->_redirect("material/manage");
    }

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }   

        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "");
        $name = $this->getParam("name", "");
        $nameeng = $this->getParam("nameeng", "");
        $date = $this->getParam("date", "now");
        $price = $this->getParam("price", "0");
        $spec = $this->getParam("spec", "");
        $description = $this->getParam("description", "");
        $mtype = $this->getParam("mtype", "");
        $dtype = $this->getParam("dtype", "");

        $supplier = $this->getParam("supplier", "");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Material(); 
        }
        else
        {
            $data = $this->_material->findOneBy(array("id"=>$id));
        }        
                
        $data->setName($name);
        $data->setNameeng($nameeng);
        $data->setOnlinedate(new Datetime($date));
        $data->setPrice(floatval($price));
        $data->setSpec($spec);
        $data->setDescription($description);
        $data->setMacrotype($mtype);
        $data->setDetailtype($dtype);
        $supobj = $this->_supplier->findOneBy(array("id"=>$supplier));
        if(isset($supobj))  
        {
            $data->setSupplier($supobj);
        }
                    
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        $this->_redirect("material/manage");
    }    

    private function getSuppliers()
    {
        $this->view->suppliers = $this->_supplier->findAll();  
    }    

    public function applymaterialAction()
    {
        echo "applymaterialAction";
    }

}



