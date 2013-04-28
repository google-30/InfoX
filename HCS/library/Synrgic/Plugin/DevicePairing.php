<?
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * This plugin confirms that a device is paired to the system before it can be
 * used. This applies only to the guest interface
 */
class Synrgic_Plugin_DevicePairing extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
	// First we check the session to see if we have a unique identifier
	// to communicate with the system. If not we lookup a unique identifier
	// based on the device id. If that's not found we force association

	$session = Zend_Registry::get(SYNRGIC_SESSION);
	if(!isset($session->token)){

	    // Check if the device sent us our persistent cookie.
	    // If it did then the cookie has the uniquid in it, we validate
	    // the id is still valid.
	    if( isset($_COOKIE[DEVICE_PERSIST_COOKIE])){
		$id = $_COOKIE[DEVICE_PERSIST_COOKIE]; 

		$em = Zend_Registry::get('em');
	    	$device = $em->getRepository('\Synrgic\Device')->findOneByUniqueid($id);
		if( $device != null ){
	    	
		    // Update the stored session in the database so we can expire it
		    // if required.
		    $device->sessionID=session_id();
		    $em->persist($device);
		    $em->flush();

		    // Store the Room information the session
		    $session->room = $device->getRoom();
		    $session->token = $id;

		    return;
		}
	    }

	    // The device not known. Check to see if the user is a staff user
	    // if not we force the device to be paired
	    $auth = Zend_Auth::getInstance();
	    $acl  = Zend_Registry::get('acl');

	    if($auth->hasIdentity()==false || 
	       	$acl->isAllowed($auth->getIdentity()->role,'management:dashboard','view') == false){

		if( $request->getModuleName() != 'management' ){ 
		    $this->getResponse()->setRedirect("/management/devices/pairrequest");
		}
	    }
	}
    }
}
