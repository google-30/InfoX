<?php

class Supplier_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_supplier;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
    }

    public function indexAction()
    {
        $suppliers = $this->_supplier->findAll();
        $this->view->suppliers = $suppliers;
    }   

    public function addAction()
    {
    } 

    public function editAction()
    {
        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $archive = $this->_supplier->findOneBy(array("id"=>$id));
        $this->view->maindata = $archive;
    } 

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->getParam("id");
        $data = $this->_supplier->findOneBy(array("id"=>$id));
    	$this->_em->remove($data);
        $this->_em->flush();        

        $this->_redirect("supplier/manage");    
    } 

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
 
        $requests = $this->getRequest()->getPost();
        if(0)
        {
            var_dump($requests);
            return;
        }        
       
        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "0");
        $name = $this->getParam("name");
        $remark = $this->getParam("remark", "");
        $business = $this->getParam("business", "");
        $officephone = $this->getParam("officephone", "");        
        $address = $this->getParam("address", "");
        //$contact = $this->getParam("contact", "");
        $email = $this->getParam("email", "");

        $fax = $this->getParam("fax", "");
        $attn = $this->getParam("attn", "");
        $attnphone = $this->getParam("attnphone", "");
        $postring = $this->getParam("postring", "");
        $fullname = $this->getParam("fullname", "");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Supplier(); 
        }
        else
        {
            $data = $this->_supplier->findOneBy(array("id"=>$id));
        }
 
        $data->setUpdate(new Datetime("now"));       
        $data->setName($name);
        $data->setRemark($remark);
        $data->setBusiness($business);
        $data->setOfficephone($officephone);
        //$data->setMobilephone($mobilephone);
        $data->setEmail($email);
        //$data->setContact($contact);
        $data->setAddress($address);

        $data->setFax($fax);
        $data->setAttn($attn);
        $data->setAttnphone($attnphone);
        $data->setPostring($postring);
        $data->setFullname($fullname);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        $this->_redirect("supplier/manage");
    } 
    

}
