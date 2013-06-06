<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * This script checks that various preconditions that are needed
 * for the application to run successfully are correct. If they
 * are not the class will print out what the problems are.
 */

class StartupCheck
{
    private $success = true;
    private $buffer;

    public static function performChecks()
    {
	$driver = new StartupCheck();
	$driver->run();
    }

    private function run()
    {
	ob_start();

	// We start by defining an output buffer. This won't be used
	// if all the checks succeed
	echo '<h1>HCS Precondition checker<h1>';
	echo '<h3>Checking Required Preconditions...<h3>';

	//
	// System checks
	//
	$this->displaySection("Performing System Checks");
	$dir = APPLICATION_PATH . '/data/cache';
	$this->displayState("Checking cache directory $dir is writeable",is_writable($dir));

	$dir = APPLICATION_PATH . '/models/proxies';
	$this->displayState("Checking DB Proxy directory $dir is writeable",is_writable($dir));

        //
	// Media Checks
        //

        // Media manager paths
	$this->displaySection("Performing Media checks");
	$dir = APPLICATION_PATH . '/data/uploads/';
	$this->displayState("Checking upload directory $dir is writeable",is_writable($dir));

    /*
	$dir = APPLICATION_PATH . '/data/uploads/media';
	$this->displayState("Checking media directory $dir is writeable",is_writable($dir));

	$dir = APPLICATION_PATH . '/data/uploads/adverts';
	$this->displayState("Checking media directory $dir is writeable",is_writable($dir));
    */

    // infox part
	$dir = APPLICATION_PATH . '/data/uploads/workers/images';
	$this->displayState("Checking media directory $dir is writeable",is_writable($dir));

	$dir = APPLICATION_PATH . '/data/uploads/archives/softwares';
	$this->displayState("Checking media directory $dir is writeable",is_writable($dir));

	$dir = APPLICATION_PATH . '/data/uploads/archives/documents';
	$this->displayState("Checking media directory $dir is writeable",is_writable($dir));


        // Module checks
	$this->displaySection("Performing 'Services' checks");
	$dir = APPLICATION_PATH . '/data/uploads/services-pic';
	$this->displayState("Checking Image uploade directory $dir is writeable",is_writable($dir));

        // Database checks
	//$this->displaySection("Checking Database");
        // TODO: Check Database connection
        // TODO:Check Schema

	if($this->success == false ){
	    echo "<hr>";
	    echo "Stopping Excution until preconditions are fixed";
	    ob_end_flush();
	    die();
	}
	
	ob_end_clean();
    }

    private function displaySection($string)
    {
	echo "<h3>$string</h3>";
    }

    private function displayState($string,$condition)
    {
	echo $string;
	echo ":";
	if( $condition )
	    echo '<font color="#00FF00">Yes</font>';
	else  {
	    echo '<font color="red">No</font>';
	    $this->success = false;
	}
	
	echo "<br>";
    }



}

?>
