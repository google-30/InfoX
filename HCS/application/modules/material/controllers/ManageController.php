<?php

include "InfoX/infox_common.php";
include "InfoX/infox_material.php";

define('UPLOAD_PATH', APPLICATION_PATH . '/data/uploads/materials/');

class Material_ManageController extends Zend_Controller_Action {

    private $_em;
    private $_material;

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        $this->_application = $this->_em->getRepository('Synrgic\Infox\Application');
        $this->_matappdata = $this->_em->getRepository('Synrgic\Infox\Matappdata');
        $this->_supplyprice = $this->_em->getRepository('Synrgic\Infox\Supplyprice');
        $this->_user = $this->_em->getRepository('Synrgic\User');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_humanresource = $this->_em->getRepository('Synrgic\Infox\Humanresource');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction() {
        $sheetarr = infox_material::getMaterialListSheets();

        $allmatsheet = "All";
        $sheetarr[] = $allmatsheet;

        $requestsheet = $this->getParam("sheet", $sheetarr[0]);

        if ($requestsheet != $allmatsheet) {
            $materialarr = $this->_material->findBy(array("sheet" => $requestsheet));
        } else {
            $materialarr = $this->_material->findAll();
        }

        $this->view->maindata = $materialarr;
        $this->view->sheetarr = $sheetarr;
        $this->view->sheet = $requestsheet;
    }

    private function getmateriallistNew() {
        $sheetarr = infox_material::getMaterialListSheets();

        $requestsheet = $this->getParam("sheet", $sheetarr[0]);
        $maintype = $this->_materialtype->findBy(array("typeeng" => $requestsheet));
        $subtypes = $this->_materialtype->findBy(array("main" => $maintype));
        $typestr = "";
        $typesarr = array();
        foreach ($subtypes as $type) {
            $typeid = $type->getId();
            $typestr .= $typeid . ",";
            $typesarr[] = $typeid;
        }
        $typestr .="0";
        //echo "typestr=$typestr";

        $query = $this->_em->createQuery(
                "select m from Synrgic\Infox\Material m where m.type in ($typestr)");
        $result = $query->getResult();
        $this->view->maindata = $result;
        //echo "result count=" . count($result);
        $this->view->sheetarr = $sheetarr;
        $this->view->sheet = $requestsheet;
    }

    public function addAction() {
        $this->getSuppliers();
        $this->getSupplyprice(0);
        //$this->getTypes();
        $this->getUnits();

        $this->view->sheets = infox_material::getMaterialListSheets();
    }

    public function editAction() {
        if (0) {
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            if (1)
                return;
        }
        $id = $this->_getParam("id");
        $material = $this->_material->findOneBy(array('id' => $id));

        $this->view->maindata = $material;
        $this->getSuppliers();
        //$this->getTypes();
        $this->view->supplypricearr = $supplypricearr = infox_material::getSupplypricesByMaterial($material);

        $this->view->sheets = infox_material::getMaterialListSheets();
    }

    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->getParam("id");

        $data = $this->_material->findOneBy(array('id' => $id));
        $this->_em->remove($data);
        $this->_em->flush();

        $this->_redirect("material/manage");
    }

    public function submitAction() {
        $this->turnoffview();

        if (0) {
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }

        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "");
        $name = $this->getParam("name", "");
        //$nameeng = $this->getParam("nameeng", "");
        $spec = $this->getParam("spec", "");
        $description = $this->getParam("description", "");
        $usage = $this->getParam("usage", "");
        //$typeid = $this->getParam("type", "0");
        $sheet = $this->getParam("sheetsel", "0");
        if ($sheet == "0") {
            $url = "/material/manage/";
            $this->_redirect($url);
        }

        $defsupplierid = $this->getParam("defsupplierid", "0");

        if ($mode == "Create") {
            $material = new \Synrgic\Infox\Material();
            
            $query = "select MAX(material.sn) from \Synrgic\Infox\Material material where material.sheet='$sheet'";
            $query = $this->_em->createQuery($query);
            $result = $query->getResult();
            //var_dump($result);
            $sn = $result[0][1] +1;
            //echo $sn;            //return;
            $material->setSn($sn);            
        } else {
            $material = $this->_material->findOneBy(array("id" => $id));
        }

        $material->setName($name);
        $material->setDescription($description);
        $material->setUsage($usage);
        $material->setSheet($sheet);

        $supplyprice = $this->_supplyprice->findOneBy(array("id" => $defsupplierid));
        if ($supplyprice) {
            $material->setSupplyprice($supplyprice);
        }

        /*
          $typeobj = $this->_materialtype->findOneBy(array("id" => $typeid));
          if (isset($typeobj)) {
          $material->setType($typeobj);
          }

          // find main type
          //$this->_materialtype->findOneBy();
          $maintypeobj = $typeobj->getMain();
          $maintypeeng = $maintypeobj->getTypeeng();
          $material->setSheet($maintypeeng);
         */
        $this->_em->persist($material);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $materialid = $id = $material->getId();

        // upload pic
        $this->storePic($id);

        // TODO: update supplyprice
        $supplypricearr = infox_material::getSupplypricesByMaterial($material);
        foreach ($supplypricearr as $tmp) {
            $id = $tmp->getId();
            $rateid = "rate" . strval($id);
            $rate = $this->getParam($rateid, 0);

            //$updateid = "update" . strval($id);
            //$update = $this->getParam($updateid, "now");

            $tmp->setRate($rate);
            //$tmp->setUpdate(new Datetime($update));
            $this->_em->persist($tmp);
        }

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $url = "/material/manage/edit/id/$materialid";
        $this->_redirect($url);
    }

    private function getSuppliers() {
        $this->view->suppliers = $this->_supplier->findAll();
    }

    private function getSupplyprice($id) {
        $supplyprice = array();
        $suppliers = $this->_supplier->findAll();

        $material = $this->_material->findOneBy(array("id" => $id));
        if ($material) {
            $supplyprice = $this->_supplyprice->findBy(array("material" => $material));
        }

        $this->view->supplyprice = $supplyprice;
    }

    private function getTypes() {
        $types = $this->_materialtype->findAll();
        $this->view->types = $types;
    }

    private function storePic($id) {// http://www.w3schools.com/php/php_file_upload.asp        
        echo "<br>Store Material Pic ... " . $_FILES["file"]["name"] . "<br>";

        $newfile = "";
        $uploadpath = UPLOAD_PATH;
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        if ((($_FILES["file"]["type"] == "image/gif") || ($_FILES["file"]["type"] == "image/jpeg") || ($_FILES["file"]["type"] == "image/jpg") || ($_FILES["file"]["type"] == "image/pjpeg") || ($_FILES["file"]["type"] == "image/x-png") || ($_FILES["file"]["type"] == "image/png"))
                //&& ($_FILES["file"]["size"] < 100000)
                && in_array($extension, $allowedExts)) {
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
                    $newfile = $id . "." . $extension;
                    $picpath = $uploadpath . $newfile;
                    move_uploaded_file($_FILES["file"]["tmp_name"], $picpath);
                    echo "Stored in: " . $picpath;
                }
            }
        } else {
            //echo "Invalid file";
        }
        echo "<br>";

        if ($newfile != "") {
            $data = $this->_material->findOneBy(array('id' => $id));
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

    private function turnoffview() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    private function getUnits() {
        $label = "info06";
        $values = $this->_miscinfo->findOneBy(array("label" => $label))->getValues();
        $this->view->units = explode(";", $values);
    }

    public function previewlistAction() {
        $this->_helper->layout->disableLayout();
        $this->getmateriallistNew();
    }

    public function supplypriceAction() {
        $id = $this->_getParam("id");
        $material = $this->_material->findOneBy(array('id' => $id));

        $this->view->maindata = $material;
        $this->getTypes();
        $this->view->supplypricearr = $supplypricearr = infox_material::getSupplypricesByMaterial($material);
    }

    public function postsupplypriceAction() {
        infox_common::turnoffView($this->_helper);
        if (0) {
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }

        $supplierid = $this->getParam("supplier", 0);
        $unit = $this->getParam("unit", "");
        $rate = $this->getParam("rate", 0);
        //$quantity = $this->getParam("quantity", 0);
        //$dodate = $this->getParam("dodate", "now");
        $materialid = $this->getParam("material", 0);

        if ($supplierid == "" || $supplierid == 0 || $unit == "" || $rate == 0) {
            echo "提交失败，请确认Supplier, Unit, Rate";
            return;
        }

        $spobj = new \Synrgic\Infox\Supplyprice();
        $supplier = $this->_supplier->findOneBy(array("id" => $supplierid));
        $spobj->setSupplier($supplier);
        $material = $this->_material->findOneBy(array("id" => $materialid));
        $spobj->setMaterial($material);
        $spobj->setUnit($unit);
        $spobj->setRate($rate);
        //$spobj->setQuantity($quantity);
        //$spobj->setUpdate(new Datetime($dodate));
        $this->_em->persist($spobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        echo "提交成功";
    }

}
