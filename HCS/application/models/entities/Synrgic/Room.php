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

/** 
 * The room class represents a physical hotel room
 * 
 * @Entity @Table(name="rooms") 
 */
class Room extends \Synrgic_Models_Entity
{
    /**
     * Unique ID required by the database
     *
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /** 
     * The name of the room
     *
     * @Column(type="string", length=50) 
     */
    protected $name;

    /**
     * A Description of the room
     *
     * @Column(type="text") 
     */
    protected $description;

	/**
     * A Type of the room
     *
     * @Column(type="text", nullable=true) 
     */
    protected $type;
}
