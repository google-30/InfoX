<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_IndexController extends Zend_Controller_Action
{

    public function init()
    {
    }

    public function indexAction()
    {
	$this->_forward('index','dashboard');
    }

}

