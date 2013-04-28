<?php

/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/

class Management_LayoutTestController extends Zend_Controller_Action
{
    private $em = null;

    public function init()
    {
        $this->_helper->_layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function indexAction() {
        $device = $this->getParam('device');
        $path = APPLICATION_PATH .  '/modules/default/layouts/scripts/' . $device;
        
        /*
        if($device=='tablet') {
        $settings = Zend_Registry::get('settings');
        $boardw = $settings->Tablet->AdvertBoardWidth->Value;
        $boardh = $settings->Tablet->AdvertBoardHeight->Value;
        $w = $settings->Tablet->AdvertWidth->Value;
        $h = $settings->Tablet->AdvertHeight->Value;
        $n = intval($boardh/$h);
        echo $n;
        exit(0);
        }
        */
        $layout = new Zend_Layout();
        $layout->setLayoutPath($path);
        $layout->setLayout('layout');
        $layout->content = '<h1>Layout: '.$device.'</h1>'
                         . 'click the actual menu item, your controller will be under management layout because you have logged in.'
                         . ' click <a href="/management">here</a> back to the management home';
        echo $layout->render();
    }
}
