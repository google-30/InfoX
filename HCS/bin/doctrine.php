<?php

define('APPLICATION_ENV', 'development');
define('APPLICATION_PATH', realpath(dirname(__FILE__).'/../application'));
define('IGNORE_INI_SESSION', 'yes');  // init session need a predefined table
                                     // but when creating db, no tables there

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
$application->getBootstrap()->bootstrap();
$em = $application->getBootstrap()->getResource('doctrine');

$classLoader = new \Doctrine\Common\ClassLoader('Symfony', DOCTRINE_LIB_PATH . '/vendor');
$classLoader->register();


// generate doctrine HelpSet
$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
  'db'=>new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
  'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));

\Doctrine\ORM\Tools\Console\ConsoleRunner::run($helperSet);


