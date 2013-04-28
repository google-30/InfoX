<?php

/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/
class Synrgic_Plugin_ViewSetup extends Zend_Controller_Plugin_Abstract
{
    private $_setup = false;
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        if(!$this->_setup) {
            $viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
            $viewRenderer->init();
            $view = $viewRenderer->view;
            // for mgmt
            //$view->headLink()->appendStylesheet($view->baseUrl().'/common/css/hcs.css');
            
            $view->addHelperPath(APPLICATION_PATH.'/../library/Grid/View/Helper', "Grid_View_Helper_");
            $view->addScriptPath(APPLICATION_PATH.'/../library/Grid/View/Helper');
            $view->addHelperPath(APPLICATION_PATH.'/../library/Synrgic/Helper',"Synrgic_Helper");
            $view->addScriptPath(APPLICATION_PATH.'/../library/Synrgic/Helper');
            
            $this->_setup = true;
        }
    }
}
