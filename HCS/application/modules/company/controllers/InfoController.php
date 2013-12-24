<?php

define('UPLOAD_PATH', APPLICATION_PATH . '/data/uploads/');

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

        $fullnamechs = $this->getParam("fullnamechs", "");
        $fullnameeng = $this->getParam("fullnameeng", "");

        $postring = $this->getParam("postring", "");
        $sheetprx = $this->getParam("sheetprx", "");

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

        $data->setFullnamechs($fullnamechs);
        $data->setFullnameeng($fullnameeng);

        $data->setPostring($postring);
        $data->setSheetprx($sheetprx);
        
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        $this->storePic($data->getId());
        
        $this->_redirect("/company/info");
    } 
    
    private function storePic($id) {
        // http://www.w3schools.com/php/php_file_upload.asp
        //$files = $this->_file;

        echo "<br>";
        $newfile = "";
        $uploadpath = UPLOAD_PATH;
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") 
                || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") 
                || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png")) 
                && ($_FILES["file"]["size"] < 100000) && in_array($extension, $allowedExts)) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            } else {
                echo "Upload: " . $_FILES["file"]["name"] . "<br>";
                echo "Type: " . $_FILES["file"]["type"] . "<br>";
                echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
                echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

                /*
                  if (file_exists($uploadpath . $_FILES["file"]["name"]))
                  {
                  echo $_FILES["file"]["name"] . " already exists. ";
                  }
                  else
                 */ {
                    $newfile = "companylogo" . $id . "." . $extension; 
                    $picpath = $uploadpath .  $newfile;
                    move_uploaded_file($_FILES["file"]["tmp_name"], $picpath);
                    echo "Stored in: " . $picpath;
                    
                }
            }
        } else {
            //echo "Invalid file";
        }
        echo "<br>";

        if ($newfile != "") {
            $cmyobj = $this->_companyinfo->findOneBy(array("id"=>$id));
            $cmyobj->setLogo($newfile);
            $this->_em->persist($cmyobj);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
        }
    }
}
