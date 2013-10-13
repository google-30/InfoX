<?php
include 'InfoX/infox_common.php';
include 'InfoX/infox_project.php';
include 'InfoX/infox_user.php';
include 'InfoX/infox_worker.php';

class Worker_SalaryController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerdetails = $this->_em->getRepository('Synrgic\Infox\Workerdetails');
    }

    public function indexAction()
    {
        $this->view->sheetarr = $sheetarr = infox_worker::getSheetarr();
        $this->view->sheet = $requestsheet = $this->getParam("sheet",$sheetarr[0]);
        $this->view->workerarr = infox_worker::getworkerlistbysheet($requestsheet);
    }

    public function personalsalaryAction()
    {

    }

    public function salarybymonthAction()
    {
        infox_common::turnoffLayout($this->_helper);        
    }    
}
