<?
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Gambling_Plugin_Adverts extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
	if ('gambling' != $request->getModuleName()) {
	    return;
	}

	$layout = Zend_Layout::getMvcInstance();
	$layout->displayAdverts=false;
    }
}
