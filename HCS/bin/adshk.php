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
 * This script makes housekeeping for adverts
 */

defined('APPLICATION_ENV') || define('APPLICATION_ENV', 'development');
defined('APPLICATION_PATH') || define('APPLICATION_PATH', realpath(dirname(__FILE__) .  '/../application'));

set_include_path(implode(PATH_SEPARATOR, array(
  realpath(APPLICATION_PATH.'/../library'),
  get_include_path(),
)));

// Zend
require_once 'Zend/Application.php';

$application = new Zend_Application(
  APPLICATION_ENV,
  APPLICATION_PATH.'/configs/application.ini'
);

// bootstrap doctrine
$application->getBootstrap()->bootstrap('doctrine');

echo "Housekeeping ......";
$n = Synrgic_Models_Adverts_Util::doHousekeeping($ads, 120);
echo "done\n";
echo "Ads deleted: $ads, medias deleted: $n\n\n";
 