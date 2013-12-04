<?php

include 'InfoX/infox_common.php';

class Material_AppmanageController extends Zend_Controller_Action {

    private $_em;
    private $_material;

    public function init() {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        $this->_application = $this->_em->getRepository('Synrgic\Infox\Application');
        $this->_matappdata = $this->_em->getRepository('Synrgic\Infox\Matappdata');
        $this->_supplyprice = $this->_em->getRepository('Synrgic\Infox\Supplyprice');
        $this->_user = $this->_em->getRepository('Synrgic\User');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_humanresource = $this->_em->getRepository('Synrgic\Infox\Humanresource');
    }

    public function indexAction() {
        $maindata = $this->_application->findAll();
        $this->view->maindata = $maindata;
        
        $pendingapps = array();
        $approvedapps = array();
        $giveupapps = array();
        
        foreach($maindata as $appobj)
        {
            $state = $appobj->getState();
            if($state == 1)
            {
                $approvedapps[] = $appobj;
            }
            else if($state == 2)
            {
                $giveupapps[] = $appobj;
            }
            else
            {
                $pendingapps[] = $appobj;
            }
        }
        
        $this->view->pendingapps = $pendingapps;
        $this->view->approvedapps = $approvedapps;
        $this->view->giveupapps = $giveupapps;
    }

    public function appdetailAction() {
        $id = $this->getParam("id");
        $appobj = $this->_application->findOneBy(array("id" => $id));
        $this->view->application = $appobj;

        $matapps = $this->_matappdata->findBy(array("application" => $appobj));
        $this->view->matapps = $matapps;
        $this->view->role = $this->getUserRole();
        $this->view->sites = $sites = $this->_site->findAll();
        $this->view->humanres = $this->_humanresource->findAll();

        $total = 0;
        foreach ($matapps as $tmp) {
            $amount = $tmp->getAmount();
            $rate = $tmp->getRate();
            $quantity = $tmp->getQuantity() ? $tmp->getQuantity() : 0;

            $amount = $amount ? $amount : 0;
            $rate = $rate ? $rate : 0;
            $total += $amount * $rate * $quantity;
        }
        $this->view->totalprice = $total;

        $siteobj = $appobj->getSite();
        if ($siteobj) {
            $this->view->sitename = $siteobj->getName();
            $this->view->siteid = $siteobj->getId();

            $company = $siteobj->getCompany();
            $this->view->company = $company;
            if ($company) {
                $cmynamechs = $company->getNamechs();
                $cmynameeng = $company->getNameeng();
                $cmyname = $cmynamechs . "/" . $cmynameeng;
                $this->view->cmyname = $cmyname;
            }
        }
    }

    public function appeditAction() {
        $id = $this->getParam("id");
        $appobj = $this->_application->findOneBy(array("id" => $id));
        $this->view->application = $appobj;

        $matapps = $this->_matappdata->findBy(array("application" => $appobj));
        $this->view->matapps = $matapps;

        $matappsInSys = array();
        $matappsNotInSys = array();
        foreach ($matapps as $tmp) {
            $insys = $tmp->getMaterialinsys();
            if ($insys) {
                $matappsInSys[] = $tmp;
            } else {
                $matappsNotInSys[] = $tmp;
            }
        }

        $this->view->matappsInSys = $matappsInSys;
        $this->view->matappsNotInSys = $matappsNotInSys;

        $this->view->role = $this->getUserRole();

        $this->view->sites = $sites = $this->_site->findAll();
        $this->view->humanres = $this->_humanresource->findAll();
    }

    public function appdelAction() {
        $this->turnoffview();

        $id = $this->getParam("id", 0);
        $appobj = $this->_application->findOneBy(array("id" => $id));
        if ($appobj) {
            // del matapp first
            $results = $this->_matappdata->findBy(array("application" => $appobj));
            foreach ($results as $tmp) {
                $this->_em->remove($tmp);
            }
            $this->_em->remove($appobj);
            $this->_em->flush();
        }

        $this->_redirect("material/manage/appmanage");
    }

    public function appmatdelAction() {
        $this->turnoffview();

        $id = $this->getParam("id", 0);
        $appmatobj = $this->_matappdata->findOneBy(array("id" => $id));

        $appid = 0;
        if ($appmatobj) {
            $appobj = $appmatobj->getApplication();
            if ($appobj) {
                $appid = $appobj->getId();
                $this->appUpdateByObject($appobj);
                //$appobj->setUpdatedate(new Datetime('now'));
                //$this->_em->persist($appobj);
            }

            $this->_em->remove($appmatobj);
            $this->_em->flush();
        }

        $url = "material/manage/appmanage";
        if ($appid != 0) {
            $url = "/material/manage/appedit/id/" . $appid;
        }
        $this->_redirect($url);
    }

    public function updatematappAction() {
        infox_common::turnoffView($this->_helper);

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $requests['id'];
        $amount = $requests['amount'];
        $supplypriceid = $requests['supplypriceid'];
        $sitepart = $this->getParam("sitepart", "未定义");
        //$remark = $requests['remark'];
        //$remark = $this->getParam("remark", "");
        //$price = $requests['price'];
        //$price = $this->getParam("price", 0);

        $supplyprice = $this->_supplyprice->findOneBy(array("id" => $supplypriceid));
        $rate = $supplyprice->getRate();
        $quantity = $supplyprice->getQuantity();
        $unit = $supplyprice->getUnit();
        $supplier = $supplyprice->getSupplier();

        // update matappdata, update application
        $matappobj = $this->_matappdata->findOneBy(array("id" => $id));

        $matappobj->setAmount($amount);
        $matappobj->setSupplier($supplier);
        $matappobj->setRate($rate);
        $matappobj->setQuantity($quantity);
        $matappobj->setUnit($unit);
        $matappobj->setSitepart($sitepart);
        $this->_em->persist($matappobj);

        $appobj = $matappobj->getApplication();
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);

        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "更新成功";
    }

    public function updatematappAction0() {
        $this->turnoffview();

        $requests = $this->getRequest()->getPost();
        if (0) {
            var_dump($requests);
            return;
        }

        $id = $requests['id'];
        $amount = $requests['amount'];

        //$remark = $requests['remark'];
        $remark = $this->getParam("remark", 0);

        $supplierid = $requests['supplierid'];

        //$price = $requests['price'];
        $price = $this->getParam("price", 0);

        // update matappdata, update application
        $matappobj = $this->_matappdata->findOneBy(array("id" => $id));

        $matappobj->setAmount($amount);
        $matappobj->setRemark($remark);
        $supplier = $this->_supplier->findOneBy(array("id" => $supplierid));
        $matappobj->setSupplier($supplier);

        $insys = $matappobj->getMaterialinsys();
        if (!$price && $insys) {// get default supplier price, and update into matapps
            $matid = $matappobj->getMaterialid();
            $matobj = $this->_material->findOneBy(array("id" => $matid));

            $suppriceobj = $this->_supplyprice->findOneBy(array("material" => $matobj, "supplier" => $supplier));
            $price = $suppriceobj ? $suppriceobj->getPrice() : $price;
        }
        $matappobj->setPrice($price);

        $sitepart = $this->getParam("sitepart", "未定义");
        $matappobj->setSitepart($sitepart);

        $this->_em->persist($matappobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        $appobj = $matappobj->getApplication();
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "更新成功";
    }

    public function submitmatappsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $siteid = $this->getParam("siteid", 0);
        $applicantid = $this->getParam("applicantid", 0);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id" => $appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[0]);

        // TODO: update site - this function really needed? 
        $siteobj = $this->_site->findOneBy(array("id" => $siteid));
        if ($siteobj) {
            $appobj->setSite($siteobj);
        }

        // update applicant
        $applicantobj = $this->_humanresource->findOneBy(array("id" => $applicantid));
        //if($applicantobj)
        {
            $appobj->setApplicant($applicantobj);
        }

        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "提交成功";
    }

    public function reviewmatappsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id" => $appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[2]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "该申请设成未审核状态";
    }

    public function rejectmatappsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        //echo "appid=$appid";

        $statusArr = $this->getStatusArr(); //array("提交", "审核", "未审核", "批准", "退回");

        $appobj = $this->_application->findOneBy(array("id" => $appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus1($statusArr[4]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "该申请已经退回";
    }

    public function approvematappsAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $appid = $this->getParam("appid", 0);
        $statusArr = $this->getStatusArr();

        $appobj = $this->_application->findOneBy(array("id" => $appid));
        $appobj->setUpdatedate(new Datetime('now'));
        $appobj->setStatus2($statusArr[3]);
        $this->_em->persist($appobj);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }

        echo "该申请已经批准";
    }

    public function previewformAction() {
        $this->turnofflayout();

        $id = $this->getParam("id", 0);
        if (!$id) {
            $this->turnoffrenderer();
            echo "app id, please.";
            return;
        }

        $appobj = $this->_application->findOneBy(array("id" => $id));
        /*
          $siteobj = $appobj->getSite();
          if($siteobj)
          {
          $cmyobj = $siteobj->getCompany();
          if($cmyobj)
          {
          // company info
          $this->view->cmyfullnameeng = $cmyfullnameeng = $cmyobj->getFullnameeng();
          $this->view->cmyaddr = $cmyaddr = $cmyobj->getAddress();
          $this->view->cmyphone = $cmyphone = $cmyobj->getPhone();
          $this->view->cmyfax = $cmyfax = $cmyobj->getFax();
          $this->view->cmyemail = $cmyemail = $cmyobj->getEmail();

          $cmycontact="Tel:" . $cmyphone . "&nbsp;&nbsp;Fax:" . $cmyfax . "&nbsp;&nbsp;Email:" . $cmyemail;
          $this->view->cmycontact=$cmycontact;
          }

          // site
          $this->view->sitename = $sitename = $siteobj->getName();
          }
         */
        $this->getCompanyinfo($appobj);

        // date
        $date = new Datetime("now");
        $this->view->date = $date->format("Y-m-d");

        // requested/applied materials
        $this->view->matapps = $this->_matappdata->findBy(array("application" => $appobj));

        // TODO: contact, manager
        // TODO: ref     
        // site
        $siteobj = $appobj->getSite();
        $this->getSiteinfo($siteobj);

        $this->view->materialentity = $this->_material;
    }

    public function previeworderAction() {
        $this->turnofflayout();
        $id = $this->getParam("id", 0);
        if (!$id) {
            $this->turnoffrenderer();
            echo "error: app id.";
            return;
        }

        // application
        $appobj = $this->_application->findOneBy(array("id" => $id));

        // company
        $this->getCompanyinfo($appobj);

        // supplier
        $supplierid = $this->getParam("supplier", 0);
        $supplierobj = $this->_supplier->findOneBy(array("id" => $supplierid));
        $this->view->supplier = $supplierobj;
        $this->getSupplierinfo($supplierobj);

        // materials
        $this->view->matapps = $this->_matappdata->findBy(array("application" => $appobj, "supplier" => $supplierobj));

        // site
        $siteobj = $appobj->getSite();
        $this->getSiteinfo($siteobj);
        $this->view->site = $siteobj;

        $user = Zend_Auth::getInstance();
        if (isset($user) && $user->hasIdentity()) {
            $username = $user->getIdentity()->getName();
            $userobj = $this->_user->findOneBy(array("username" => $username));
            $staffobj = $user->getIdentity()->getHumanresource();

            $this->view->staffname = $staffobj ? $staffobj->getNameeng() : "";
            $this->view->staffphone = $staffobj ? $staffobj->getPhone1() : "";
        }

        $this->view->materialrepo = $this->_material;
    }

    private function getSiteinfo($obj) {
        $this->view->sitename = $sitename = $obj->getName();
        $this->view->siteaddress = $obj->getAddress();

        $leaders = $obj->getLeaders();
        if ($leaders == null || $leaders == "") {
            $this->view->siteleaders = "&nbsp;";
            $this->view->siteleadersphones = "&nbsp;";
            return;
        }
        $leadersArr = explode(";", $leaders);
        $leadersStr = "";
        $phonesStr = "";
        foreach ($leadersArr as $leaderid) {
            $leaderobj = $this->_humanresource->findOneBy(array("id" => $leaderid));
            $leadersStr .= $leaderobj->getNameeng() . ";";
            $phonesStr .= $leaderobj->getPhone1() . ";";
        }

        $this->view->siteleaders = $leadersStr;
        $this->view->siteleadersphones = $phonesStr;
    }

    private function getSupplierinfo($obj) {
        $this->view->suppostring = $postring = $obj->getPostring();
        $this->view->supfullname = $fullname = $obj->getFullname();
        $this->view->supattn = $attn = $obj->getAttn();
        $this->view->supattnphone = $obj->getAttnphone() ? $obj->getAttnphone() : "";
        $this->view->supofficephone = $officephone = $obj->getOfficephone();
        $this->view->supfax = $fax = $obj->getFax();
    }

    private function getCompanyinfo($appobj) {
        $siteobj = $appobj->getSite();
        if ($siteobj) {
            $cmyobj = $siteobj->getCompany();
            if ($cmyobj) {
                // company info
                $this->view->cmyfullnameeng = $cmyfullnameeng = $cmyobj->getFullnameeng();
                $this->view->cmyaddr = $cmyaddr = $cmyobj->getAddress();
                $this->view->cmyphone = $cmyphone = $cmyobj->getPhone();
                $this->view->cmyfax = $cmyfax = $cmyobj->getFax();
                $this->view->cmyemail = $cmyemail = $cmyobj->getEmail();
                $this->view->cmypostring = $postring = $cmyobj->getPostring();

                $cmycontact = "Tel:" . $cmyphone . "&nbsp;&nbsp;Fax:" . $cmyfax . "&nbsp;&nbsp;Email:" . $cmyemail;
                $this->view->cmycontact = $cmycontact;
            }

            // site
            //$this->view->sitename = $sitename = $siteobj->getName();
        }
    }

    private function getStatusArr() {
        $statusArr = array("提交", "审核", "未审核", "批准", "退回");
        return $statusArr;
    }

    private function getUsername() {
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $username = $auth->getIdentity()->username;
            //echo "username=$username<br>";            
            return $username;
        } else {
            $ted = "Ted, u here~";
            //echo "username=$ted<br>";            
            return $ted;
        }
    }

    private function getUserRole() {
        $username = $this->getUsername();
        $user = $this->_user->findOneBy(array("username" => $username));
        $role = $user ? $user->getRole() : "";
        return $role;
    }

    private function turnoffview() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    private function turnofflayout() {
        $this->_helper->layout->disableLayout();
    }

    private function turnoffrenderer() {
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    private function getSuppliers() {
        $this->view->suppliers = $this->_supplier->findAll();
    }

    private function getSupplyprice($id) {
        $supplyprice = array();
        $suppliers = $this->_supplier->findAll();

        $material = $this->_material->findOneBy(array("id" => $id));
        if ($material) {
            $supplyprice = $this->_supplyprice->findBy(array("material" => $material));
        }

        $this->view->supplyprice = $supplyprice;
    }

    private function getTypes() {
        $types = $this->_materialtype->findAll();
        $this->view->types = $types;
    }

    private function appUpdateById($id) {
        $appobj = $this->_application->findOneBy(array("id" => $id));
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);
        $this->_em->flush();
    }

    private function appUpdateByObject($appobj) {
        $appobj->setUpdatedate(new Datetime('now'));
        $this->_em->persist($appobj);
        $this->_em->flush();
    }

    public function appapproveAction() {
        infox_common::turnoffView($this->_helper);

        $id = $this->getParam("id", 0);
        $application = $this->_application->findOneBy(array("id" => $id));
        $state = 1;
        $this->changeAppState($id, $state);

        $this->redirect("/material/appmanage/");
    }

    public function appgiveupAction() {
        infox_common::turnoffView($this->_helper);

        $id = $this->getParam("id", 0);
        $state = 2;
        $this->changeAppState($id, $state);
        $this->redirect("/material/appmanage/");
    }

    public function apppendingAction() {
        infox_common::turnoffView($this->_helper);

        $id = $this->getParam("id", 0);
        $state = 0;
        $this->changeAppState($id, $state);
        $this->redirect("/material/appmanage/");
    }    
    
    private function changeAppState($id, $state) {
        $application = $this->_application->findOneBy(array("id" => $id));
        $application->setState($state);
        $this->_em->persist($application);
        $this->_em->flush();

        $this->redirect("/material/appmanage/");
    }

    // for PO
    public function polistAction() {
        infox_common::turnoffView($this->_helper);
        $appid = $this->getParam("id");
        $url = "/material/po?appid=$appid";
        $this->redirect($url);
    }

}
