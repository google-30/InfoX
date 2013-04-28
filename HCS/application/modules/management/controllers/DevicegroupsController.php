<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_DevicegroupsController extends Zend_Controller_Action
{
    private $_em;

    public function init()
    {
	$this->_em = Zend_Registry::get('em');
    }

    public function indexAction()
    {
        $data = $this->_em->getRepository('\Synrgic\DeviceGroup')->findAll();
        $this->view->data = $data;
    }

}

