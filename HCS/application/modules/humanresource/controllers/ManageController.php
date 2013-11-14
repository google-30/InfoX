<?php

class Humanresource_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_humanres;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_humanres = $this->_em->getRepository('Synrgic\Infox\Humanresource');
        $this->_users = $this->_em->getRepository('Synrgic\User');
        $this->_role =  $this->_em->getRepository('Synrgic\Infox\Role');    
    }

    public function indexAction()
    {
        $humanres = $this->_humanres->findAll();
        $this->view->humanres = $humanres;        
    }   

    public function addAction()
    {
        $this->view->roles = $this->_role->findAll();        
    } 

    public function editAction()
    {
        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $maindata = $this->_humanres->findOneBy(array("id"=>$id));
        $this->view->maindata = $maindata;

        $this->view->roles = $this->_role->findAll();                
    } 

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->getParam("id");
        $data = $this->_humanres->findOneBy(array("id"=>$id));
    	$this->_em->remove($data);
        $this->_em->flush();        

        $this->_redirect("humanresource/manage");    
    } 

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
 
        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }        
       
        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "0");
        $name = $this->getParam("name","");
        $nameeng = $this->getParam("nameeng","");

        $phone1 = $this->getParam("phone1", "");
        $phone2 = $this->getParam("phone2", "");
        $email1 = $this->getParam("email1", "");
        $email2 = $this->getParam("email2", "");
        $othercontact = $this->getParam("othercontact", "");
        $remark = $this->getParam("remark", "");

        // TODO: positions in a entity
        //$position = $this->getParam("position", "");
        $roleid = $this->getParam("role", "0");

        $username = $this->getParam("username", "");
        $password = $this->getParam("password", "");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Humanresource(); 
        }
        else
        {
            $data = $this->_humanres->findOneBy(array("id"=>$id));
        }
 
        $data->setDate(new Datetime("now"));       
        $data->setName($name);
        $data->setNameeng($nameeng);
        $data->setPhone1($phone1);
        $data->setPhone2($phone2);
        $data->setEmail1($email1);
        $data->setEmail2($email2);
        $data->setOthercontact($othercontact);
        $data->setRemark($remark);

        // TODO
        $roleobj = $this->_role->findOneBy(array("id"=>$roleid));
        if($roleobj)
        {
            $data->setRole($roleobj);
        }        

        $data->setUsername($username);
        $data->setPassword($password);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        
        
        // sync data with user table
        if($username!="" && $password!="")
        {
            if($mode == "Create")
            {
                $user = new \Synrgic\User();    
            }
            else
            {
                $user = $this->_users->findOneBy(array("humanresource"=>$data));
                if(is_null($user))
                {
                    $user = new \Synrgic\User();    
                }
            }

            $user->setUsername($username);
        	$pwhelper = new Synrgic_Models_PasswordHelper();
        	$cryptedPassword = $pwhelper->cryptPassword($password);
	        $user->setPassword($cryptedPassword);

            $user->setName($name);
            $user->setDisabled(false);

            $rolestr = "";
            if($roleobj)
            {
                $rolestr = $roleobj->getRole();
            }
            $user->setRole($rolestr);        

	        $language = $this->_em->getRepository('\Synrgic\Language')->findOneByName("Chinese");
	        $user->setpreferredLanguage($language);                    

            $user->setHumanresource($data);

            $this->_em->persist($user);
            try {
                $this->_em->flush();
            } catch (Exception $e) {
                var_dump($e);
                return;
            }
        }

        $this->_redirect("humanresource/manage");
    } 
    

}
