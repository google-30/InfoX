<?php

class Project_ManageController extends Zend_Controller_Action
{
    private $_em;
    private $_supplier;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_humanres = $this->_em->getRepository('Synrgic\Infox\Humanresource');
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_role = $this->_em->getRepository('Synrgic\Infox\Role');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_application = $this->_em->getRepository('Synrgic\Infox\Application');

        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
        $this->_emachinery = $this->_em->getRepository('Synrgic\Infox\Emachinery');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $this->_workeronsite = $this->_em->getRepository('Synrgic\Infox\Workeronsite');
    }

    public function indexAction()
    {
        $maindata = $this->_site->findAll();
        $this->view->maindata = $maindata;
    }   

    public function addAction()
    {
        $this->findLeaders();
        $this->getCompanyinfo();
        $this->getGeneralContractors();
        $this->getSiteproperties();
        $this->getPermission1();
    } 

    public function editAction()
    {
        $this->findLeaders();
        $this->getCompanyinfo();
        $this->getGeneralContractors();
        $this->getSiteproperties();
        $this->getPermission1();

        $id = $this->getParam("id");
        //echo "id=$id<br>";
        $maindata = $this->_site->findOneBy(array("id"=>$id));
        $this->view->maindata = $maindata;
    } 

    public function deleteAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $id = $this->getParam("id");
        $data = $this->_site->findOneBy(array("id"=>$id));
    	$this->_em->remove($data);
        $this->_em->flush();        

        $this->_redirect("project/manage");    
    } 

    public function submitAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);
 
        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }        
       
        $mode = $this->getParam("mode", "Create");
        $id = $this->getParam("id", "0");
        $name = $this->getParam("name");
        $address = $this->getParam("address", "");        
        //$manager = $this->getParam("manager", 0);
        $start = $this->getParam("start", "");
        $stop = $this->getParam("stop", "");
        $remark = $this->getParam("remark", "");
        //$workerno = $this->getParam("workerno", "");
        $company = $this->getParam("company", 0);
        $contractor = $this->getParam("contractor", "");
        $property = $this->getParam("property", "");

        $leadersArr = $this->getParam("leaders", null);
        $leadersStr = "";
        if($leadersArr)
        {
            $leadersStr = implode(";", $leadersArr);
        }   

        //$permission1 = $this->getParam("permission1", "0");

        if($mode == "Create")
        {
            $data = new \Synrgic\Infox\Site(); 
        }
        else
        {
            $data = $this->_site->findOneBy(array("id"=>$id));
        }
 
        $data->setName($name);
        $data->setAddress($address);
        if($start!="")
        {
            $data->setStart(new Datetime($start));
        }
        if($stop!="")
        {
            $data->setStop(new Datetime($stop));
        }
        $data->setRemark($remark);
        //$data->setManager($this->_humanres->findOneBy(array("id"=>$manager)));
        //$data->setWorkerno($workerno);

        //$data->setLeader($this->_humanres->findOneBy(array("id"=>$leader)));
        $data->setLeaders($leadersStr);
    
        $companyobj = $this->_companyinfo->findOneBy(array("id"=>intval($company)));
        if(isset($companyobj))
        {
            $data->setCompany($companyobj);
        }

        $data->setContractor($contractor);
        $data->setProperty($property);

        $bPermission1 = ($permission1=="0") ? false : true;
        $data->setPermission1($bPermission1);

        $postr = $this->getParam("postr", "");
        $data->setPostr($postr);

        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        

        $this->_redirect("project/manage");
    } 

    public function sitedetailAction()
    {
        $this->getSiteDetails();
    }

    private function findLeaders()
    {
        $leaderrole = $this->_role->findOneBy(array("role"=>"leader"));        
        $leaders = $this->_humanres->findBy(array("role"=>$leaderrole));
        $this->view->leaders = $leaders;
        //$managers = $this->_humanres->findBy(array("position"=>"manager"));
        //$this->view->managers = $managers;
    }    

    private function getCompanyinfo()
    {
        $companyinfos = $this->_companyinfo->findAll();
        $this->view->companyinfos = $companyinfos;
    }

    public function addpartAction()
    {
        $this->turnoffview();

        $id = $this->getParam("id", 0);
        $partname = $this->getParam("partname", "");
        
        $data = $this->_site->findOneBy(array("id"=>$id));
        $parts = $data->getParts();
        $newparts = $partname . ";" . $parts ;
        $data->setParts($newparts);
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        
        
        echo "添加成功";
    }

    public function delpartAction()
    {
        $this->turnoffview();

        $id = $this->getParam("id", 0);
        $delpart = $this->getParam("delpart", "");

        $data = $this->_site->findOneBy(array("id"=>$id));
        $parts = $data->getParts();

        //$partsarr = explode(";", $parts);
        $pos = strpos($parts, $delpart);
        if($pos===false)
        {
            echo "fail to find this part name";
            return;
        }

        $newparts = substr($parts, 0, $pos) . substr($parts, $pos+strlen($delpart)+1);
        //echo $newparts;  
        $data->setParts($newparts);
        $this->_em->persist($data);
        try {
            $this->_em->flush();
        } catch (Exception $e) {
            var_dump($e);
            return;
        }        
        
        echo "删除成功";                               
    }

    private function turnoffview()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
    }

    private function turnofflayout()
    {
        $this->_helper->layout->disableLayout();   
    }

    public function partsdefineAction()
    {
        //$this->turnoffview();
        $this->_helper->layout->disableLayout();   
    }

    private function getGeneralContractors()
    {
        $label = "info01"; // ... use info01 to label contractor info
        
        $infoobj = $this->_miscinfo->findOneBy(array("label"=>$label));
        $values = $infoobj ? $infoobj->getValues() : "";
        $valueArr = ($values != "") ? explode(";", $values) : array();

        $this->view->contractors = $valueArr;
    }

    private function getSiteproperties()
    {
        $label = "info02";
        
        $infoobj = $this->_miscinfo->findOneBy(array("label"=>$label));
        $values = $infoobj ? $infoobj->getValues() : "";
        $valueArr = ($values != "") ? explode(";", $values) : array();

        $this->view->siteproperties = $valueArr;
    }

    // allow or not leader to apply materials 
    private function getPermission1()
    {
        $permission1arr = array(0=>"禁止材料申请",1=>"允许材料申请");
        $this->view->permission1arr = $permission1arr;
    }

    public function workerlistAction()
    {
        $this->getSiteDetails();

        $id = $this->getParam("id", 0);
        $siteobj = $this->_site->findOneBy(array("id"=>$id));

        $records = $this->_workeronsite->findBy(array("site"=>$siteobj));
        $sn=0;
        foreach($records as $tmp)
        {
            $tmp["sn"] = ++$sn;
        }
        $this->view->records = $records;
    }

    public function emachineryAction()
    {
        $this->getSiteDetails();     

        $siteid = $this->getParam("id",0);
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));
        $emachineries = $this->_emachinery->findBy(array("site"=>$siteobj));   

        $this->view->emachineries = $emachineries;
    }

    public function applistAction()
    {
        $this->getSiteDetails();  

        $siteid = $this->getParam("id",0);
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));
        $applist = $this->_application->findBy(array("site"=>$siteobj));   

        $this->view->applist = $applist;        
    }

    public function allmaterialsAction()
    {
        $this->getSiteDetails();  

        $siteid = $this->getParam("id",0);
        $siteobj = $this->_site->findOneBy(array("id"=>$siteid));
        $applist = $this->_application->findBy(array("site"=>$siteobj));   

        //$this->view->applist = $applist;     
        $idArr = array();
        foreach($applist as $tmp)
        {
            $id = $tmp->getId();            
            $idArr[] = $id;
        }   
        if(count($idArr)==0)
        {
            return;
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->add('select', 'm')
           ->add('from', 'Synrgic\Infox\Matappdata m');
        $qb->add('where', $qb->expr()->in('m.application', $idArr));
        $query = $qb->getQuery();
        $result = $query->getResult();

        $allmats = array();
        foreach($result as $tmp)
        {
            $materialid = $tmp->getMaterialid();
            $amount = $tmp->getAmount();
            $longname = $tmp->getLongname();
            $price = $tmp->getRate();
            $unit_manual = $tmp->getUnit();

            $matobj = $this->_material->findOneBy(array("id"=>$materialid));
            $unit = $matobj ? $matobj->getUnit() : $unit_manual ;

            $found = false;

            /* copy array element to $mat
            foreach($allmats as $mat)
            {
                $matid = $mat['materialid'];
                if($matid == $materialid)
                {                                        
                    $mat['amount'] += $amount;
                    echo "mat amount=" . $mat['amount'] . ",amount=$amount";       
                    $found = true;
                    break;
                }        
            }
            */

            // this is ref
            for($i=0; $i<count($allmats); $i++)
            {
                $matid = $allmats[$i]['materialid'];
                if($matid == $materialid)
                {                                        
                    $allmats[$i]['amount'] += $amount;
                    //echo "mat amount=" . $allmats[$i]['amount'] . ",amount=$amount";       
                    $allmats[$i]['totalprice'] = $allmats[$i]['amount'] * $allmats[$i]['price'];        
                    $found = true;
                    break;
                }        
            }

            if($found)
            {
                $found = false;
                continue;
            }
            else
            {
                $tmpArr = array();
                $tmpArr['materialid'] = $materialid;
                $tmpArr['amount'] = $amount;
                $tmpArr['longname'] = $longname;
                $tmpArr['price'] = $price;                
                $tmpArr['unit'] = $unit;
                $tmpArr['totalprice'] = $price * $amount;

                $allmats[] = $tmpArr;
            }
        }

        $this->view->allmats = $allmats;
    }

    private function getSiteDetails()
    {
        $this->findLeaders();

        $id = $this->getParam("id",0);
        //echo "id=$id<br>";
        $siteobj = $this->_site->findOneBy(array("id"=>$id));
        $this->view->maindata = $siteobj;

        $this->view->applications = $this->_application->findBy(array("site"=>$siteobj));

        // leaders names
        $namesStr = "";
        $selLeadersIdStr = $siteobj->getLeaders();
        if($selLeadersIdStr)
        {
            $selLeadersIdArr = explode(";", $selLeadersIdStr);
            foreach($selLeadersIdArr as $tmp)
            {
                $data = $this->_humanres->findOneBy(array("id"=>$tmp));
                $name = $data ? $data->getName() : "&nbsp;";
                $namesStr .= $name .  "；&nbsp;";
            } 
        }       
        $this->view->leaders = $namesStr;

    }

    public function siteinfoAction()
    {
        $this->turnofflayout();   
        $this->getSiteDetails();
    }
}
