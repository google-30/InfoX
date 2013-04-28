<?php
 
namespace Synrgic\BillPreview;
/**
 * @Entity
 * @Table(name="guestdata")
 */
class Guestdata extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     @ @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", length=128)
     */
    protected $guestname;

    /**
     * @OneToOne(targetEntity="Synrgic\Guest")
     */
    protected $guest;

    /**
     * @Column(type="string", length=16, unique=true)
     */
    protected $room;

    /**
     * @Column(type="string", length=32, nullable=true)
     */
    protected $roomtype;
 
    /**
     * @Column(type="date")
     */
    protected $arrival;

    /**
     * @Column(type="date")
     */
    protected $departure;
  
}
    
