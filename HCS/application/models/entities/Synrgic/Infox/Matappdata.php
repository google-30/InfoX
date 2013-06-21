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
     * material 
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Material")
     */
    protected $material;

    /**
     * @Column(type="integer")
     */
    protected $amount;
    
}
