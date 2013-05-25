<?php

//define('UPLOAD_BASE', APPLICATION_PATH. '/data/uploads');
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
    }

    public function indexAction()
    {
        //$result = $this->_worker->findAll();
        /*
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
            ->from('Synrgic\Infox\Worker', 'a')
           ->leftJoin('a.workercompanyinfo', 'c');
        $result = $qb->getQuery()->getResult();
        */

        // dql result: http://docs.doctrine-project.org/en/2.1/reference/dql-doctrine-query-language.html
        // embed select: http://msdn.microsoft.com/zh-cn/library/ms189575(v=sql.105).aspx
        $query = $this->_em->createQuery(
//        'select w, wc.hwage from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc'
//        'select w,wc.hwage,wc.companylable,wc.worktype, from Synrgic\Infox\Worker w JOIN w.workercompanyinfo wc'
                     'select w, wc.companylabel, wc.hwage, ws.worktype, ws.worklevel,
                     (select site.name from Synrgic\Infox\Site site where site.id = wc.site) as sitename
                     from Synrgic\Infox\Worker w LEFT JOIN w.workercompanyinfo wc LEFT JOIN w.workerskill ws'
                 );
        $result = $query->getResult();
        $this->view->result = $result;

        //echo $result[1][0]['nameeng'];
        //echo $result[1]['hwage'];
        //echo $result[1]['worktype'];
        //echo $result[1]['sitename'];

    }

    public function addAction()
    {
        $this->view->id = 0;

        $sites = $this->_site->findAll();
        $this->view->sites = $sites;
    }

    public function editAction()
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
    }

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $requests = $this->getRequest()->getPost();
        if(0)
        {
            var_dump($requests);
            return;
        }
/*
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

                if (file_exists($uploadpath . $_FILES["file"]["name"]))
                {
                    echo $_FILES["file"]["name"] . " already exists. ";
                }
                else
                {
                    move_uploaded_file($_FILES["file"]["tmp_name"],
                                       $uploadpath . $_FILES["file"]["name"]);
                    echo "Stored in: " . $uploadpath . $_FILES["file"]["name"];
                }
            }
        }
        else
        {
            //echo "Invalid file";
        }
*/
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
        }
        else if($mode = "Create")
        {
            $workerdata = new \Synrgic\Infox\Worker();
            $cmydata = new \Synrgic\Infox\Workercompanyinfo();
            $skilldata = new \Synrgic\Infox\Workerskill();
            $familydata = new \Synrgic\Infox\Workerfamily();
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
        echo "skill id=" . $skillid;

        // company info
        $companylabel = $requests["companylabel"];
        $hwage = $requests["hwage"];    // float
        $srvyears = $requests["srvyears"];
        $yrsinsing = $requests["yrsinsing"];

        $cmydata->setCompanylabel($companylabel);
        $cmydata->setHwage(floatval($hwage));

        $site = $requests["site"];
        if($site != "")
        {
            $onesite = $this->_site->findOneBy(array('name'=>$site));
            if($onesite)
            {
                $cmydata->setSite($onesite);
            }
        }
        $cmydata->setSrvyears(intval($srvyears));
        $cmydata->setYrsinsing(intval($yrsinsing));

        $this->_em->persist($cmydata);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }
        $cmyid = $cmydata->getId();
        echo "cmydata id=" . $cmyid;

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
        echo "familydata id=" . $fmyid;

        // connect skill,company,family with worker
        $workerdata->setWorkerskill($this->_workerskill->findOneBy(array("id"=>$skillid)));
        $workerdata->setWorkercompanyinfo($this->_workercompanyinfo->findOneBy(array("id"=>$cmyid)));
        $workerdata->setWorkerfamily($this->_workerfamily->findOneBy(array("id"=>$fmyid)));
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
    }


}
