<?php
include 'InfoX/infox_common.php';

class Material_ImportController extends Zend_Controller_Action
{
    private $_files;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
        $this->_supplyprice = $this->_em->getRepository('Synrgic\Infox\Supplyprice');
    }

    public function indexAction()
    {
    }

    private function turnoffview()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    public function submitAction()
    {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }

        // upload excel
        define('UPLOAD_TMP_PATH', APPLICATION_PATH. '/data/uploads/');
        $uploadpath = UPLOAD_TMP_PATH;
        $filepath = "";
        $allowedExts = array("xls", "xlsx");
        $extension = end(explode(".", $_FILES["file"]["name"]));
        if (in_array($extension, $allowedExts))
        {
            if ($_FILES["file"]["error"] > 0)
            {
                echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
                return;
            }
            else
            {
                echo "Upload: " . $_FILES["file"]["name"] . "<br>";
                echo "Type: " . $_FILES["file"]["type"] . "<br>";
                echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
                echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br>";

                if (file_exists($uploadpath . $_FILES["file"]["name"]))
                {
                    echo $_FILES["file"]["name"] . " already exists.<br>";
                }

                $filepath = $uploadpath . $_FILES["file"]["name"];
                move_uploaded_file($_FILES["file"]["tmp_name"],  $filepath);
                echo "Stored in:$filepath<br>";
            }
        }
        else
        {
            echo "Error: Invalid file, 请上传xls, xlsx后缀文件<br>";
            return;
        }

        // load data from excel
        include 'PHPExcel/IOFactory.php';

        $inputFileName = $filepath;
        echo 'Loading file ',pathinfo($inputFileName,PATHINFO_BASENAME),' using IOFactory to identify the format<br>';
        //$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

        $inputFileType = 'Excel5';
        /**  Create a new Reader of the type defined in $inputFileType  **/
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        /* reduce memory consumption */
        $objReader->setReadDataOnly(true);
        
        if(1)
        {
        $sheetarr =  array("safety material","formwork","concrete", "concrete",
                            "rebar","equipment","electrical","worker domitory","logistics",
                            "water pipe","spare parts","scaffolding", );
        }
        else
        {
        $sheetarr =  array("safety material",);
        }

        $objWorksheet = $objReader->setLoadSheetsOnly($sheetarr);
        /**  Load $inputFileName to a PHPExcel Object  **/
        $objPHPExcel = $objReader->load($inputFileName);
        echo '<hr />';

        foreach($sheetarr as $sheetname)
        {
            $objWorksheet = $objPHPExcel->setActiveSheetIndexByName($sheetname);
            if($objWorksheet)
            {
                $this->storeDetails($sheetname, $objWorksheet);
            }

        /*
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            if(++$i == 1)
            {   // ignore the first row
                continue;
            }

            $j=0;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            foreach ($cellIterator as $cell)
            {
                $j++;
                if(in_array($j, $datecolumns))
                {
                    $cellvalue = $cell->getValue();
                    if($cellvalue == "" || !intval($cellvalue))
                    {
                        $value = "";
                    }
                    else
                    {
                        $value = date('d-m-Y',PHPExcel_Shared_Date::ExcelToPHP($cellvalue));
                    }
                }
                else
                {
                    $value = $cell->getFormattedvalue();
                }
                echo $value . "||";
            }

            echo "<hr>";
        }
        return;
        */

        }
        
       //$this->redirect("/material/manage/");        
    }

    private function storeSupplier($objWorksheet)
    { 
        // step1 - supplier    
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
    }

    private function storeDetails($sheetname, $objWorksheet)
    {
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
        $i=0;
        $subtypeArr = array();
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            if(++$i == 1)
            {   // ignore the first row
                continue;
            }

            $cell = $objWorksheet->getCell("B".$i);
            $valueb = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("C".$i);
            $valuec = $cell->getFormattedvalue();

            // this means a sub type
            if($valueb!="" && $valuec!="")
            {                
                $typeeng = trim($valueb);
                $typechs = trim($valuec);
             
                $typeobj = $this->_materialtype->findOneBy(array("typeeng"=>$typeeng, "typechs"=>$typechs));
                if($typeobj)
                {
                    //echo "sub type already there.<br>";
                }
                else
                {
                    $sub = array($typeeng, $typechs);
                    if(!in_array($sub, $subtypeArr))
                    {
                        $subtypeArr[] = $sub;
                    }
                }
            }
            // sub type done            
        }

        $maintype = $this->_materialtype->findOneBy(array("typeeng"=>$sheetname));
        foreach($subtypeArr as $tmp)
        {
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
        $indexarr = array("B","C","D","E","F","G","H","I","J","K",);
        $nameenglast = "";
        $namelast = "";
        $materialArr = array();
        $i = 0;
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            if(++$i == 1)
            {   // ignore the first row
                continue;
            }

            // related to type
            $cell = $objWorksheet->getCell("B".$i);
            $valueb = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("C".$i);
            $valuec = $cell->getFormattedvalue();

            // related to material description
            $cell = $objWorksheet->getCell("D".$i);
            $valued = $cell->getFormattedvalue();

            // unit
            $cell = $objWorksheet->getCell("E".$i);
            $valuee = $cell->getFormattedvalue();
            // dodate/update
            $cell = $objWorksheet->getCell("G".$i);
            $valuee = $cell->getFormattedvalue();
            // rate
            $cell = $objWorksheet->getCell("H".$i);
            $valuee = $cell->getFormattedvalue();
            // quantity
            $cell = $objWorksheet->getCell("I".$i);
            $valuee = $cell->getFormattedvalue();
           
            if($valueb!="" && $valuec!="")
            {// materials from a new subtype
                $nameenglast = $typeeng = trim($valueb);
                $namelast = $typechs = trim($valuec);
                $typeobj = $this->_materialtype->findOneBy(array("typeeng"=>$typeeng, "typechs"=>$typechs));                
                if($typeobj)
                {
                    $subtype = $typeobj;
                }
                else
                {
                    echo "shit, what happened...";
                    continue;
                }
            }

            // this is kind of material
            if(trim($valued) != "")
            {// find material, store it in db
                $nameenglast = $nameeng = ($valueb=="") ? $nameenglast : $valueb;
                $namelast = $namechs = ($valuec=="") ? $namelast : $valuec;
                $description = trim($valued);
                $unit = trim($valuee);

                // TODO: check if it's already there
                $obj = $this->_material->findOneBy(
                        array('name'=>$namechs,'nameeng'=>$nameeng, 'description'=>$description));
                if($obj)
                {
                    //echo "material already there.<br>";
                }
                else
                {
                    $obj = new \Synrgic\Infox\Material();
                    $obj->setNameeng($nameeng);
                    $obj->setName($namechs);
                    $obj->setDescription($description);
                    $obj->setType($subtype);
                    $obj->setUnit($unit);
                    $obj->setSheet($sheetname);                      
                    $this->_em->persist($obj);          
                }                
            }
        }

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        // import supplyprice
        $this->storeSupplyprice($objWorksheet);
    }

    private function storeSupplyprice($objWorksheet)
    {
        //echo "storeSupplyprice<br>";

        $subtype = null;
        $indexarr = array("B","C","D","E","F","G","H","I","J","K",);
        $nameenglast = "";
        $namelast = "";
        $supplypriceArr = array();
        $i = 0;

        $nameeng = "";
        $namechs = "";
        $description = "";

        foreach ($objWorksheet->getRowIterator() as $row)
        {
            if(++$i == 1)
            {   // ignore the first row
                continue;
            }

            $cell = $objWorksheet->getCell("B".$i);
            $valueb = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("C".$i);
            $valuec = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("D".$i);
            $valued = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("E".$i);
            $valuee = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("G".$i);
            $valueg = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("H".$i);
            $valueh = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("I".$i);
            $valuei = $cell->getFormattedvalue();
            $cell = $objWorksheet->getCell("K".$i);
            $valuek = $cell->getFormattedvalue();

            if($valueb != "")
            {
                $nameeng = $valueb;
            }        

            if($valuec != "")
            {
                $namechs = $valuec;
            }

            if($valued != "")
            {
                $description = $valued;
            }

            if($valuee != "" && $valueg != "" && $valueh != "" && $valuei != "")
            {//in this case, store the supplyprice

                $tmparr = array();
                $tmparr[] = $namechs;
                $tmparr[] = $nameeng;
                $tmparr[] = $description;

                $tmparr[] = trim($valuek);

                $tmparr[] = trim($valuee);
                $tmparr[] = date('d-m-Y',PHPExcel_Shared_Date::ExcelToPHP($valueg));//trim($valueg);                
                $tmparr[] = trim($valueh);
                $tmparr[] = trim($valuei);   

                $supplypricearr[] = $tmparr;
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

    private function persistAllSupplyPrices($supplypricearr)
    {
        // debug, dump the array
        echo "supplypricearr count=" . count($supplypricearr);
        foreach($supplypricearr as $tmp)
        {
            print_r($tmp); echo "<br>";
            //$this->persistSupplyprice($tmp);
        }
    }

    private function persistSupplyprice($dataarr)
    {// dataarr = { material name, material nameeng, material description, supplier name, unit, update, rate, quantity, }
        $mname = $dataarr[0];
        $mnameeng = $dataarr[1];
        $mdescription = $dataarr[2];
        $sname = $dataarr[3];
        $unit = $dataarr[4];
        $update = $dataarr[5];
        $rate = $dataarr[6];
        $quantity = $dataarr[7];

        $material = $this->_material->findOneBy(array("name"=>$mname, "nameeng"=>$mnameeng, "description"=>$mdescription));
        $supplier = $this->_supplier->findOneBy(array("name"=>$sname));

        $supplyprice = new \Synrgic\Infox\Supplyprice();
        $supplyprice->setMaterial($material);
        $supplyprice->setSupplier($supplier);        
        $supplyprice->setUnit($unit);        
        $supplyprice->setUpdate(new Datetime($update));        
        $supplyprice->setRate($rate);        
        $supplyprice->setQuantity($quantity);
        
        $this->_em->persist($supplyprice);        
    }

    public function truncateallAction()
    {
        infox_common::turnoffView($this->_helper);

        // http://stackoverflow.com/questions/9686888/how-to-truncate-a-table-using-doctrine-2
        // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
        // put it here to reset id generator
        $data = new \Synrgic\Infox\Material();
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
        }
        catch (\Exception $e) {
            $connection->rollback();
            var_dump($e);
            return;
        }        

        $this->redirect("/material/manage");

        /*
        $this->_em->remove($data);
        $this->_em->flush();
        */
    }
}
