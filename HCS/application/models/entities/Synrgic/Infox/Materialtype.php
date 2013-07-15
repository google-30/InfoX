<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_materialtype")
 */
class Materialtype extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * material type   
     * @Column(type="string", nullable=true)
     */
    protected $type;

    /**
     * main/parent type
     * @ManyToOne(targetEntity="Synrgic\Infox\Materialtype")
     */
    protected $main;
    
}
