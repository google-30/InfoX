<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Browser_IndexController extends Zend_Controller_Action
{
    public function init()
    {
        //echo 'This is init.';        
    }

    public function indexAction()
    {
        $device = strtolower(\Synrgic\Device::probeDeviceTypeAsString());
        //echo "device=$device\n";

        $this->view->device = $device;        
    }
}

