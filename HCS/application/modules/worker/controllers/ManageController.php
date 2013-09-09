<?php

define('UPLOAD_WORKER', APPLICATION_PATH. '/data/uploads/workers/images/');
class Worker_ManageController extends Zend_Controller_Action
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
        $this->getworkerlist();
    }

    public function previewlistAction()
    {
        $this->_helper->layout->disableLayout();        
        $this->getworkerlist();
    }

    private function getworkerlist()
    {
        $requestsheet = $this->getParam("sheet","HC.C");     
        $sheetarr = array("HC.C","HT.C","HC.B","HT.B");
        $workerarr = array();
                    
        if(!in_array($requestsheet, $sheetarr))
        {
            $allworkers = $this->_workerdetails->findAll();
            foreach($allworkers as $worker)
            {
                $sheet = $worker->getSheet();
                if(!in_array($sheet, $sheetarr))
                {
                    $workerarr[] = $worker;
                }
            }            
        }
        else
        {
            $workerarr = $this->_workerdetails->findBy(array('sheet'=>$requestsheet));
        }

        $this->view->sheet = $requestsheet;        
        $this->view->sheetarr = $sheetarr;
        $this->view->maindata = $workerarr;
    }

    public function index1Action()
    {
        // dql result: http://docs.doctrine-project.org/en/2.1/reference/dql-doctrine-query-language.html
        // embed select: http://msdn.microsoft.com/zh-cn/library/ms189575(v=sql.105).aspx
        /*
        $query = $this->_em->createQuery(
        'select w, wc.hwage, ws.worktype, ws.worklevel,
        (select site.name from Synrgic\Infox\Site site where site.id = wc.site) as sitename,
        (select cinfo.namechs from Synrgic\Infox\Companyinfo cinfo where cinfo.id = wc.company) as companyname   
        from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws'

         );
        $result = $query->getResult();
        $this->view->result = $result;

        $query = $this->_em->createQuery(
            'select w, wc, ws from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws'
        );
        $result = $query->getResult();
        $this->view->workersdata = $result;        
        */

        $qb = $this->_em->createQueryBuilder();
        $qb->select('w', 'ws')
            ->from('Synrgic\Infox\Worker', 'w')
            ->leftJoin('w.workerskill', 'ws')
            ->leftJoin('w.workercompanyinfo', 'wc');
        $result = $qb->getQuery()->getResult();

        $this->view->workersdata = $result;  

        $this->getCustominfo(0);

    }

    public function workerexpireAction()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('w', 'ws')
            ->from('Synrgic\Infox\Worker', 'w')
            ->leftJoin('w.workerskill', 'ws')
            ->leftJoin('w.workercompanyinfo', 'wc');
        $result = $qb->getQuery()->getResult();

        $this->getSecurityexp($result);        
    }

    private function getSecurityexp($allworkers)
    {// get expired, 1 month, 2 monthes, worker list
        // http://stackoverflow.com/questions/10582108/how-can-i-compare-a-date-with-current-date-using-doctrine-2
        // $em->createQuery('SELECT d FROM test d WHERE d.expDate > CURRENT_DATE()');

        if(!count($allworkers))
        {
            return;
        }

        $expiredarr = array();
        $expire1arr = array();
        $expire2arr = array();

        foreach($allworkers as $tmp)
        {
            $date = $tmp["workerskill"]->getSecurityexp();
            $now = new DateTime("now");
            
            if(!$date)
            {
                $expiredarr[] = $tmp; 
                continue;
            }

            $interval = $date->diff($now);
            $invert = $interval->invert;
            $days = $interval->days;
            //$mark = $invert ? "+" : "-"; echo $mark . $days . "<br>";

            if(!$invert)
            {
                $expiredarr[] = $tmp; 
            }
            else if($days <= 30)
            {
                $expire1arr[] = $tmp;
            }
            else if($days <= 60)
            {
                $expire2arr[] = $tmp;
            }            
        }

        $this->view->expired = $expiredarr;
        $this->view->expire1 = $expire1arr;
        $this->view->expire2 = $expire2arr;        
        
    }

    private function getCustominfo($id)
    {
        // titles
        $label="01";
        $category="worker";
        $infoobj = $this->_miscinfo->findOneBy(array("category"=>$category, "label"=>$label));        
        $values = $infoobj ? $infoobj->getValues() : "";
        $this->view->customtitles = explode(";", $values);

        // data
        $workerobj = $this->_worker->findOneBy(array("id"=>$id));
        $custominfoobj = $workerobj ? $workerobj->getWorkercustominfo() : null;
        $this->view->custominfos = $custominfoobj ? $custominfoobj : null;        
    }

    public function addAction()
    {
        $this->view->id = 0;

        $sites = $this->_site->findAll();
        $this->view->sites = $sites;

        $this->findCompanies();
        $this->getWorktypes();

        $this->getCustominfo(0);
    }

    public function editAction()
    {
        $id = $this->_getParam("id");
        //echo "id=$id";
        $this->view->workerid = $id;
        $this->view->worker = $worker = $this->_workerdetails->findOneBy(array("id"=>$id));

        $sites = $this->_site->findAll();
        $this->view->sites = $sites;

        $this->findCompanies();
        $this->getWorktypes();

        $this->getCustominfo($id);
    }

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }

        $this->storeInfo($requests);
        $this->_files = $_FILES;
    }
    
    private function storeInfo($requests)
    {
        $mode = $requests["mode"];

        if($mode == "Edit")
        {
            //$workerid = $requests["workerid"];
            $workerid = $this->getParam("workerid", 0);
            $data = $this->_workerdetails->findOneBy(array('id'=>$workerid));

            $customdata = $data->getWorkercustominfo();
            if(!$customdata)
            {
                $customdata = new \Synrgic\Infox\Workercustominfo();
            }
        }
        else if($mode = "Create")
        {
            $data = new \Synrgic\Infox\Worker();
            $customdata = new \Synrgic\Infox\Workercustominfo();
        }
        
        $sn=$this->getParam("sn", "");
        //echo "sn=$sn<br>"; return;        
        $eeeno=$this->getParam("eeeno", "");
        $nameeng =$this->getParam("nameeng", "");
        $namechs =$this->getParam("namechs", "");
        $wpno=$this->getParam("wpno", "");
        $wpexpiry=$this->getParam("wpexpiry", "");
        $wpexpiry = ($wpexpiry=="") ? null : new Datetime($wpexpiry); 
        $doa=$this->getParam("doa", "");
        $doa = ($doa=="") ? null : new Datetime($doa);
        $issuedate=$this->getParam("issuedate", "");
        $issuedate = ($issuedate=="") ? null : new Datetime($issuedate);
        $finno=$this->getParam("finno", "");
        $ppno=$this->getParam("ppno", "");
        $dob=$this->getParam("dob", "");
        $dob = ($dob=="") ? null : new Datetime($dob);
        $ppexpiry=$this->getParam("ppexpiry", "");
        $ppexpiry = ($ppexpiry=="") ? null : new Datetime($ppexpiry);
        $rate=$this->getParam("rate", "");
        $pano=$this->getParam("pano", "");
        $sbno=$this->getParam("sbno", "");
        $securityexp=$this->getParam("securityexp", "");
        $securityexp = ($securityexp=="") ? null : new Datetime($securityexp);
        $worktype=$this->getParam("worktype", "");
        $arrivaldate=$this->getParam("arrivaldate", "");
        $arrivaldate = ($arrivaldate=="") ? null : new Datetime($arrivaldate);
        $medicaldate=$this->getParam("medicaldate", "");
        $medicaldate = ($medicaldate=="") ? null : new Datetime($medicaldate);
        $csoc=$this->getParam("csoc", "");
        $medicalinsurance=$this->getParam("medicalinsurance", "");
        $workingsite=$this->getParam("sn", "");
        $dormitory=$this->getParam("workingsite", "");
        $hometown = $this->getParam("hometown", "");
        $education=$this->getParam("education", "");
        $age = $this->getParam("age", "");
        $marital=$this->getParam("marital", "");
        $constructionworker=$this->getParam("constructionworker", "");
        $applyfor=$this->getParam("applyfor", "");
        $goodat=$this->getParam("goodat", "");
        $contactno1=$this->getParam("contactno1", "");
        $contactno2=$this->getParam("contactno2", "");
        $certificate=$this->getParam("certificate", "");
        $remarks=$this->getParam("remarks", "");

        /*
        $agent=$this->getParam("sn", "");        
        $company=$this->getParam("sn", "");
        $race=$this->getParam("sn", "");
        */

        $data->setSn($sn);
        $data->setEeeno($eeeno);
        $data->setNamechs($namechs);    
        $data->setNameeng($nameeng);
        $data->setWpno($wpno);
        $data->setWpexpiry($wpexpiry);

        $data->setApplyfor($applyfor);
        $data->setApplyfor($applyfor);
        $data->setApplyfor($applyfor);
        $data->setApplyfor($applyfor);                        

        $data->setApplyfor($applyfor);
        $data->setGoodat($goodat);
        $data->setContactno1($contactno1);        
        $data->setContactno2($contactno2);        
        $data->setCertificate($certificate);        
        $data->setRemarks($remarks);
    
    }

    public function add1Action()
    {
        $this->view->id = 0;

        $sites = $this->_site->findAll();
        $this->view->sites = $sites;

        $this->findCompanies();
        $this->getWorktypes();

        $this->getCustominfo(0);
    }

    public function edit1Action()
    {
        $id = $this->_getParam("id");
        //echo "id=$id";
        $this->view->workerid = $id;
        $this->view->worker = $worker = $this->_worker->findOneBy(array("id"=>$id));
        $skillid = $worker->getWorkerskill();
        $cmyid = $worker->getWorkercompanyinfo();
        $familyid = $worker->getWorkerfamily();

        $this->view->skill  = $skill = $this->_workerskill->findOneBy(array("id"=>$skillid));
        $this->view->companyinfo = $companyinfo = $this->_workercompanyinfo->findOneBy(array("id"=>$cmyid));
        $this->view->family = $family = $this->_workerfamily->findOneBy(array("id"=>$familyid));

        $sites = $this->_site->findAll();
        $this->view->sites = $sites;

        $this->findCompanies();
        $this->getWorktypes();

        $this->getCustominfo($id);
    }

    public function submit1Action()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }

        $this->storeInfo($requests);
        $this->_files = $_FILES;
    }

    private function storePic($workerid)
    {// http://www.w3schools.com/php/php_file_upload.asp
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

    private function storeInfo1($requests)
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
