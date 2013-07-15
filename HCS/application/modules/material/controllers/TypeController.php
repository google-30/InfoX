<?php

class Material_TypeController extends Zend_Controller_Action
{
    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_materialtype = $this->_em->getRepository('Synrgic\Infox\Materialtype');
 
    }

    public function indexAction()
    {
        $mains = $this->_materialtype->findBy(array("main"=>null));        
        $this->view->mains = $mains;
        
    }

}
