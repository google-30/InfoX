<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_supplyprice")
 */
class Supplyprice extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @ManyToOne(targetEntity="Synrgic\Infox\Material")
     */
    protected $material;

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Supplier")
     */
    protected $supplier;

    /**
     * DO Date   
     * @Column(type="date", nullable=true)
     */
    protected $update;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $unit;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $rate;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $quantity;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $price;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;
    
}
