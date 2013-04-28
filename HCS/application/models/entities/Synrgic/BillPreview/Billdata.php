<?php
 
namespace Synrgic\BillPreview;
/**
 * @Entity
 * @Table(name="billdata")
 */
class Billdata extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     @ @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(name="date", type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @Column(type="text")
     */
    protected $description;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $amount;

    /**
     * @Column(type="string", length=16)
     */
    protected $room;

    /**
     * @ManyToOne(targetEntity="Synrgic\Room", fetch="EAGER")
     */
    protected $physicalRoom;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $quantity;

    /**
     * @Column(type="string", length=64, nullable=true)
     */
    protected $name;    

    /**
     * @Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $price;    
}

