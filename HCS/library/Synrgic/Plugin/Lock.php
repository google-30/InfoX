<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * This plugin provides a check if the interface is locked and forces
 * the user to authenticate if it is
 */
class Synrgic_Plugin_Lock extends Zend_Controller_Plugin_Abstract
{
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$session = Zend_Registry::get(SYNRGIC_SESSION);

		if(isset($session->pin)){
			if( $request->getControllerName() != 'auth'){
				$request->setControllerName('auth');
				$request->setActionName('index');
			}
		}
	}
}
