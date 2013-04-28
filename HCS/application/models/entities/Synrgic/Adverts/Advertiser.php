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
 * Advertiser entity
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 10/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\Common\Collections\ArrayCollection;
use \Synrgic\Media;

/**
 * @Entity(repositoryClass="Synrgic\Adverts\AdvertiserRepository")
 */
class Advertiser extends \Synrgic_Models_Entity {
   
   /**
    * @Id
    * @GeneratedValue(strategy="AUTO")
    * @Column(type="integer")
    */
   protected $id;

   /**
    * The name of the advertiser
    * @Column(type="string", length=255, unique=true, nullable=false)
    */
   protected $name;

   /**
    * @Column(type="text")
    */
   protected $contact;

   /**
    * Adverts belong to this advertiser
    * @OneToMany(targetEntity="Adverts", cascade={"all"}, mappedBy="advertiser")
    */
   protected $adverts;

   /**
    * Medias belong to this advertiser
    *
    * @ManyToMany(targetEntity="Synrgic\Media")
    * @JoinTable(name="advertiser_medias",
    *     joinColumns={@JoinColumn(name="advertiser_id", referencedColumnName="id")},
    *     inverseJoinColumns={@JoinColumn(name="media_id", referencedColumnName="id")}
    *     )
    */
   protected $medias;

   /**
    * Advertiser register time, just for management later
    * @Column(name="created_dt", type="datetime", nullable=false) 
    */
   protected $createdTime; 

   public function __construct()
   {
       $this->createdTime = new \DateTime('now');
       $this->adverts = new ArrayCollection();
       $this->medias = new ArrayCollection(); 
   }
}


