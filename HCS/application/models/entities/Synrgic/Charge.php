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
 * The Charge class represents a particular charge that
 * has occurred to a guest
 * 
 * @Entity 
 * @Table(name="charges") 
 */
class Charge extends \Synrgic_Models_Entity
{
    /**
     * Unique ID required by the database
     *
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Date of the charge
     *
     * @Column(type="date")
     */
    protected $date;

    /**
     * Time of the Charge
     *
     * @Column(type="time",nullable=true)
     */
    protected $time;

    /**
     * A Description of the room
     * XXX This needs to become multilingual
     *
     * @Column(type="text") 
     */
    protected $description;

    /**
     * Amount of the charge
     */
    protected $amount;
}

?>
