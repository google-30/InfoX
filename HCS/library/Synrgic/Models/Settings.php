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
 * A class which obtains configuration settings from 
 * the database in a lazy fashion with caching.
 * 
 * Settings consist of a name and belong to a section.
 * To obtain a setting by name you can use: 
 * settings->name
 * settings->section->name
 * settings->get('name')
 * settings->get('section.name')
 *
 * The class also implements the iterator interface
 * where all settings can be obtained for all sections.
 * Additionally settings for a particular section can be iterated
 * over using:
 *
 * settings->restrictToSection(x);
 * foreach(settings as setting)....
 */
class Synrgic_Models_Settings Implements Iterator, Countable
{

    // Doctrine Entity manager used to query the database
    private $_em;

    // Name of section this object is restricted to
    private $_sectionName;

    // Index for the iterator
    private $_index;

    // All settings, possibly restricted to a section
    // This variable is used when iterating
    private $_data;

    /**
     * Construct a Settings object which can be used by anyone
     *
     * @param $sectionName The name of the section to query by default
     * @param settingsObject an existing object to use to create the object
     */
    public function __construct($sectionName = null)
    {
	$this->_em = Zend_Registry::get('em');
	$this->_sectionName = $sectionName;
	$this->_index=0;
	$this->_data = null;
    }

    public function __get($name)
    {
	return $this->get($name);
    }

    /*
       public function __set($name,  $value)
       {
       }

       public function __unset($name)
       {

       }
     */

    /**
     * Restrict obtaining the setting to the section named. If the section does
     * not exists, not keys will be available. Any iterators already in use will
     * be rewound to the start of the section list. To remove restrictions use
     * unRestrict()
     *
     * @param sectionName The name of the section to restrict iteration to
     * @return Settings
     */
    public function getSection($sectionName)
    {
	return new Synrgic_Models_Settings($sectionName);
    }


    /**
     * Obtain the particular setting. By default we
     * parse sections first. Hence settings->section->name
     * will work.
     */
    public function get($name,  $default = NULL )
    {
	$func = 'get' . ucfirst($name);

	/** 
	 * If we have a section name, we query the section for the name
	 */
	if( $this->_sectionName != null){
	    $setting = $this->_em->getRepository('\Synrgic\Setting')->findOneBy(
		array('name'=>$name,'section'=>$this->_sectionName ));
	    if( $setting != null ){
		return new _Synrgic_Setting_Wrapper($setting);
	    } else {
		if( $default !== NULL ){
		    return $default;
		}
		throw new exception('Setting not found:'.  $name . ' in section ' . $this->_sectionName);
	    }
	} else {
	    /** 
	     * Because we are not restricted to a section, the name could be either
	     * a section name or a function name. We test
	     * for a section name first, falling back to a function name
	     */
	    if( $this->_em->getRepository('\Synrgic\Setting')->findOneBySection(
		    array('section'=>$name)))
		return new Synrgic_Models_Settings($name);
	    else {
		// No section by that name, see if a key exists without 
		// a section name
		$setting =
		    $this->_em->getRepository('\Synrgic\Setting')->findOneBy(
			array('name'=>$name,'section'=>''));
		if($setting != null ){
		    return new _Synrgic_Setting_Wrapper($setting);
		} else {
		    // No setting of that name found. Return default if given
		    if( $default !== NULL ){
			return $default;
		    }
		    throw new exception('Setting/section not found:'.  $name);
		}
	    }
	}
    }

    /**
     * Obtain all settings. This may be restricted to a section
     * if _sectionName is valid. After this method executes _data
     * will contain a valid array
     */
    private function getSettings()
    {
	if ($this->_data != null )
	    return;

	if( $this->_sectionName != null )
	    $this->_data = $this->_em->getRepository('\Synrgic\Setting')->findBySection(
									$this->_sectionName);
	else
	    $this->_data = $this->_em->getRepository('\Synrgic\Setting')->findAll();
    }

    /*--------------------*/
    /* Iterator interface */
    /*--------------------*/

    public function current()
    {
	$this->getSettings();
	return new _Synrgic_Setting_Wrapper(current($this->_data));
    }

    public function key()
    {
	$this->getSettings();
	return current($this->_data)->getName();
    }

    public function next()
    {
	$this->getSettings();
	next($this->_data);
	$this->_index++;
    }

    public function rewind()
    {
	$this->getSettings();
	reset($this->_data);
	$this->_index=0;
    }

    public function valid()
    {
	$this->getSettings();
	return ($this->_index < count($this->_data));
    }

    /*---------------------*/
    /* Countable Interface */
    /*---------------------*/
    public function count()
    {
	$this->getSettings();
	return count($this->_data);
    }




};

/**
 * The Synrgic Settings wrapper makes it possible to use -> calls 
 * on the get values of data. Ie:  setting->value
 * calls setting->getValue()
 *
 * This is a convienence class not intended for use outside of settings
 */
class _Synrgic_Setting_Wrapper
{
    private $_setting;

    public function __construct($setting)
    {
	$this->_setting = $setting;
    }

    public function __get($name)
    {
	$func = 'get'.$name;
	return $this->_setting->$func();
    }
}


?>
