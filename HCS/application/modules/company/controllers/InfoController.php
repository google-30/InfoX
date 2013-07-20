<?php

class Company_InfoController extends Zend_Controller_Action
{
    private $_em;
    private $_supplier;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
    }

    public function indexAction()
    {
        $maindata = $this->_companyinfo->findAll();
        $this->view->maindata = $maindata;
    }   

    public function addAction()
    {
    } 

    public function editAction()
    {
        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $maindata = $this->_companyinfo->findOneBy(array("id"=>$id));
        $this->view->maindata = $maindata;
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
        $namechs = $this->getParam("namechs", "");
        $nameeng = $this->getParam("nameeng", "");
        $phone = $this->getParam("phone", "");
        $address = $this->getParam("address", "");
        $email = $this->getParam("email", "");
        $fax = $this->getParam("fax", "");
        $coregno = $this->getParam("coregno", "");
        $remark = $this->getParam("remark", "");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Companyinfo(); 
        }
        else
        {
            $data = $this->_companyinfo->findOneBy(array("id"=>$id));
        }
 
        $data->setUpdate(new Datetime("now"));       
        $data->setNamechs($namechs);
        $data->setNameeng($nameeng);
        $data->setPhone($phone);
        $data->setFax($fax);
        $data->setEmail($email);
        $data->setAddress($address);
        $data->setCoregno($coregno);
        $data->setRemark($remark);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        $this->_redirect("/company/info");
    } 
    

}
