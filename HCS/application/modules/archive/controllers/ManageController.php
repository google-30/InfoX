<?php

define('UPLOAD_SW', APPLICATION_PATH. '/data/uploads/archives/softwares/');
define('UPLOAD_DOC', APPLICATION_PATH. '/data/uploads/archives/documents/');
class Archive_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_archive;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_archive = $this->_em->getRepository('Synrgic\Infox\Archive');
    }

    public function indexAction()
    {
        $softwares = $this->_archive->findBy(array("type"=>"sw"));
        $this->view->softwares = $softwares;
        $documents = $this->_archive->findBy(array("type"=>"doc"));
        $this->view->documents = $documents;
    }

    public function addAction()
    {
            
    }

    public function editAction()
    {
        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $archive = $this->_archive->findOneBy(array("id"=>$id));
        $this->view->archive = $archive;
    }

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->getParam("id");
        $data = $this->_archive->findOneBy(array("id"=>$id));
    	$this->_em->remove($data);
        $this->_em->flush();        

        $this->_redirect("archive/manage");
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
       
        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "0");
        $title = $this->getParam("title1");
        $remark = $this->getParam("remark");
        //$path = $this->getParam("file", "");
        $type = $this->getParam("type", "doc");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Archive(); 
        }
        else
        {
            $data = $this->_archive->findOneBy(array("id"=>$id));
        }
 
        $data->setUpdate(new Datetime("now"));       
        $data->setTitle($title);
        $data->setRemark($remark);
        $data->setType($type);
        //$data->setPath($path);
        $data->setSize(0);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        $id = $data->getId();
        $this->uploadarchive($id, $type);    

        $this->_redirect("archive/manage");
    }

    private function uploadarchive($id, $type)
    {
        echo "<br>";
        $newfile = "";   
        if($type == "doc")
        {
            $uploadpath = UPLOAD_DOC;
            $downloadpath = "/archives/documents/";
        }
        else
        {
            $uploadpath = UPLOAD_SW;
            $downloadpath = "/archives/softwares/";
        }

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
                $newfile = $id . "_" . $_FILES["file"]["name"];
                $newpath = $uploadpath . $newfile;
                echo "Stored in: " . $newpath;
                move_uploaded_file($_FILES["file"]["tmp_name"], $newpath);
            }
        }
        echo "<br>";   

        if($newfile != "")
        {
            $data = $this->_archive->findOneBy(array('id'=>$id));
            $path = $downloadpath . $newfile; 
            $data->setPath($path);        
            $this->_em->persist($data);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
        }                       
    }
}
