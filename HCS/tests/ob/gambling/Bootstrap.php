<?
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Gambling_Bootstrap extends Zend_Application_Module_Bootstrap
{
    /**
     * By default we disable the adverts on any gambling related 
     * screens. We still use the default layout so we don't create 
     * a separate layout in the module, merely turn off the adverts
     * in the default layout
     */
    protected function _initLayoutHelper()
    {
	$bootstrap = $this->getApplication();
	$bootstrap->bootstrap('frontcontroller');
	$front = $bootstrap->getResource('frontcontroller');
	$front->registerPlugin(new Gambling_Plugin_Adverts());
    }
}

?>
