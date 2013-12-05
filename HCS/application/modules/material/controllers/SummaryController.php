<?php

set_time_limit(0);
include 'InfoX/infox_common.php';
include 'InfoX/infox_material.php';
include 'InfoX/infox_project.php';

class Material_SummaryController extends Zend_Controller_Action {

    private $_files;

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
        $this->_supplyprice = $this->_em->getRepository('Synrgic\Infox\Supplyprice');
    }

    public function indexAction() {

        $this->view->sheetarr = $sheetarr = infox_material::getSummarySheets();
        $this->view->sheet = $sheet = $this->getParam("sheet", $sheetarr[0]);

        $this->view->maindata = infox_material::getMaterialsBySheet($sheet);
    }

    public function submitAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        // upload excel
        define('UPLOAD_TMP_PATH', APPLICATION_PATH . '/data/uploads/');
        $uploadpath = UPLOAD_TMP_PATH;
        $filepath = "";
        $allowedExts = array("xls", "xlsx");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        if (in_array($extension, $allowedExts)) {
            if ($_FILES["file"]["error"] > 0) {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
                return;
            } else {
                echo "Upload: " . $_FILES["file"]["name"] . "<br>";
                echo "Type: " . $_FILES["file"]["type"] . "<br>";
                echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
                echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

                if (file_exists($uploadpath . $_FILES["file"]["name"])) {
                    echo $_FILES["file"]["name"] . " already exists.<br>";
                }

                $filepath = $uploadpath . $_FILES["file"]["name"];
                move_uploaded_file($_FILES["file"]["tmp_name"], $filepath);
                echo "Stored in:$filepath<br>";
            }
        } else {
            echo "Error: Invalid file, 请上传xls, xlsx后缀文件<br>";
            return;
        }

        // load data from excel
        include 'PHPExcel/IOFactory.php';

        $inputFileName = $filepath;
        echo 'Loading file ', pathinfo($inputFileName, PATHINFO_BASENAME), ' using IOFactory to identify the format<br>';
        //$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

        $inputFileType = 'Excel2007'; //'Excel5';
        /**  Create a new Reader of the type defined in $inputFileType  * */
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        /* reduce memory consumption */
        $objReader->setReadDataOnly(true);

        if (1) {
            $sheetarr = infox_material::getSummarySheets();
        } else {
            $sheetarr = array("Punggol",);
        }

        $objWorksheet = $objReader->setLoadSheetsOnly($sheetarr);
        /**  Load $inputFileName to a PHPExcel Object  * */
        $objPHPExcel = $objReader->load($inputFileName);
        echo '<hr />';

        foreach ($sheetarr as $sheetname) {
            $siteid = infox_project::createSiteByName($sheetname);
            echo $siteid . "--"; //continue; 
            
            $objWorksheet = $objPHPExcel->setActiveSheetIndexByName($sheetname);
            if ($objWorksheet) {
                $this->storeDetails($sheetname, $objWorksheet);
            }
        }

        $this->redirect("/material/summary/");
    }

    private function storeDetails($sheetname, $objWorksheet) {

        $i = 0;
        foreach ($objWorksheet->getRowIterator() as $row) {
            if (++$i == 1) {   // ignore the first row
                continue;
            }

            $record = new \Synrgic\Infox\Ordermaterialsummaryraw();

            $cell = $objWorksheet->getCell("A" . $i);
            $valuea = $value = $cell->getFormattedvalue();
            $record->setSn(trim($value));

            $cell = $objWorksheet->getCell("B" . $i);
            $valueb = $value = $cell->getFormattedvalue();
            $record->setCatalog(trim($value));

            if ($valuea == "" && $valueb == "") {// blank row
                breaK;
            }

            $cell = $objWorksheet->getCell("C" . $i);
            $value = $cell->getFormattedvalue();
            $record->setItemc(trim($value));

            $cell = $objWorksheet->getCell("D" . $i);
            $value = $cell->getFormattedvalue();
            $record->setIteme(trim($value));

            $cell = $objWorksheet->getCell("E" . $i);
            $value = $cell->getFormattedvalue();
            $record->setUnit(trim($value));

            $cell = $objWorksheet->getCell("F" . $i);
            $value = $cell->getFormattedvalue();
            $record->setQty(trim($value));

            $cell = $objWorksheet->getCell("G" . $i);
            $value = $cell->getFormattedvalue();
            $record->setPrice(trim($value));

            $cell = $objWorksheet->getCell("H" . $i);
            $value = $cell->getFormattedvalue();
            $record->setOrderdate(new Datetime(date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP(trim($value)))));

            $cell = $objWorksheet->getCell("I" . $i);
            $value = $cell->getFormattedvalue();
            $record->setSupplier(trim($value));

            $cell = $objWorksheet->getCell("J" . $i);
            $value = $cell->getFormattedvalue();
            $record->setTotalamount(trim($value));

            $cell = $objWorksheet->getCell("K" . $i);
            $value = $cell->getFormattedvalue();
            $record->setWorkerqty(trim($value));

            $cell = $objWorksheet->getCell("L" . $i);
            $value = $cell->getFormattedvalue();
            $record->setRemark(trim($value));

            $record->setSheet($sheetname);

            $this->_em->persist($record);
        }

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    private function storeDetails1($sheetname, $objWorksheet) {
        //$datecolumns = array(7,);

        $this->storeSupplier($objWorksheet);
        // step1 - supplier  
        /*
          $i=0;
          $supplierArr = array();
          foreach ($objWorksheet->getRowIterator() as $row)
          {
          if(++$i == 1)
          {   // ignore the first row
          continue;
          }

          $cell = $objWorksheet->getCell("K".$i);
          $valuek = $cell->getFormattedvalue();
          if($valuek != "" )
          {
          $name = trim($valuek);
          $supplier = $this->_supplier->findOneBy(array("name"=>$name));
          if($supplier)
          {
          //echo "supplier already there.<br>";
          }
          else if(!in_array($name, $supplierArr))
          {
          $supplierArr[] = $name;
          }
          }
          }

          foreach($supplierArr as $tmp)
          {
          $supplier = new \Synrgic\Infox\Supplier();
          $supplier->setName($tmp);
          $this->_em->persist($supplier);
          }

          try {
          $this->_em->flush();
          } catch (Exception $e) {
          var_dump($e);
          return;
          }
          //return;
          // supplier done
         */

        // sub type
        $i = 0;
        $subtypeArr = array();
        foreach ($objWorksheet->getRowIterator() as $row) {
            if (++$i == 1) {   // ignore the first row
                continue;
            }

            $cell = $objWorksheet->getCell("B" . $i);
            $valueb = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("C" . $i);
            $valuec = $cell->getFormattedvalue();

            // this means a sub type
            //if($valueb!="" && $valuec!="")
            if ($valueb != "") {
                $typeeng = trim($valueb);
                $typechs = trim($valuec);

                $typeobj = $this->_materialtype->findOneBy(array("typeeng" => $typeeng, "typechs" => $typechs));
                if ($typeobj) {
                    //echo "sub type already there.<br>";
                } else {
                    $sub = array($typeeng, $typechs);
                    if (!in_array($sub, $subtypeArr)) {
                        $subtypeArr[] = $sub;
                    }
                }
            }
            // sub type done            
        }

        $maintype = $this->_materialtype->findOneBy(array("typeeng" => $sheetname));
        foreach ($subtypeArr as $tmp) {
            $typeeng = $tmp[0];
            $typechs = $tmp[1];

            $typeobj = new \Synrgic\Infox\Materialtype();
            $typeobj->setTypechs($typechs);
            $typeobj->setTypeeng($typeeng);
            $typeobj->setMain($maintype);
            $this->_em->persist($typeobj);
        }
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        //return; 
        // sub type done

        $subtype = null;
        $subtypeflag = false;  // subtype found
        $indexarr = array("B", "C", "D", "E", "F", "G", "H", "I", "J", "K",);
        $nameenglast = "";
        $namelast = "";
        $materialArr = array();
        $i = 0;

        foreach ($objWorksheet->getRowIterator() as $row) {
            if (++$i == 1) {   // ignore the first row
                continue;
            }

            // related to type
            $cell = $objWorksheet->getCell("B" . $i);
            $valueb = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("C" . $i);
            $valuec = trim($cell->getFormattedvalue());

            // related to material description
            $cell = $objWorksheet->getCell("D" . $i);
            $valued = trim($cell->getFormattedvalue());

            // unit
            $cell = $objWorksheet->getCell("E" . $i);
            $valuee = trim($cell->getFormattedvalue());
            // dodate/update
            $cell = $objWorksheet->getCell("G" . $i);
            $valueg = trim($cell->getFormattedvalue());
            // rate
            $cell = $objWorksheet->getCell("H" . $i);
            $valueh = trim($cell->getFormattedvalue());
            // quantity
            $cell = $objWorksheet->getCell("I" . $i);
            $valuei = trim($cell->getFormattedvalue());

            //if($valueb!="" && $valuec!="")
            if ($valueb != "") {// materials from a new subtype
                $nameenglast = $typeeng = trim($valueb);
                $namelast = $typechs = trim($valuec);
                $typeobj = $this->_materialtype->findOneBy(array("typeeng" => $typeeng, "typechs" => $typechs));
                if ($typeobj) {
                    $subtype = $typeobj;
                    $subtypeflag = true;
                } else {
                    //echo "shit, what happened...";
                    continue;
                }
            }

            $materialfound = false;
            if (trim($valued) != "") {// find material, store it in db
                $materialfound = true;
                $description = trim($valued);
            } else if ($valuee != "" && $valueg != "" && $valueh != "" && $valuei != "" && ($subtypeflag || $valuec != "")) {// another case: no description(spec), but it's still a material
                $materialfound = true;
                $description = "NO DESCRIPTION";
            } else if ($valuec != "") {
                $namelast = $valuec;
            }

            if ($materialfound) {
                $nameenglast = $nameeng = ($valueb == "") ? $nameenglast : $valueb;
                $namelast = $namechs = ($valuec == "") ? $namelast : $valuec;

                $tmparr = array();
                $tmparr['name'] = trim($namechs);
                $tmparr['nameeng'] = trim($nameeng);
                $tmparr['description'] = trim($description);
                $tmparr['type'] = $subtype;
                $tmparr['sheet'] = $sheetname;

                $materialArr[] = $tmparr;
                $materialfound = false;
                $subtypeflag = false;
            }
        }

        $this->storeMaterials($materialArr);

        // import supplyprice
        $this->storeSupplyprice($objWorksheet);

        /*
          try {
          $this->_em->flush();
          } catch (Exception $e) {
          var_dump($e);
          return;
          } */
    }

    private function storeSupplier($objWorksheet) {
        // step1 - supplier    
        $i = 0;
        $supplierArr = array();
        foreach ($objWorksheet->getRowIterator() as $row) {
            if (++$i == 1) {   // ignore the first row
                continue;
            }

            $cell = $objWorksheet->getCell("K" . $i);
            $valuek = $cell->getFormattedvalue();
            if ($valuek != "") {
                $name = trim($valuek);
                $supplier = $this->_supplier->findOneBy(array("name" => $name));
                if ($supplier) {
                    //echo "supplier already there.<br>";
                } else if (!in_array($name, $supplierArr)) {
                    $supplierArr[] = $name;
                }
            }
        }

        foreach ($supplierArr as $tmp) {
            $supplier = new \Synrgic\Infox\Supplier();
            $supplier->setName($tmp);
            $this->_em->persist($supplier);
        }

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        //return;
        // supplier done    
    }

    private function storeMaterials($materialarr) {
        foreach ($materialarr as $tmp) {
            $name = $tmp['name'];
            $nameeng = $tmp['nameeng'];
            $description = $tmp['description'];
            $type = $tmp['type'];
            $sheet = $tmp['sheet'];

            $obj = $this->_material->findOneBy(
                    array('name' => $name, 'nameeng' => $nameeng, 'description' => $description));
            if ($obj) {
                //echo "material already there.<br>";
            } else {
                $obj = new \Synrgic\Infox\Material();
                $obj->setNameeng($nameeng);
                $obj->setName($name);
                $obj->setDescription($description);
                $obj->setType($type);
                //$obj->setUnit($unit);
                $obj->setSheet($sheet);
                $this->_em->persist($obj);
            }
        }

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    private function storeSupplyprice($objWorksheet) {
        //echo "storeSupplyprice<br>";

        $subtype = null;
        $subtypeflag = false;

        $indexarr = array("B", "C", "D", "E", "F", "G", "H", "I", "J", "K",);
        $nameenglast = "";
        $namelast = "";
        $supplypriceArr = array();
        $i = 0;

        $nameeng = "";
        $namechs = "";
        $description = "";

        foreach ($objWorksheet->getRowIterator() as $row) {
            if (++$i == 1) {   // ignore the first row
                continue;
            }

            $cell = $objWorksheet->getCell("B" . $i);
            $valueb = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("C" . $i);
            $valuec = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("D" . $i);
            $valued = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("E" . $i);
            $valuee = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("G" . $i);
            $valueg = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("H" . $i);
            $valueh = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("I" . $i);
            $valuei = trim($cell->getFormattedvalue());
            $cell = $objWorksheet->getCell("K" . $i);
            $valuek = trim($cell->getFormattedvalue());

            if ($valueb != "") {
                $nameeng = $valueb;
                $namechs = $valuec;
                $description = $valued;
                $subtypeflag = true;
            }

            if ($valuec != "") {
                $namechs = $valuec;
                $description = $valued;
            }

            if ($valued != "") {
                $description = $valued;
            } else if ($valued == "" && $subtypeflag) {
                $description = "NO DESCRIPTION";
            } else if ($valued == "" && $valuec != "") {
                $description = "NO DESCRIPTION";
            }


            if ($valuee != "" && $valueg != "" && $valueh != "" && $valuei != "") {//in this case, store the supplyprice
                $tmparr = array();
                $tmparr[] = trim($namechs);
                $tmparr[] = trim($nameeng);
                $tmparr[] = trim($description);

                $tmparr[] = trim($valuek);

                $tmparr[] = trim($valuee);
                $tmparr[] = date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($valueg)); //trim($valueg);                
                $tmparr[] = trim($valueh);
                $tmparr[] = trim($valuei);

                $supplypricearr[] = $tmparr;

                $subtypeflag = false;
            }
        }

        $this->persistAllSupplyPrices($supplypricearr);

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    private function persistAllSupplyPrices($supplypricearr) {
        // debug, dump the array
        echo "supplypricearr count=" . count($supplypricearr) . "<br>";
        foreach ($supplypricearr as $tmp) {
            //print_r($tmp); echo "<br>";
            $this->persistSupplyprice($tmp);
        }
    }

    private function persistSupplyprice($dataarr) {// dataarr = { material name, material nameeng, material description, supplier name, unit, update, rate, quantity, }
        $mname = $dataarr[0];
        $mnameeng = $dataarr[1];
        $mdescription = $dataarr[2];
        $sname = $dataarr[3];
        $unit = $dataarr[4];
        $update = $dataarr[5];
        $rate = $dataarr[6];
        $quantity = $dataarr[7];

        $material = $this->_material->findOneBy(array("name" => $mname, "nameeng" => $mnameeng, "description" => $mdescription));
        if (!$material) {
            //print_r($dataarr); echo "<br>";
            echo "nameeng=$mnameeng,name=$mname,description=$mdescription<br>";
            //$obj = $this->_material->findOneBy(array("nameeng"=>$mnameeng));
            //echo $obj->getId() . "<br>";
        }

        $supplier = $this->_supplier->findOneBy(array("name" => $sname));

        $supplyprice = new \Synrgic\Infox\Supplyprice();
        $supplyprice->setMaterial($material);
        $supplyprice->setSupplier($supplier);
        $supplyprice->setUnit($unit);
        $supplyprice->setUpdate(new Datetime($update));
        $supplyprice->setRate($rate);
        $supplyprice->setQuantity($quantity);

        $this->_em->persist($supplyprice);
    }

    public function truncateallAction() {
        infox_common::turnoffView($this->_helper);

        // http://stackoverflow.com/questions/9686888/how-to-truncate-a-table-using-doctrine-2
        // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
        // put it here to reset id generator
        $data = new \Synrgic\Infox\Ordermaterialsummaryraw();
        $cmd = $this->_em->getClassMetadata(get_class($data));
        $connection = $this->_em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            //$connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName(), true);
            $connection->executeUpdate($q);
            //$connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            var_dump($e);
            return;
        }

        $this->redirect("/material/summary");
    }
}
