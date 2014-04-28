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
     * @Column(type="string", nullable=true)
     */
    protected $unit;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $rate;


}
