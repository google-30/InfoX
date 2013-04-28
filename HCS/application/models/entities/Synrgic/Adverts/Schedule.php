<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/*-
 * Schedule entity
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 19/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Synrgic\Adverts\ScheduleRepository")
 */
class Schedule extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * @OneToMany(targetEntity="ScheduleEntry", cascade={"all"}, mappedBy="schedule")
     */
    protected $scheduleEntries;

    /**
     * While any changes of a schedule occured, this timestamp will be updated.
     * this is a key field served as communication between the backend and the 
     * frontend (we use unix timestamp)
     *
     * @Column(name="updated_dt", type="integer", nullable=false)
     */
    protected $updatedTime;

    /**
     * @Column(name="created_dt", type="datetime", nullable=false);
     */
    protected $createdTime;

    /**
     * To lock the schedule, when creating the schedule. When if the client
     * requesting a locked schedule, an empty schedule respond to it and the
     * client must delay a timeslice to request the schedule agin till a nonempt     
     *
     * @Column(type="boolean")
     */
    protected $locked = false;

    public function setCreatedTime($dt) {
        $this->_setDateTime('createdTime', $dt);
    }

    public function __construct()
    {
        $this->createdTime = new \DateTime('now');
        $this->updatedTime = time();
        $this->scheduleEntries = new ArrayCollection(); 
    }
}
