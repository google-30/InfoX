<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\Noticeboard;

/**
 * The Alert class represents a single alert that needs to be displayed
 * to the occupents of one or more rooms. An alerts is sent either to a guest
 * or to a room but not both. The reason for the distinction is that a guest
 * may book more than 1 room in the hotel. Hence the alert is delivered to all
 * rooms that the guest has booked but only needs to be acknowledged once.
 *
 * Added to this an alert may be room specific for example a pizza delivery
 * notification.
 * 
 * @Entity(repositoryClass="Synrgic\Noticeboard\AlertRepository")
 * @Table(name="Alerts")
 */
class Alert extends \Synrgic_Models_Entity {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * The Guest this alert is intended for
     *
     * @ManyToOne(targetEntity="\Synrgic\Guest")
     */
    protected $guest;

    /**
     * The Guest this alert is intended for
     *
     * @ManyToOne(targetEntity="\Synrgic\OccupiedRoom")
     */
    protected $room;

    /**
     * The time of the alert
     *
     * @Column(type="datetime");
     */
    protected $issued;

    /**
     * The category of the alert
     * 
     * @ManyToOne(targetEntity="AlertCategory")
     */
    protected $category;

    /**
     * Title of the alert
     *
     * @Column(type="string")
     */
    protected $title;

    /**
     * Message to display
     *
     * @Column(type="text")
     */
    protected $message;

    /**
     * Set when the Alert is Acknowledged
     *
     * @Column(type="datetime",nullable=true);
     */
    protected $acknowledged;

    /**
     * Set when the Alert is Deleted by the user
     *
     * @Column(type="datetime",nullable=true);
     */
    protected $purged;

}

?>
