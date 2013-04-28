<?php

class Material_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_material;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
    }

    public function indexAction()
    {// get all materials in table
        $materialobjs = $this->_material->findAll();
        $this->view->materials = $materialobjs;
    }

    public function addAction()
    {
        
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
        $this->view->id = $id;
        $this->view->name = $data->getName();
        $this->view->price = $data->getPrice();
        $this->view->date = $data->getOnlinedate()->format('m/d/Y H:i:s');
        $this->view->description = $data->getDescription();

    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        if(0)
        {                
            var_dump($requests);
            return;
        }   

        foreach($requests as $key => $value)
        {
            if($value == 'true' && $key)
            {                
                $data = $this->_material->findOneBy(array('id' => $key));
                if($data)
                {    
                    //var_dump($data);
                    $this->_em->remove($data);
                }                
            } 
        }
        $this->_em->flush();
    }

    public function savedetailAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }                

        $id = $this->_getParam("id");
        $description  = $this->getParam("data");        
        $mode = $this->getParam("mode");  
        $name = $this->getParam("name");
        $date = $this->_getParam("date"); 
        $price = $this->_getParam("price");

        $data = null;
        if($mode == "Edit")
        {
            $data = $this->_material->findOneBy(array('id' => $id));
            echo "Data updated!\n";                         
        }
        else
        {
            $data = new \Synrgic\Infox\Material();
            echo "Data stored!"; 
        }        

        $data->setName($name);
        $data->setPrice($price);
        $data->setOnlinedate(new Datetime($date));
        $data->setDescription($description);

        $this->_em->persist($data);
        try {
            $this->_em->flush(); 
        } catch (Exception $e){
            var_dump($e);
            //return;
        }             

    }

    public function applymaterialAction()
    {
        echo "applymaterialAction";
    }
}



