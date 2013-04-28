<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_material")
 */
class Material extends \Synrgic_Models_Entity {
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
     * @Column(type="datetime", nullable=true)
     */
    protected $onlinedate;
    	
    /**
     * @Column(type="float")
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
}
