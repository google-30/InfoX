<?php

//define('UPLOAD_BASE', APPLICATION_PATH. '/data/uploads');
define('UPLOAD_WORKER', APPLICATION_PATH. '/data/uploads/workers/images/');
class Worker_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_worker;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
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
        //$id = $this->_getParam("id"); 
        //echo "id=$id";
        //$loginForm = new Synrgic_Forms_Login();
        //$this->view->workerform = $loginForm;
        //$uploadform = new Synrgic_Forms_Upload();
        //$this->view->workerform = $uploadform;
        $this->view->id = 0;
        
    }

    public function editAction()
    {
        $id = $this->_getParam("id"); 
        //echo "id=$id";
        $form = new Synrgic_Forms_Worker();

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {

                // success - do something with the uploaded file
                $uploadedData = $form->getValues();
                $fullFilePath = $form->file->getFileName();

                Zend_Debug::dump($uploadedData, '$uploadedData');
                Zend_Debug::dump($fullFilePath, '$fullFilePath');

                // was a file uploaded?
                if($form->file->isUploaded()) {
                    //$form->file->receive(); // move to the specified place
                    $location = $form->file->getFileName();
                    Zend_Debug::dump($location, '$location');
                }

                echo "done";
                exit;

            } else {
                $form->populate($formData);
            }
        }

        $this->view->workerform = $form;
    }
    
    public function submitAction()
    {     
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        if(1)
        {
            $requests = $this->getRequest()->getPost();
            //var_dump($requests);
            //return;
        }            

        $this->storeInfo($requests);

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
          echo "Invalid file";
          }

    }

    private function storeInfo($requests)
    {
        $mode = $requests["mode"];

        if($mode == "Edit")
        {
            $workerid = $requests["workerid"];
            $skillid = $requests["skillid"];
            $companyinfoid = $requests["companyinfoid"];
            $familyid = $requests["familyid"];
        }       
        else if($mode = "Create")
        {
            $workerdata = new \Synrgic\Infox\Worker();
                
        }
        else
        {
            echo "unknown store mode, please check.";
            return;
        }
        
        // TODO: worker
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
        $workerdata->setPassexp(new DateTime($passexp));
        $workerdata->setPassport($passport);
        $workerdata->setPassportexp(new DateTime($passportexp));
        $workerdata->setGender($gender);
        $workerdata->setAge(intval($age));
        $workerdata->setBirth(new DateTime($birth));
        $workerdata->setMarital($marital);
        $workerdata->setAddress($address);
        $workerdata->setHometown($hometown);        

        $this->_em->persist($workerdata);
        try {
            $this->_em->flush(); 
        } catch (Exception $e){
            var_dump($e);
            return;
        }             

        // TODO: skill
        $worktype = $requests["worktype"];
        $worklevel = $requests["worklevel"];
        $education = $requests["education"];
        $pastwork = $requests["pastwork"];
        $skill1 = $requests["skill1"];
        $skill2 = $requests["skill2"];
        $drvlic = $requests["drvlic"];   
        // date
        $securityexp = $requests["securityexp"];

        // TODO: company info
        $companylabel = $requests["companylabel"];
        $hwage = $requests["hwage"];
        // todo
        $site = $requests["site"]; 
        $srvyears = $requests["srvyears"];
        $yrsinsing = $requests["yrsinsing"];

        // TODO: family 
        $homeaddr = $requests["homeaddr"];
        $member1 = $requests["member1"];
        $contact1 = $requests["contact1"];
        $member2 = $requests["member2"];
        $contact2 = $requests["contact2"];
        $member3 = $requests["member3"];
        $contact3 = $requests["contact3"];


    }

}
