<?php
include 'InfoX/infox_project.php';

class Project_AttendanceController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_em = Zend_Registry::get('em');        
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
    }

    public function indexAction()
    {
        
    }


}
