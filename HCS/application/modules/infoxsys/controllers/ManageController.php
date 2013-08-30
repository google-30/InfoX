<?php

class Infoxsys_ManageController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_worker = $this->_em->getRepository('Synrgic\Infox\Worker');
        $this->_site = $this->_em->getRepository('Synrgic\Infox\Site');
        $this->_workerskill = $this->_em->getRepository('Synrgic\Infox\Workerskill');
        $this->_workercompanyinfo = $this->_em->getRepository('Synrgic\Infox\Workercompanyinfo');
        $this->_workerfamily = $this->_em->getRepository('Synrgic\Infox\Workerfamily');
        $this->_companyinfo = $this->_em->getRepository('Synrgic\Infox\Companyinfo');
        $this->_miscinfo = $this->_em->getRepository('Synrgic\Infox\Miscinfo');
    }

    public function indexAction()
    {

    }
}
