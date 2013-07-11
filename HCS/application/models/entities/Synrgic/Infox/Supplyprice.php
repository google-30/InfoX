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
     * @Column(type="datetime", nullable=true)
     */
    protected $update;

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Material")
     */
    protected $material;

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Supplier")
     */
    protected $supplier;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $price;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;
    
}
