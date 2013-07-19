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
        $this->_supplyprice = $this->_em->getRepository('Synrgic\Infox\Supplyprice');
        $this->_user = $this->_em->getRepository('Synrgic\User');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
    }

    public function indexAction()
    {// get all materials in table
        $materialobjs = $this->_material->findAll();
        $this->view->materials = $materialobjs;
    }

    public function addAction()
    {
        $this->getSuppliers();  
        $this->getSupplyprice(0);
        $this->getTypes();  
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
        $this->getSupplyprice($id);
        $this->getTypes();
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
        $spec = $this->getParam("spec", "");
        $description = $this->getParam("description", "");
        //$mtype = $this->getParam("mtype", "");
        //$dtype = $this->getParam("dtype", "");

        $usage = $this->getParam("usage", "");
        $unit = $this->getParam("unit", "");

        $typeid = $this->getParam("type", "0");

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
        $data->setUpdate(new Datetime("now"));
        //$data->setPrice(floatval($price));
        $data->setSpec($spec);
        $data->setDescription($description);
        $data->setMacrotype($mtype);
        $data->setDetailtype($dtype);
        $data->setUsage($usage);
        $data->setUnit($unit);
        /*
        $supobj = $this->_supplier->findOneBy(array("id"=>$supplier));
        if(isset($supobj))  
        {
            $data->setSupplier($supobj);
        }
        */

        $typeobj = $this->_materialtype->findOneBy(array("id"=>$typeid));
        if(isset($typeobj)) 
        {
            $data->setType($typeobj);
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

        // TODO: update supplyprice
        $suppliers = $this->_supplier->findAll();
        foreach($suppliers as $tmp)
        {
        
            $id = $tmp->getId();
            $priceid = "price" . strval($id);
            $price = $this->getParam($priceid, "0");

            $updateid = "update" . strval($id);
            $update = $this->getParam($updateid, "now");

            $priceobj = $this->_supplyprice->findOneBy(array("supplier"=>$tmp, "material"=>$data));
            if(is_null($priceobj))
            {
                $priceobj = new \Synrgic\Infox\Supplyprice();
            }
            $priceobj->setMaterial($data);
            $priceobj->setSupplier($tmp);
            $priceobj->setPrice(floatval($price));
            $priceobj->setUpdate(new Datetime($update));

            $this->_em->persist($priceobj);                        
  
        }

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

    private function getSupplyprice($id)
    {
        $supplyprice = array();
        $suppliers = $this->_supplier->findAll();  

        $material = $this->_material->findOneBy(array("id"=>$id));
        if($material)
        {
            $supplyprice = $this->_supplyprice->findBy(array("material"=>$material));
        }

        $this->view->supplyprice = $supplyprice;
    }

    private function getTypes()
    {
        $types = $this->_materialtype->findAll();
        $this->view->types = $types;
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

        $this->view->role = $this->getUserRole();
         
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
    
    public function submitmatappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[0]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         
        
        echo "提交成功";
    }

    public function reviewmatappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[2]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         

        echo "该申请设成未审核状态";
    }

    public function rejectmatappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();//array("提交", "审核", "未审核", "批准", "退回");

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[4]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         

        echo "该申请已经退回";
    }

    public function approvematappsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus2($statusArr[3]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }         

        echo "该申请已经批准";
    }

    private function getStatusArr()
    {
        $statusArr = array("提交", "审核", "未审核", "批准", "退回");
        return  $statusArr;
    }

    private function getUsername()
    {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity()->username;
            //echo "username=$username<br>";            
            return $username;
        }
        else
        {
            $ted = "Ted, u here~";
            //echo "username=$ted<br>";            
            return $ted;
        }             
    }    

    private function getUserRole()
    {
        $username = $this->getUsername();
        $user = $this->_user->findOneBy(array("username"=>$username));
        $role = $user ? $user->getRole() : "";
        return $role;
    }    

}



