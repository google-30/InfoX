#!/usr/bin/env php
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
 * This script uses doctrine to load the database with initial data
 */

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) .  '/../application'));
defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');

set_include_path(
	implode(PATH_SEPARATOR, 
			array( APPLICATION_PATH .  '/../library', 
				   get_include_path(), 
				   APPLICATION_PATH . "/models/entities")
			)
	);

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
 
// Initialize Zend_Application
$application = new Zend_Application( 'development', APPLICATION_PATH . '/configs/application.ini');
 
// Initialize Doctrine
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('doctrine');

$em = Zend_Registry::get('em');

if( $argc > 1 ){
	include($argv[1]);
} else {
/*
	include('data/language.php');
	include('data/guestdata.php');
	include('data/billdata.php');
	include('data/mediatype.php');
	include('data/settings.php');
	include('data/attractiondata.php');
	include('data/room.php');
	include('data/provider.php');
	include('data/accounts.php');
	include('data/devicegroups.php');
	include('data/guest.php');
	include('data/catalog.php');
	include('data/services.php');
	include('data/pagegroup.php');
	include('data/page.php');
	include('data/infodata.php');
	include('data/alert.php');
        include('data/chargemodel.php');
*/
	include('data/infox_site.php');
	include('data/infox_workercompanyinfo.php');
	include('data/infox_workerskill.php');
	include('data/infox_workerfamily.php');
	include('data/infox_worker.php');
	include('data/infox_material.php');
	include('data/infox_archive.php');

}
