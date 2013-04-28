<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * The Page Helper provides a way to easy embed a CMS page or 
 * partial CMS page into a user visible page
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Synrgic_Helper_Page extends Zend_View_Helper_Abstract
{
    public $view;
    private $helper = new Synrgic_Models_Page();

    public function cms(\Synrgic\CMS\Page $page)
    {
        $this->_helper->setPage($page);
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        return $this;
    }

    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $output = $this->_helper->getPage()->getContent();
        return $output;
    }

}

?>

