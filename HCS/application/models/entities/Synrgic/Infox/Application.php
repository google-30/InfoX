<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_application")
 */
class Application extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $nameeng;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $onlinedate;
    	
    /**
     * @Column(type="float", nullable=true)
     */
    protected $price;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $description;
  
    /**
     * @Column(type="string", nullable=true)
     */
    protected $warehouse;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $macrotype;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $detailtype;    

    /**
     * supplier
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Supplier")
     */
    protected $supplier;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $spec;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $pic;
    
}
