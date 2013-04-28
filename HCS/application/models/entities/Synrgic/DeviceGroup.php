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

/** 
 * A DeviceGroup associates one or more devices 
 * together. This can aid in defining rules
 * for the system.
 * 
 * @Entity @Table(name="DeviceGroups") 
 */
class DeviceGroup extends \Synrgic_Models_Entity
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
    protected $name;

    /**
     * @Column(type="text") 
     *
     * XXX Should convert to Synrgic\String so language independant
     */
    protected $description;

    /**
     * A list of all the devices in the group
     *
     * @ManyToMany(targetEntity="Device", inversedBy="groups", cascade={"all"})
     */
    protected $devices;

    public function __construct()
    {
	$this->devices = new ArrayCollection();
    }

}
