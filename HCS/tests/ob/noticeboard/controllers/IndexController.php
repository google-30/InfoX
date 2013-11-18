<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Noticeboard_IndexController extends Zend_Controller_Action
{
    const IDLE_TIME = 1; // In Seconds
    const MAX_TIME  = 120; // In Seconds

    private $_em = null;

    private $_alertRepo = null;

    private $_guest = null;

    private $_room = null;

    public function init()
    {
	$this->_em = Zend_Registry::get('em');
	$this->_alertRepo = $this->_em->getRepository('\Synrgic\Noticeboard\Alert');
	$session = Zend_Registry::get(SYNRGIC_SESSION);
	$this->_guest = $session->guest;
	$this->_room = $session->room;

	$this->_helper->ajaxContext->addActionContext('data','json')->initContext();

    }

    public function indexAction()
    {
	$this->_helper->layout->setLayout('testing');
    }

    public function dataAction()
    {
	$this->_helper->layout->disableLayout();
	$this->_helper->viewRenderer->setNoRender();


	// Alerts since last queried
	$inputTimeStamp = empty($_POST['lastPolled'])? 0 : $_POST['lastPolled'];
	$displayedAlerts = empty($_POST['activeAlerts'])? 0 : $_POST['activeAlerts'];
	$timestamp = DateTime::createFromFormat("U",$inputTimeStamp);
	$initial = 0;

	$pendingAlerts = $this->_alertRepo->findActiveAlertsByRoom($this->_room);

	Zend_Session::writeclose();

	while ( count($pendingAlerts) == $displayedAlerts && $initial < self::MAX_TIME){
	    sleep(self::IDLE_TIME);
	    $pendingAlerts = $this->_alertRepo->findActiveAlertsByRoom($this->_room);
	    $initial+=self::IDLE_TIME;
	}

	$displayedAlerts = count($pendingAlerts);

	$data = array();
	$alerts = array();

	foreach ($pendingAlerts as $alert ) {
	    if($alert->getIssued() >= $timestamp ){
		$adata = array();
		$adata['id'] =  $alert->getId();
		$adata['title'] = $alert->getTitle();
		$adata['message'] = $alert->getMessage();
		$adata['category'] = $alert->getCategory()->getName();
		$adata['issued'] = $alert->getIssued()->format('Y-m-d H:i:s');
		$adata['new'] = $alert->acknowledged == null ? true : false;
	    }
	    $alerts[] = $adata;
	}
	$data["alerts"]=$alerts;

	$data["weather"]= $this->view->action('weather','index','noticeboard');

	$json = Zend_Json::encode($data);
	echo $json;
    }

    public function miniAction()
    {
	/* Only view script output needed */
    }

    public function weatherAction()
    {
	// XXX TODO Create based on actual weather
	$this->view->weather=new
	    Synrgic_Models_Weather(15,Synrgic_Models_Weather::CLOUDY);
    }

    public function acknowledgeAction()
    {
	$this->_helper->layout->disableLayout();
	$this->_helper->viewRenderer->setNoRender();

	$request= $this->getRequest();
	$id = $this->getParam('id');
	if( $id ){
	    $alert = $this->_alertRepo->getAlertById($id);
	    $this->_alertRepo->acknowledgeAlert($alert,$this->_room);
	}
    }

    /**
     * Deleted a particular alert
     */
    public function deleteAction()
    {
	$this->_helper->layout->disableLayout();
	$this->_helper->viewRenderer->setNoRender();

	$request= $this->getRequest();
	$id = $this->getParam('id');
	if( $id ){
	    $alert = $this->_alertRepo->getAlertById($id);
	    $this->_alertRepo->deleteAlert($alert,$this->_room);
	}
    }

    /**
     * Removes all notifications pending to the user
     */
    public function deleteallAction()
    {
	if( $this->getRequest()->isXmlHttpRequest()){
	    $this->_helper->layout->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();

	    $this->_alertRepo->deleteActiveAlertsByRoom($this->_room);
	}
    }
}



?>


