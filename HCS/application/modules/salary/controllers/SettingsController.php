<?php
include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';
include 'InfoX/infox_salary.php';

class Salary_SettingsController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_setting = $this->_em->getRepository('Synrgic\Infox\Setting');
    }

    public function indexAction()
    {
        $settingnames = infox_salary::getSettingArray();
        /*
        array("cotmultiple", "botmultiple", "workerfood", "leaderfood", 
            "absencedays", "absencelow", "absencehigh", "absencetotal");
        */
        foreach($settingnames as $name)
        {
            switch($name)
            {
                case "cotmultiple";
                    $value = infox_salary::getSettingBySectionName("salary", "cotmultiple");
                    $this->view->cotmultiple = $value;
                    break;
                case "botmultiple";
                    $value = infox_salary::getSettingBySectionName("salary", "botmultiple");
                    $this->view->botmultiple = $value;
                    break;
                case "workerfood";
                    $value = infox_salary::getSettingBySectionName("salary", "workerfood");
                    $this->view->workerfood = $value;
                    break;
                case "leaderfood";
                    $value = infox_salary::getSettingBySectionName("salary", "leaderfood");
                    $this->view->leaderfood = $value;
                    break;
                case "absencedays";
                    $value = infox_salary::getSettingBySectionName("salary", "absencedays");
                    $this->view->absencedays = $value;
                    break;
                case "absencelow";
                    $value = infox_salary::getSettingBySectionName("salary", "absencelow");
                    $this->view->absencelow = $value;
                    break;
                case "absencehigh";
                    $value = infox_salary::getSettingBySectionName("salary", "absencehigh");
                    $this->view->absencehigh = $value;
                    break;
                case "absencetotal";
                    $value = infox_salary::getSettingBySectionName("salary", "absencetotal");
                    $this->view->absencetotal = $value;
                    break;
                case "cbasic";
                    $value = infox_salary::getSettingBySectionName("salary", "cbasic");
                    $this->view->cbasic = $value;
                    break;
                case "bbasic";
                    $value = infox_salary::getSettingBySectionName("salary", "bbasic");
                    $this->view->bbasic = $value;
                    break;
                    
                }
        }
    }

    public function submitAction()
    {
        infox_common::turnoffView($this->_helper);
        $requests = $this->getRequest()->getPost();
        if(0) { var_dump($requests); return; }
        
        $settingnames = infox_salary::getSettingArray();
        foreach($settingnames as $name)
        {   
            $value = $this->getParam($name, "");
            infox_salary::setSettingBySectionName("salary", $name, $value);
        }

        $this->redirect("/salary/settings/");
    }

}
