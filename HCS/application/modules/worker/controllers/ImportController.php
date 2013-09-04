<?php

class Worker_ImportController extends Zend_Controller_Action
{
    private $_em;
    private $_worker;
    private $_site;
    private $_workerskill;
    private $_workercompanyinfo;
    private $_workerfamily;

    private $_files;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerskill = $this->_em->getRepository('Synrgic\Infox\Workerskill');
        $this->_workercompanyinfo = $this->_em->getRepository('Synrgic\Infox\Workercompanyinfo');
        $this->_workerfamily = $this->_em->getRepository('Synrgic\Infox\Workerfamily');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_workercustominfo = $this->_em->getRepository('Synrgic\Infox\Workercustominfo');

        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
    }

    public function indexAction()
    {
        /*
        $qb = $this->_em->createQueryBuilder();
        $qb->select('w', 'ws')
            ->from('Synrgic\Infox\Worker', 'w')
            ->leftJoin('w.workerskill', 'ws')
            ->leftJoin('w.workercompanyinfo', 'wc');
        $result = $qb->getQuery()->getResult();

        $this->view->workersdata = $result;

        $this->getCustominfo(0);
        */

        $this->view->workersdata = $result = $this->_workerdetails->findAll();
    }

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        if(0) {
            var_dump($requests);
            return;
        }

        // upload excel
        define('UPLOAD_WORKER', APPLICATION_PATH. '/data/uploads/workers/');
        $uploadpath = UPLOAD_WORKER;
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

        // TODO: truncate table
        // http://stackoverflow.com/questions/9686888/how-to-truncate-a-table-using-doctrine-2


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

        $objWorksheet = $objReader->setLoadSheetsOnly( array("HC.C") );
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
        $objWorksheet = $objPHPExcel->setActiveSheetIndexByName('HC.C');

        $datecolumns = array(6,7,8,11,12,16,18,19,20,);

        $i=0;
        $j=0;
        foreach ($objWorksheet->getRowIterator() as $row)
        {
            if(++$i == 1)
            {   // ignore the first row
                continue;
            }

            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

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
echo "pano=" . $value . "<br>";
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

    private function storePic($workerid)
    {   // http://www.w3schools.com/php/php_file_upload.asp
        $files = $this->_files;

        echo "<br>";
        $newfile = "";
        $uploadpath = UPLOAD_WORKER;
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
                    $picpath = $uploadpath . $workerid . "." . $extension;
                    move_uploaded_file($_FILES["file"]["tmp_name"], $picpath);
                    echo "Stored in: " . $picpath;
                    $newfile = $workerid . "." . $extension;
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
            $workerdata = $this->_worker->findOneBy(array('id'=>$workerid));
            //$pic = '/data/uploads/workers/images/' . $newfile;
            $pic = "/workers-pic/images/" . $newfile;
            $workerdata->setPic($pic);
            $this->_em->persist($workerdata);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
        }
    }

    private function storeInfo($requests)
    {
        $mode = $requests["mode"];

        if($mode == "Edit")
        {
            $workerid = $requests["workerid"];

            $workerdata = $this->_worker->findOneBy(array('id'=>$workerid));
            $skilldata = $workerdata->getWorkerskill();
            $cmydata = $workerdata->getWorkercompanyinfo();
            $familydata = $workerdata->getWorkerfamily();

            $customdata = $workerdata->getWorkercustominfo();
            if(!$customdata)
            {
                $customdata = new \Synrgic\Infox\Workercustominfo();
            }
        }
        else if($mode = "Create")
        {
            $workerdata = new \Synrgic\Infox\Worker();
            $cmydata = new \Synrgic\Infox\Workercompanyinfo();
            $skilldata = new \Synrgic\Infox\Workerskill();
            $familydata = new \Synrgic\Infox\Workerfamily();

            $customdata = new \Synrgic\Infox\Workercustominfo();
        }
        else
        {
            echo "fatal error: unknown edit mode";
            return;
        }

        //$metadata = $em->getClassMetaData(get_class($data));
        //$metadata->setIdGenerator(new \Doctrine\ORM\Id\AssignedGenerator());

        // worker
        $nameeng = $requests["nameeng"];
        $namechs = $requests["namechs"];
        $fin = $requests["fin"];
        $passexp = $requests["passexp"];         // date
        $passport = $requests["passport"];
        $passportexp = $requests["passportexp"];         // date
        $gender = $requests["gender"];
        $age = $requests["age"];         //TODO: integer
        $birth = $requests["birth"];
        $marital = $requests["marital"];
        $address = $requests["address"];
        $hometown = $requests["hometown"];

        $arrivesing = $this->getParam("arrivesing", "");//$requests["arrivesing"];
        $leavesing = $this->getParam("leavesing", "");//$requests["leavesing"];

        $workerdata->setNameeng($nameeng);
        $workerdata->setNamechs($namechs);
        $workerdata->setFin($fin);
        if($passexp != "")
        {
            $workerdata->setPassexp(new DateTime($passexp));
        }
        $workerdata->setPassport($passport);
        if($passportexp != "")
        {
            $workerdata->setPassportexp(new DateTime($passportexp));
        }
        $workerdata->setGender($gender);
        $workerdata->setAge(intval($age));
        if($birth != "")
        {
            $workerdata->setBirth(new DateTime($birth));
        }
        $workerdata->setMarital($marital);
        $workerdata->setAddress($address);
        $workerdata->setHometown($hometown);

        if($arrivesing!="")
        {
            $workerdata->setArrivesing(new Datetime($arrivesing));
        }
        if($leavesing!="")
        {
            $workerdata->setLeavesing(new Datetime($leavesing));
        }

        $this->_em->persist($workerdata);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        $workerid = $workerdata->getId();
        echo "worker id=" . $workerid;

        // upload pic
        $this->storePic($workerid);

        // skill
        $worktype = $requests["worktype"];
        $worklevel = $requests["worklevel"];
        $education = $requests["education"];
        $pastwork = $requests["pastwork"];
        $skill1 = $requests["skill1"];
        $skill2 = $requests["skill2"];
        $drvlic = $requests["drvlic"];
        $securityexp = $requests["securityexp"];

        $skilldata->setWorktype($worktype);
        $skilldata->setWorklevel($worklevel);
        $skilldata->setEducation($education);
        $skilldata->setPastwork($pastwork);
        $skilldata->setSkill1($skill1);
        $skilldata->setSkill2($skill2);
        $skilldata->setDrvlic($drvlic);
        if($securityexp != "")
        {
            $skilldata->setSecurityexp(new DateTime($securityexp));
        }

        $this->_em->persist($skilldata);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        $skillid = $skilldata->getId();
        echo "skill id=$skillid<br>";

        // company info
        //$companylabel = $requests["companylabel"];
        //$cmydata->setCompanylabel($companylabel);

        $hwage = $requests["hwage"];    // float
        $cmydata->setHwage(floatval($hwage));

        $srvyears = $requests["srvyears"];
        $cmydata->setSrvyears(intval($srvyears));

        $yrsinsing = $requests["yrsinsing"];
        $cmydata->setYrsinsing(intval($yrsinsing));

        $servecompany = $this->getParam("servecompany", 0);
        $companyobj = $this->_companyinfo->findOneBy(array("id"=>$servecompany));
        if($companyobj)
        {
            $cmydata->setCompany($companyobj);
        }

        /*
        $site = $requests["site"];
        if($site != "")
        {
            $siteobj = $this->_site->findOneBy(array('name'=>$site));
            if($siteobj)
            {
                $cmydata->setSite($siteobj);
            }
        }
        */
        $siteid = $this->getParam("site", 0);
        $siteobj = $this->_site->findOneBy(array('id'=>$siteid));
        $cmydata->setSite($siteobj);

        $this->_em->persist($cmydata);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        $cmyid = $cmydata->getId();
        echo "cmydata id=$cmyid<br>";

        // family
        $homeaddr = $requests["homeaddr"];
        $member1 = $requests["member1"];
        $contact1 = $requests["contact1"];
        $member2 = $requests["member2"];
        $contact2 = $requests["contact2"];
        $member3 = $requests["member3"];
        $contact3 = $requests["contact3"];

        $familydata->setHomeaddr($homeaddr);
        $familydata->setMember1($member1);
        $familydata->setContact1($contact1);
        $familydata->setMember2($member2);
        $familydata->setContact2($contact2);
        $familydata->setMember3($member3);
        $familydata->setContact3($contact3);

        $this->_em->persist($familydata);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        $fmyid = $familydata->getId();
        echo "familydata id=$fmyid<br>";

        // custom info
        $custom1 = $this->getParam("custom1","");
        $custom2 = $this->getParam("custom2","");
        $custom3 = $this->getParam("custom3","");
        $custom4 = $this->getParam("custom4","");
        $customdata->setCustom1($custom1);
        $customdata->setCustom2($custom2);
        $customdata->setCustom3($custom3);
        $customdata->setCustom4($custom4);
        $this->_em->persist($customdata);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        $customid = $customdata->getId();
        echo "custominfo id=$customid<br>";

        // connect skill,company,family with worker
        $workerdata->setWorkerskill($this->_workerskill->findOneBy(array("id"=>$skillid)));
        $workerdata->setWorkercompanyinfo($this->_workercompanyinfo->findOneBy(array("id"=>$cmyid)));
        $workerdata->setWorkerfamily($this->_workerfamily->findOneBy(array("id"=>$fmyid)));
        $workerdata->setWorkercustominfo($this->_workercustominfo->findOneBy(array("id"=>$customid)));

        $this->_em->persist($workerdata);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $this->_redirect ( "worker/manage" );
    }

    public function outputAction()
    {
        $this->_helper->layout->disableLayout();
        $id = $this->getParam("no", 0);

        $qb = $this->_em->createQueryBuilder();
        $qb->select('w', 'ws','wc')
        ->from('Synrgic\Infox\Worker', 'w')
        ->leftJoin('w.workerskill', 'ws')
        ->leftJoin('w.workercompanyinfo', 'wc')
        ->where("w.id = ?1")
        ->setParameter(1, $id);
        $result = $qb->getQuery()->getResult();

        $this->view->workerdata = $result;
        //var_dump($result);
        //echo $result ? "got" : "got nothing";

        $this->view->workerdata = $this->_worker->findOneBy(array("id"=>$id));
    }

    private function findCompanies()
    {
        $this->view->companies = $this->_companyinfo->findAll();
    }

    private function getWorktypes()
    {
        $label = "info04";
        $values = $this->_miscinfo->findOneBy(array("label"=>$label))->getValues();

        $this->view->worktypes = explode(";", $values);
    }

}
