<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_matappdata")
 */
class Matappdata extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * one application has multiple material data 
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Application")
     */
    protected $application;

    /**
     * material id: may be in material table or self define
     *   
     * @Column(type="integer",nullable=true)
     */
    protected $materialid;
    
    /**
     * in material table or not
     *   
     * @Column(type="boolean", nullable=true)
     */
    protected $materialinsys;    

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $amount;
    
    /**
     *   
     * @Column(type="string", nullable=true)
     */
    protected $remark;

    /**
     *   
     * @Column(type="string", nullable=true)
     */
    protected $longname;
            
    /**
     * site part: it's defined in site entity
     *  
     * @Column(type="string", nullable=true)
     */
    protected $sitepart;  

    /**
     * one application has multiple material data 
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Supplier")
     */
    protected $supplier;
    
    /**
     * @Column(type="float",nullable=true)
     */
    protected $rate;
    
    /**
     * unit 
     * 1. the material is manually input; 
     * 2. material entity has no unit; 
     *  
     * @Column(type="string", nullable=true)
     */
    protected $unit;      

    /**
     * @Column(type="float",nullable=true)
     */
    protected $quantity;

    /**
     * @Column(type="float",nullable=true)
     */
    protected $total;    
}
