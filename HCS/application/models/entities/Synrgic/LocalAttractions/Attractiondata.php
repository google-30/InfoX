<?php
 
namespace Synrgic\LocalAttractions;
/**
 * @Entity
 * @Table(name="attractions")
 */
class Attractiondata extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", length=64, nullable=false)
     */
    protected $title;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @Column(type="string", length=16)
     */
    protected $type;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $latitude;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $longitude;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $postcode;
    
    /**
     * The language of the a page in the group
     * 
     * @ManyToOne(targetEntity="Synrgic\Language")
     */
    protected $language;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $level;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $address;

    /**
     * @Column(name="start", type="datetime", nullable=true)
     */
    protected $start;

    /**
     * @Column(name="stop", type="datetime", nullable=true)
     */
    protected $stop;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $sponsor;    

   /**
    * Who own this adverts
    * @ManyToOne(targetEntity="Synrgic\Adverts\Advertiser")
    */
   protected $advertiser;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $distance;
    
}

