<?php

class Material_ImportController extends Zend_Controller_Action
{
    private $_files;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
    }

    public function indexAction()
    {
        $this->view->maindata = $result = $this->_material->findAll();
    }

    private function turnoffview()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    public function submitAction()
    {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if(0) {
            var_dump($requests);
            return;
        }

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

        $sheetarray =  array("safety material","safety material (2)");
        $objWorksheet = $objReader->setLoadSheetsOnly($sheetarray);

        /**  Load $inputFileName to a PHPExcel Object  **/
        $objPHPExcel = $objReader->load($inputFileName);
        echo '<hr />';

        // http://phpexcel.codeplex.com/discussions/265801
        // http://phpexcel.codeplex.com/discussions/442409
        // http://phpexcel.codeplex.com/discussions/257839
        // http://phpexcel.codeplex.com/discussions/70463

        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);
        //$objWorksheet = $objPHPExcel->setActiveSheetIndex('0') ;
        $objWorksheet = $objPHPExcel->setActiveSheetIndexByName('safety material (2)');

        $datecolumns = array(7,);
        $i=0;
        $j=0;

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

        foreach ($objWorksheet->getRowIterator() as $row)
        {
            if(++$i == 1)
            {   // ignore the first row
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cell = $objWorksheet->getCell("A".$i);
            $value1 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("B".$i);
            $value2 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("C".$i);
            $value3 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("D".$i);
            $value4 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("E".$i);
            $value5 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("F".$i);
            $value6 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("G".$i);
            $value7 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("H".$i);
            $value8 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("I".$i);
            $value9 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("J".$i);
            $value10 = $cell->getFormattedvalue();

            $cell = $objWorksheet->getCell("K".$i);
            $value11 = $cell->getFormattedvalue();




            $cell = $objWorksheet->getCell("A".$i);
            $sn = $cell->getValue();
            if($sn=="")
            {   // sn equal 0 means not a worker
                continue;
            }
            else
            {
                $obj = new \Synrgic\Infox\Workerdetails();
            }

            $j=0;
            foreach ($cellIterator as $cell)
            {
                $j++;
                $value = "";
                if(in_array($j, $datecolumns))
                {
                    $cellvalue = $cell->getValue();
                    if($cellvalue == "" || !intval($cellvalue))
                    {
                        $value = null;
                    }
                    else
                    {
                        $value = date('d-m-Y',PHPExcel_Shared_Date::ExcelToPHP($cellvalue));
                        $value = new Datetime($value);
                    }
                }
                else
                {
                    $value = $cell->getValue();
                }

                switch ($j)
                {
                case 1:
                    $obj->setSn($value);
                    break;
                case 2:
                    $obj->setEeeno($value);
                    break;
                case 3:
                    $obj->setNamechs($value);
                    break;
                case 4:
                    $obj->setNameeng($value);
                    break;
                case 5:
                    $obj->setWpno($value);
                    break;
                case 6:
                    $obj->setWpexpiry($value);
                    break;
                case 7:
                    $obj->setDoa($value);
                    break;
                case 8:
                    $obj->setIssuedate($value);
                    break;
                case 9:
                    $obj->setFinno($value);
                    break;
                case 10:
                    $obj->setPpno($value);
                    break;
                case 11:
                    $obj->setDob($value);
                    break;
                case 12:
                    $obj->setPpexpiry($value);
                    break;
                case 13:
                    $obj->setRate($value);
                    break;
                case 14:
                    $obj->setPano($value);
                    break;
                case 15:
                    $obj->setSbno($value);
                    break;
                case 16:
                    $obj->setSecurityexp($value);
                    break;
                case 17:
                    $obj->setWorktype($value);
                    break;
                case 18:
                    $obj->setArrivaldate($value);
                    break;
                case 19:
                    $obj->setMedicaldate($value);
                    break;
                case 20:
                    $obj->setCsoc($value);
                    break;
                case 21:
                    $obj->setMedicalinsurance($value);
                    break;
                case 22:
                    $obj->setWorkingsite($value);
                    break;
                case 23:
                    $obj->setDormitory($value);
                    break;
                case 24:
                    $obj->setHometown($value);
                    break;
                case 25:
                    $obj->setEducation($value);
                    break;
                case 26:
                    $obj->setAge($value);
                    break;
                case 27:
                    $obj->setMarital($value);
                    break;
                case 28:
                    $obj->setConstructionworker($value);
                    break;
                case 29:
                    $obj->setApplyfor($value);
                    break;
                case 30:
                    $obj->setGoodat($value);
                    break;
                case 31:
                    $obj->setContactno1($value);
                    break;
                case 32:
                    $obj->setContactno2($value);
                    break;
                case 33:
                    $obj->setCertificate($value);
                    break;
                case 34:
                    $obj->setRemarks($value);
                    break;
                }

            }
            $this->_em->persist($obj);

        }

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        /*
                foreach ($objWorksheet->getRowIterator() as $row)
                {
                    if(++$i == 1)
                    {// ignore the first row
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
                        if($cellvalue == "")
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
                        $value = $cell->getValue();
                    }
                    echo $value . "||";
                    }

                    echo "<hr>";
                }
        */
    }

    public function truncateAction()
    {
        // TODO: truncate table
        // http://stackoverflow.com/questions/9686888/how-to-truncate-a-table-using-doctrine-2


    }

}
