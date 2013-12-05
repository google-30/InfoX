<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_matpodata")
 */
class Matpodata extends \Synrgic_Models_Entity {
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
     * po no.
     *   
     * @Column(type="string",nullable=true)
     */
    protected $pono;

    /**
     * contact
     *   
     * @Column(type="string",nullable=true)
     */
    protected $contact;

    /**
     * phone
     *   
     * @Column(type="string",nullable=true)
     */
    protected $phone;

    /**
     * po is for supplier
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Supplier")
     */
    protected $supplier;

    /**
     * po state
     *   
     * @Column(type="integer",nullable=true)
     */
    protected $state;
    
    
}
