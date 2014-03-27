<?php

set_time_limit(0);
include 'InfoX/infox_common.php';

class Worker_ImportController extends Zend_Controller_Action {

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_workercustominfo = $this->_em->getRepository('Synrgic\Infox\Workercustominfo');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
    }

    public function indexAction() {
        
    }

    private function turnoffview() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    public function submitAction() {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        // upload excel
        define('UPLOAD_WORKER', APPLICATION_PATH . '/data/uploads/workers/');
        $uploadpath = UPLOAD_WORKER;
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

        // http://phpexcel.codeplex.com/discussions/265801
        // http://phpexcel.codeplex.com/discussions/442409
        // http://phpexcel.codeplex.com/discussions/257839
        // http://phpexcel.codeplex.com/discussions/70463
        // load data from excel
        include 'PHPExcel/IOFactory.php';

        $inputFileName = $filepath;
        echo 'Loading file ', pathinfo($inputFileName, PATHINFO_BASENAME), ' using IOFactory to identify the format<br>';
        //$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

        $inputFileType = 'Excel5';
        /**  Create a new Reader of the type defined in $inputFileType  * */
        $objReader = PHPExcel_IOFactory::createReader($inputFileType);
        /* reduce memory consumption */
        $objReader->setReadDataOnly(true);

        if (1) {
            $sheetarr = array("HC.C", "HT.C", "HC.B", "HT.B");
        } else {
            $sheetarr = array("HC.C",);
        }

        $objWorksheet = $objReader->setLoadSheetsOnly($sheetarr);
        /**  Load $inputFileName to a PHPExcel Object  * */
        $objPHPExcel = $objReader->load($inputFileName);

        echo '<hr />';

        //$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
        //var_dump($sheetData);
        //$objWorksheet = $objPHPExcel->setActiveSheetIndex('0') ;

        foreach ($sheetarr as $sheetname) {
            $objWorksheet = $objPHPExcel->setActiveSheetIndexByName($sheetname);
            if ($objWorksheet) {
                $this->storeDetails($sheetname, $objWorksheet);
            }
        }

        $this->redirect("/worker/manage/");
        return;
    }

    private function storeDetails($sheetname, $objWorksheet) {
        $datecolumns = array(6, 7, 8, 11, 12, 15, 16, 17, 19,);

        $i = 0;
        $j = 0;
        foreach ($objWorksheet->getRowIterator() as $row) {
            if (++$i == 1) {   // ignore the first row
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cell = $objWorksheet->getCell("A" . $i);
            $sn = $cell->getValue();

            $cellb = $objWorksheet->getCell("B" . $i);
            $eeeno = trim($cellb->getValue());

            if ($sn == "" || !intval($sn) || $eeeno == "") {   // these mean it's not a worker row
                continue;
            } else {
                $obj = new \Synrgic\Infox\Workerdetails();
            }

            $j = 0;
            foreach ($cellIterator as $cell) {
                $j++;

                $value = "";
                if (in_array($j, $datecolumns)) {
                    $cellvalue = $cell->getValue();
                    if ($cellvalue == "" || !intval($cellvalue)) {
                        $value = null;
                    } else {
                        $value = date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($cellvalue));
                        $value = new Datetime($value);
                    }
                } else {
                    $value = $cell->getValue();
                }

                switch ($j) {
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
                        $obj->setWorktype($value);
                        break;
                    case 15:
                        $obj->setArrivaldate($value);
                        break;
                    case 16:
                        $obj->setMedicaldate($value);
                        break;
                    case 17:
                        $obj->setCsoc($value);
                        break;
                    case 18:
                        $intval = (int) $value;
                        if ($intval != 0 || $intval) {// date
                            $value = date('d-m-Y', PHPExcel_Shared_Date::ExcelToPHP($value));
                        }
                        $obj->setMedicalinsurance($value);
                        break;
                    case 19:
                        $obj->setSecurityexp($value);
                        break;
                    case 20:
                        $obj->setWorkingsite($value);
                        break;
                    case 21:
                        $obj->setDormitory($value);
                        break;
                    case 22:
                        $obj->setGoodat($value);
                        break;
                    case 23:
                        $obj->setContactno1($value);
                        break;
                    case 24:
                        $obj->setContactno2($value);
                        break;
                    case 25:
                        $obj->setCertificate($value);
                        break;
                    case 26:
                        $obj->setAgent($value);
                        break;
                    case 27:
                        $obj->setRemark($value);
                        break;
                    case 28:
                        $obj->setHometown($value);
                        break;
                    case 29:
                        $obj->setEducation($value);
                        break;
                    case 30:
                        $obj->setAge($value);
                        break;
                    case 31:
                        $obj->setMarital($value);
                        break;
                    case 32:
                        $obj->setConstructionworker($value);
                        break;
                    case 33:
                        $obj->setApplyfor($value);
                        break;
                }
            }

            $obj->setSheet($sheetname);
            $this->_em->persist($obj);            
        }

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
    }

    public function truncateworkerdetailsAction1() {
        $this->turnoffview();

        // http://stackoverflow.com/questions/5301285/explicitly-set-id-with-doctrine-when-using-auto-strategy
        // put it here to reset id generator
        $data = new \Synrgic\Infox\Workerdetails();
        $cmd = $this->_em->getClassMetadata(get_class($data));
        $connection = $this->_em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        $connection->beginTransaction();
        try {
            //$connection->query('SET FOREIGN_KEY_CHECKS=0');
            $q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
            $connection->executeUpdate($q);
            //$connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            var_dump($e);
            return;
        }

        $this->redirect("/worker/manage");

        /*
          $this->_em->remove($data);
          $this->_em->flush();
         */
    }

    public function truncateworkerdetailsAction() {
        infox_common::turnoffView($this->_helper);

        $data = new \Synrgic\Infox\Workerdetails();
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

        //$this->redirect("/worker/manage");        
    }

    private function findCompanies() {
        $this->view->companies = $this->_companyinfo->findAll();
    }

    private function getWorktypes() {
        $label = "info04";
        $values = $this->_miscinfo->findOneBy(array("label" => $label))->getValues();

        $this->view->worktypes = explode(";", $values);
    }

}
