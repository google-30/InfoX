<?php
/*-
 * Copyright (c) 2012-2013 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/
class Synrgic_Plugin_AuthAcl extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
	/**
	 * Preconditions to Auth/ACL checks
	 */

	//
	// User can always access an error controller without
	// needing specific permissions
	//
	if( $request->getControllerName() == 'error' )
	    return;

	/**
	 * Step 1: Establish authentication.
	 */

	// Check to see if the user is already authenticated
	$auth = Zend_Auth::getInstance();
	if($auth->hasIdentity() == false){
	    // User is not authenticated. Assign them 'anonymous' permission level
	    $user = new \Synrgic\User();
	    $user->setName('Anonymous');
	    $user->setRole('anonymous');
	    $user->setPreferredLanguage(new \Synrgic\Language());
	    $auth->getStorage()->write($user);

	}


	// Check to see if we can raise the users permission level to guest
	if( 0 && $auth->getIdentity()->role == 'anonymous' ){
	    $session = Zend_Registry::get(SYNRGIC_SESSION);

	    // A Guest is present in the room when the OccupiedRoom table has a match for the room
	    if( isset($session->room) &&  $session->room instanceof \Synrgic\OccupiedRoom == false ){

		// we check to see if any guest has an occupied room matching this
		$room = $session->room;
		$em = Zend_Registry::get('em');
		$room = $em->getRepository('\Synrgic\OccupiedRoom')->findOneByPhysicalRoom($room->getId());
		if( $room != NULL ){
		    $session->room = $room; // Upgrade Room Object to Occupied Room
		    $auth->getStorage()->write($session->room->getGuest());
		    $session->guest = $session->room->getGuest(); //XXX/TODO: This is used by a lot of items, maintain here for now - do a sweeping remove later - benjsc 20130212
		}
	    }
	}

	/**
	 * Step 2. Check Access Permissions for user
	 *
	 * At this point a user will always exists hence we can soley rely on ACL's from now on

	 */

        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = strtolower($request->getActionName());

	$user = $auth->getIdentity();
	$acl = Zend_Registry::get('acl');

	if($action == 'index' || $action == '')
	    $action='view';

	$resource = Synrgic_Models_AclBuilder::mapResource($module,$controller,$action);

	if($acl->isAllowed($user->role,$resource,$action) == false){

	    // Permission was denied. The user is still allowed to perform
	    // the request provided they can supply suffient priveleges. We
	    // give them the opportunity to raise the privileges for the
	    // operation.

	    /**
             * The below is very useful for debugging ACL issues

	    var_dump($user->role . "- " . $resource . ":" . $action . "...ACL Denied\n");
	    flush();
	     */

	    if( $request->getControllerName() != 'auth') {
		$request->setModuleName('management');
		$request->setControllerName('auth');
		$request->setActionName('index');
		return;
	    }

	    throw new Exception(sprintf('You have no permission to this url :%s/%s/%s', $module, $controller, $action==='view'?'':$action));

	}
    }
}
