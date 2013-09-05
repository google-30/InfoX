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

        $indexarr = array("B","C","D","E","F","G","H","I","J","K",);
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

            $nameenglast = "";
            $namelast = "";
            if($valueb!="" || $valuec!="")
            {
                $nameenglast = $valueb;
                $namelast = $valuec;
                echo "nameenglast=$nameenglast<br>"; //continue;
            }

            $valuearr = array();
            foreach($indexarr as $idx)
            {
                $cell = $objWorksheet->getCell($idx.$i);
                $value = $cell->getFormattedvalue();
                echo "value=$value||";

                if($idx=="B" && ($value==""))
                {
                    $value = $nameenglast;
                echo "XXXXXXvalue=$value||";
                }

                if($idx=="C" && $value=="")
                {
                    $value = $namelast;                
                }

                $valuearr[] = $value;
           
            }
            echo "<br>";
            //continue;

            $skipflag = true;
            foreach($valuearr as $value)
            {
                if($value != "")
                {
                    $skipflag = false;
                    break;
                }
            }

            if(!$skipflag)
            {// store data
                $obj = new \Synrgic\Infox\Material();
                $obj->setNameeng($valuearr[0]);
                $obj->setName($valuearr[1]);
                $obj->setDescription($valuearr[2]);
                $obj->setUnit($valuearr[3]);
                $obj->setDono($valuearr[4]);

                $value = date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($valuearr[5]));
                $value = new Datetime($value);
                $obj->setDodate($value);

                $obj->setRate($valuearr[6]);
                $obj->setQuantity($valuearr[7]);
                $obj->setAmount($valuearr[8]);
                $obj->setSuppliers($valuearr[9]);
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

    public function truncateAction()
    {
        // TODO: truncate table
        // http://stackoverflow.com/questions/9686888/how-to-truncate-a-table-using-doctrine-2


    }

}
