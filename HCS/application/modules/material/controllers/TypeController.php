<?php

class Material_TypeController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
 
    }

    public function indexAction()
    {
        /*
        $mains = $this->_materialtype->findBy(array("main"=>null));        
        $this->view->mains = $mains;        
        */
        $query = $this->_em->createQuery(
                'select mtype from Synrgic\Infox\Materialtype mtype where mtype.id = mtype.main'
                );
        $mains = $query->getResult();
        $this->view->mains = $mains;

        $alltypes = $this->_materialtype->findAll();
        $this->view->alltypes = $alltypes;
    }

    public function addAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    

        $typechs = $this->getParam("typechs","");
        $typeeng = $this->getParam("typeeng","");
        $type = $this->getParam("type","0");

        $typeobj = new \Synrgic\Infox\Materialtype();
        $typeobj->setTypechs($typechs);
        $typeobj->setTypeeng($typeeng);

        if($type != "0")
        {
            $mainid = $this->getParam("maintype","0");
            $mainobj = $this->_materialtype->findOneBy(array("id"=>$mainid));
            if($mainobj)
            {
                $typeobj->setMain($mainobj);
            }
        }

        $this->_em->persist($typeobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }                

        if($type == "0")
        {
            $mainid = $typeobj->getId();
            $mainobj = $this->_materialtype->findOneBy(array("id"=>$mainid));
            if($mainobj)
            {
                $typeobj->setMain($mainobj);
            }
        }
        $this->_em->persist($typeobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }                
        
        $this->redirect("/material/type");
    }

    public function editAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(1)
        {                
            var_dump($requests);
            return;
        }    
        
            
    }
    
    public function posttypeAction()
    {
        $this->turnoffview();
        
        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }    

        $id = $this->getParam("id", "0");        
        $typeobj = $this->_materialtype->findOneby(array("id"=>$id));
        if(isset($typeobj))
        {
            $typechs = $typeobj->getTypechs();
            $typeeng = $typeobj->getTypeeng();
        }        
        else
        {
            return;    
        }
        $data["typechs"] = $typechs;
        $data["typeeng"] = $typeeng;
        $json = Zend_Json::encode($data);
        echo $json;        
         
    }        

    private function turnoffview()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

    }
}
