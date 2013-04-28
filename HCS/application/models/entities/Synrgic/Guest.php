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
 * The Guest class represents a single 
 * guest in the hotel. A guest is defined
 * as the person who has booked one or more rooms.
 * 
 * @Entity(repositoryClass="Synrgic\GuestRepository")
 * @Table(name="guests")
 */
class Guest extends \Synrgic_Models_Entity {
    /**
     * Unique identifier for a page
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * Name of the language
     * 
     * @Column(type="string")
     */
    protected $name;

    /**
     * The preferred language of the guest
     * 
     * @ManyToOne(targetEntity="Language", fetch="EAGER")
     */
    protected $preferredLanguage;

    /**
     * The pin number given for access to higher level systems
     * (ie gambling, internet)
     *
     * @Column(type="string", nullable=true)
     */
    protected $pin;

    /**
     * Rooms allocated to the guest
     *
     * @OneToMany(targetEntity="OccupiedRoom", mappedBy="guest", cascade={"all"})
     */
    protected $rooms;

    /**
     * Nominated contact room for the guest
     *
     * @OneToOne(targetEntity="OccupiedRoom")
     */ 
    protected $alertRoom;

    /**
     * Charges to the guest, not related to a room.
     * Ie Dinner at a restaurant.
     *
     * @ManyToMany(targetEntity="Charge", cascade={"all"})
     */
    protected $charges;

    /**
     * Orders related to the guest
     *
     * XXX/TODO xiaofei Add orders/mappings to orders here
     */
    protected $orders;

    /**
     * The role for the guest. If this is changed ACL's must also be updated
     */
    public $role = 'guest';

    public function __construct()
    {
	$this->orders = new ArrayCollection();
	$this->charges = new ArrayCollection();
	$this->rooms = new ArrayCollection();
    }
}

?>
