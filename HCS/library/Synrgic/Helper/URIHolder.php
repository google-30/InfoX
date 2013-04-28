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
 * The URI Holder helper provides a quick method to both save
 * and retrieve URI's throughout the system. This can be used
 * to save URI's when redirecting to authentication pages for example.
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Synrgic_Helper_URIHolder extends Zend_Controller_Action_Helper_Abstract
{
    private $_session;

    /**
     * Construct an instance of the helper using the given Zend_Session_Namespace
     * object to store the information in.
     *
     * @param Zend_Session_Namespace session The Session based namespace to use for storage
     */
    public function __construct($session)
    {
	assert(isset($session));
	$this->_session = $session;
    }

    /**
     * Indicate if there is a saved URI 
     * 
     * @return True if there is a saved URI
     */
    public function hasSaved()
    {
	return isset($this->_session->URIHolder_uri);
    }

    /**
     * Saves the Request URI for later access
     *
     * @return Synrgic_Helper_URIHolder
     */
    public function save()
    {
	$request= Zend_Controller_Front::getInstance()->getRequest();
	if(isset($request)){
	    $this->_session->URIHolder_uri=$request->getRequestUri();
	}

	return $this;
    }

    /**
     * Redirects to the saved URI, if there is no saved URI the default
     * is used instead.
     *
     * @param default The default URI to redirect to if there is none saved
     */
    public function redirect($default='/')
    {
	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('Redirector');
	$where = $default;
	if($this->hasSaved())
	    $where = $this->_session->URIHolder_uri;
	$redirector->gotoURL($where);
    }

    /**
     * Obtain the saved URI, with a default if none exists
     *
     * @param string    default The Default URI to use if none were saved
     * @return string The saved URI
     */
    public function getURI($default='/')
    {
	if($this->hasSaved())
	    return $this->_session->URIHolder_uri;
	return $default;
    }

    /**
     * Handle the Helper API
     */
    public function direct()
    {
	$this->redirect();
    }

}

?>

