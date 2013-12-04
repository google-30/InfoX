<?php

include 'InfoX/infox_common.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PoController
 *
 * @author philip
 */
class Material_PoController extends Zend_Controller_Action {

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
        $appid = $this->getParam("appid", 0);
        if($appid == 0)
        {
            echo "please check app id or report to philip.zhou.2009@gmail.com";            
        }
        
        $appobj = $this->_application->findOneBy(array("id"=>$appid));
        $this->view->application = $appobj;
        
        $matapps = $this->_matappdata->findBy(array("application" => $appobj));
        $this->view->matapps = $matapps;
        $this->view->role = infox_common::getUsername(); //$this->getUserRole();
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

}
