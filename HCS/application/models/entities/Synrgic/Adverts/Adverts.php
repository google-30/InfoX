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
 * Adverts entity
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 10/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\Common\Collections\ArrayCollection;
use Synrgic\LocalAttractions\Attractiondata;
/**
 * @Entity(repositoryClass="Synrgic\Adverts\AdvertsRepository")
 */
class Adverts extends \Synrgic_Models_Entity {
   
   /**
    * @Id
    * @GeneratedValue(strategy="AUTO")
    * @Column(type="integer")
    */
   protected $id;

   /**
    * Who own this adverts
    * @ManyToOne(targetEntity="Advertiser", cascade={"all"}, fetch="EAGER", inversedBy="adverts")
    * @JoinColumn(name="advertiser_id", referencedColumnName="id")
    */
   protected $advertiser;

   /**
    * for what?
    * @Column(type="boolean")
    */
   protected $permanent;

   /**
    * popup/popdown/cyclic/randomly
    * @Column(name="play_mode", type="string", length=64) 
    */
   protected $playMode;

   /**
    * if this adverts is not enabled, this adverts willl never be played on frone-end. 
    * @Column(type="boolean", nullable=true) 
    */
   protected $disabled;

   /**
    * Number of blocks in the Advert Board must be not less than 1
    * @Column(type="integer", nullable=false)
    */
   protected $size;

   /**
    * Number of time slices
    * @Column(type="integer", nullable=false)
    */
   protected $duration;

   /**
    * @Column(name="click_url", type="text")
    */
   protected $clickUrl;

   /**
    * @ManyToOne(targetEntity="\Synrgic\Media", cascade = {"persist"}, fetch="EAGER")
    * @JoinColumn(name="media_id", referencedColumnName="id")
    */
   protected $media;

   /**
    * @ManyToOne(targetEntity="\Synrgic\ChargeModel", cascade = {"persist"}, fetch="EAGER")
    * @JoinColumn(name="charge_model_id", referencedColumnName="id")
    */
   protected $chargeModel;
   
   /**
    * @Column(name="start_date", type="date", nullable=false)
    */
   protected $startDate;

   /**
    * @Column(name="end_date", type="date", nullable=false)
    */
   protected $endDate;

   /**
    * @Column(name="start_time", type="time")
    */
   protected $startTime;

   /**
    * @Column(name="end_time", type="time")
    */
   protected $endTime;

   /**
    * The keywords associated to this adverts so that we can push adverts
    * base on the keywords contained in the specific page later (actually
    * each page should associate some meta keywords. Otherwise adverts
    * will be too crawded......
    *
    * @Column(type="string")
    */
   protected $keywords;

   /**
    * LA references
    * 
    * @ManyToMany(targetEntity="Synrgic\LocalAttractions\Attractiondata")
    * @JoinTable(name="adverts_attractions",
    *     joinColumns={@JoinColumn(name="adverts_id", referencedColumnName="id")},
    *     inverseJoinColumns={@JoinColumn(name="attractions_id", referencedColumnName="id")}
    *     )
    */
   protected $attractions;
   
   /**
    * Add this field as delete marker. Physical deletion is delegated to housekeeping.
    * @Column(name="deleted_dt", type="datetime", nullable=true) 
    */
   protected $deletedTime;
   
   /**
    * Add this field, make sure that ads housekeeping does correct things
    * @Column(name="updated_dt", type="datetime", nullable=false) 
    */
   protected $updatedTime;
   
   /**
    * @Column(name="created_dt", type="datetime", nullable=false) 
    */
   protected $createdTime;

   // may need a general way to convert datetime from post data
   public function setStartDate($val) {
       $this->_setDateTime('startDate', $val);
   }

   public function setStartTime($val) {
       $this->_setDateTime('startTime', $val);
   }

   public function setEndDate($val) {
       $this->_setDateTime('endDate', $val);
   }

   public function setEndTime($val) {
       $this->_setDateTime('endTime', $val);
   }

   public function setCreatedTime($val) {
       $this->_setDateTime('createdTime', $val);
   }

   public function __construct() 
   {
       $this->attractions = new ArrayCollection();
       
       // set default
       $this->createdTime = new \DateTime();
       $this->updatedTime = new \DateTime();
       $this->startDate = new \DateTime();
       $this->endDate = new \DateTime();
       $this->startTime = new \DateTime();
       $this->endTime = new \DateTime();
       $this->size = 1;
       $this->duration = 1;
       $this->permanent = false;
       $this->enabled = true;  // reserve for admin
   }
}

