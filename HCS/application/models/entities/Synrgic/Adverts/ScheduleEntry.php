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
 * ScheduleEntry entity
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 19/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="schedule_entry")
 */
class ScheduleEntry extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * The values of the following fields copy from adverts into the 
     * schedule entry at creating time since an adverts may be changed 
     * later. 
     */

    /**
     * @Column(name="start_time", type="time")
     */
    protected $startTime;

    /**
     * @Column(name="end_time", type="time")
     */
    protected $endTime;

    /**
     * @Column(name="size", type="integer")
     */
    protected $size;

    /**
     * @Column(name="duration", type="integer")
     */
    protected $duration;

    /**
     * @Column(type="string", length=64)
     */
    protected $playMode;

    /**
     * @Column(type="boolean")
     */
    protected $permanent;
    
    /**
     * @Column(name="click_url", type="text", nullable=true)
     */
    protected $clickUrl;
    
    /**
     * Should reference to adverts but an adverts may be housekept
     * but schedule entries may be the input for ads charging and
     * need to be kept according to financial period.
     * 
     * NOTE: a design consideration: we copy media id here not
     * reference to media so that the obsolete medias 
     * can be removed by housekeeping.
     * 
     * @Column(name="media_id", type="integer", nullable=false)
     */
    protected $mediaId = 0;
    
    /**
     * Same as above.
     * @Column(name="advertiser_id", type="integer", nullable=false)
     */
    protected $advertiserId = 0;

    //end

    /**
     * When the adverts changed and removed, this schedule entry should be 
     * stopped and not used, then be replaced by a new one.
     *
     * @Column(name="stopped_dt", type="datetime", nullable=true)
     */
    protected $stoppedTime;
    
    /**
     * When the adverts is housekept, this schedule entry should be
     * stopped and not used
     *
     * @Column(name="housekept_dt", type="datetime", nullable=true)
     */
    protected $housekeptTime;
    
    /**
     * Use unix timestamp, just for display order
     * @Column(name="created_dt", type="integer", nullable=true)
     */
    protected $createdTime;
       
    /**
     * @ManyToOne(targetEntity="Schedule", inversedBy="scheduleEntries")
     * @JoinColumn(name="schedule_id", referencedColumnName="id")
     */
    protected $schedule;

    public function setStartTime($dt) {
        $this->_setDateTime('startTime', $dt);
    }
    
    public function setStoppedTime($dt) {
        $this->_setDateTime('stoppedTime', $dt);
    }
    
    public function setHousekeptTime($dt) {
        $this->_setDateTime('housekeptTime', $dt);
    }
    
    public function setEndTime($dt) {
        $this->_setDateTime('endTime', $dt);
    }
    
}
