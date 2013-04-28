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
 * The Weather class provides assistance to web pages that need to
 * display images and icons related to temperature and the weather
 */
class Synrgic_Models_Weather
{
    const SUNNY = 1;
    const CLOUDY = 2;

    const ICONPATH='/common/images/weather';

    private $_temperature;
    private $_condition;

    public function __construct($temperature,$condition)
    {
	$this->_temperature = $temperature;
	$this->_condition = $condition;
    }

    public function getTemperature()
    {
	return $this->_temperature;
    }


    public function getName()
    {
	switch($this->_condition){
	    case self::SUNNY: return "Sunny"; 
	    case self::CLOUDY: return "Cloudy"; 
	    default: return "Unknown";
	}
    }


    public function getIcon()
    {
	$path = self::ICONPATH . '/';

	switch($this->_condition){
	    case self::SUNNY: $path.='sun.png'; break;
	    case self::CLOUDY: $path.='cloudy.png'; break;
	}

	return $path;
    }
}

?>
