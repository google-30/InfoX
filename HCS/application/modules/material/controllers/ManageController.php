<?php

define('UPLOAD_PATH', APPLICATION_PATH. '/data/uploads/materials/');
class Material_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_material;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        $this->_application = $this->_em->getRepository('Synrgic\Infox\Application');
        $this->_matappdata = $this->_em->getRepository('Synrgic\Infox\Matappdata');
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

        $id = $data->getId();
        // upload pic
        $this->storePic($id);

        $this->_redirect("material/manage");
    }    

    private function getSuppliers()
    {
        $this->view->suppliers = $this->_supplier->findAll();  
    }    

    private function storePic($id)
    {// http://www.w3schools.com/php/php_file_upload.asp
        
        echo "<br>";
        $newfile = "";   
        $uploadpath = UPLOAD_PATH;
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        if ((($_FILES["file"]["type"] == "image/gif")
                || ($_FILES["file"]["type"] == "image/jpeg")
                || ($_FILES["file"]["type"] == "image/jpg")
                || ($_FILES["file"]["type"] == "image/pjpeg")
                || ($_FILES["file"]["type"] == "image/x-png")
                || ($_FILES["file"]["type"] == "image/png"))
                && ($_FILES["file"]["size"] < 100000)
                && in_array($extension, $allowedExts))
        {
            if ($_FILES["file"]["error"] > 0)
            {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
            }
            else
            {
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
                */
                {
                    $newfile = $id . "." . $extension;
                    $picpath = $uploadpath . $newfile;
                    move_uploaded_file($_FILES["file"]["tmp_name"], $picpath);
                    echo "Stored in: " . $picpath;                    
                }
            }
        }
        else
        {
            //echo "Invalid file";
        }
        echo "<br>";   

        if($newfile != "")
        {
            $data = $this->_material->findOneBy(array('id'=>$id));
            $pic = "/materials/" . $newfile; 
            $data->setPic($pic);        
            $this->_em->persist($data);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
        }
    }

    public function applymaterialAction()
    {
        echo "applymaterialAction";
    }


    public function appmanageAction()
    {
        $maindata = $this->_application->findAll();        
        $this->view->maindata = $maindata;
    }
    
    public function appeditAction()
    {
        $id = $this->getParam("id");
        $appobj = $this->_application->findOneBy(array("id"=>$id));
        $this->view->application = $appobj;
        
        $matapps = $this->_matappdata->findBy(array("application"=>$appobj));
        $this->view->matapps = $matapps;
         
    }

    public function appdelAction()
    {
    
    }

    public function updatedataAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        if(0)
        {                
            var_dump($requests);
            return;
        }    

        $id = $requests['id'];
        $amount = $requests['amount'];
        $remark = $requests['remark'];
        $supplierid = $requests['supplierid'];
        $price = $requests['price'];
        // update matappdata, update application
        $matappobj = $this->_matappdata->findOneBy(array("id"=>$id));       
        $matappobj->setAmount($amount); 
        $matappobj->setRemark($remark);
        
        $supplier = $this->_supplier->findOneBy(array("id"=>$supplierid));
        $matappobj->setSupplier($supplier);
        $matappobj->setPrice($price); 
        $this->_em->persist($matappobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        } 

        $appobj = $matappobj->getApplication();      
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        } 

        echo "更新成功";

    }
    
}



