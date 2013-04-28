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
 * The OccupiedRoom Class represents a room when it is in use
 * by a guest. The room  has additional information not required
 * of a standard room. The physical room should not be used for
 * any mappings that need a guest association. Instead use an 
 * instance of this class as this will always have the latest 
 * room information for the period of the stay of the guest.
 *
 * @Entity
 * @Table(name="OccupiedRoom")
 */
class OccupiedRoom extends \Synrgic_Models_Entity {
    /** 
     * Unique ID of the Occupied Room
     *
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * The guest occupying this room
     *
     * @ManyToOne(targetEntity="Guest",inversedBy="rooms", fetch="EAGER")
     */
    protected $guest;

    /**
     * Physical Room associated with this
     * virtual occupied room
     *
     * @OneToOne(targetEntity="Room", fetch="EAGER")
     */
    protected $physicalRoom;

    /**
     * Charges related to the room
     *
     * @ManyToMany(targetEntity="Charge",cascade={"all"})
     */
    protected $charges;

    public function __construct()
    {
	$this->charges = new ArrayCollection();
    }

    /**
     * Obtain the name of the room. We wrap 
     * the internal class easing the user of the caller
     */ 
    public function getName()
    {
	return $this->physicalRoom->getName();
    }
}

?>
