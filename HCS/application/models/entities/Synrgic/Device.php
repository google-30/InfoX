<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic;

use Doctrine\Common\Collections\ArrayCollection;

// Pull in the mobile detect class
require(APPLICATION_PATH . "/../../external/Mobile-Detect/Mobile_Detect.php");

/** 
 * The Device class represents a physical 
 * device which can display information to the user
 * and optionally accept input from the user
 * 
 * @Entity(repositoryClass="Synrgic\DeviceRepository") 
 * @Table(name="Devices") 
 */
class Device extends \Synrgic_Models_Entity
{
    /**
     * Unique ID required by the database
     *
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The uniqueid field maps a uniq identifier provided by
     * the device back to the system.
     *
     * @Column(type="text") 
     */
    protected $uniqueid;
    
    /**
     * A User friendly name give to the device
     *
     * @Column(type="text")
     */
    protected $name;

    /**
     * @Column(type="text") 
     */
    protected $description;

    /**
     * Indicates what type the device is
     * 
     * @Column(type="integer")
     */
    protected $deviceType;
    const UNKNOWN = 0;
    const TABLET  = 1;
    const PHONE   = 2;
    const PC      = 3;
    const TABLE   = 4;


    /**
     * A list of all groups the device belongs to
     *
     * @ManyToMany(targetEntity="DeviceGroup",fetch="EAGER",mappedBy="devices")
     */
    protected $groups;

    /**
     * Physical Room the device belongs too
     *
     * @ManyToOne(targetEntity="Room",fetch="EAGER")
     */
    protected $room;
    
    /**
     * SessionID The session ID currently in use
     * by the device
     *
     * @Column(type="text");
     */
    protected $sessionID;

    /**
     * device state
     * ONLINE|NORMAL, ONLINE|RESET
     *
     * @Column(type="text", nullable=true)
     */
    protected $state;
    const NORMAL = 0;
    const RESET = 1;
    const ONLINE = "ONLINE";
    const OFFLINE = "OFFLINE";
    const ERROR = "ERROR";


    public function __construct()
    {
	$this->groups = new ArrayCollection();
    }

    public function getDeviceTypeAsString()
    {
	return $this->convertDeviceTypeToString($this->deviceType);
    }

    /**
     * Provides a textual string indicating the type of 
     * device that the user is using. 
     *
     * @return A textual string
     */
    public static function probeDeviceTypeAsString()
    {
	$value = self::probeDeviceType();
	return self::convertDeviceTypeToString($value);
    }
    /**
     * Obtains the device type as an integer based on the constants
     * defined in this class. 
     *
     * XXX/TODO Add support for Table/PC Type detection
     *
     * @return An integer corresponding to a constant in this class
     */
    public static function probeDeviceType()
    {
    	$detect = new \Mobile_Detect();
        $deviceType = ($detect->isMobile() ? ($detect->isTablet() ? self::TABLET : self::PHONE) : self::TABLE);
        return $deviceType;
    }

    public static function getDeviceTypeList()
    {
	return array( self::TABLET => "Tablet",
		      self::PHONE => "Phone",
		      self::TABLE => "Table",
		      self::PC => "Computer");
    }

    public static function convertDeviceTypeToString($deviceType)
    {
	switch($deviceType){
	    case self::TABLET: return "Tablet";
	    case self::PHONE: return "Phone";
	    case self::TABLE: return "Table";
	    case self::PC: return "Computer";
	    default: return "Unknown";
	}
    }
}

?>
