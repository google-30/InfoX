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
     * material type in chs  
     * @Column(type="string", nullable=true)
     */
    protected $typechs;

    /**
     * material type in Eng
     * @Column(type="string", nullable=true)
     */
    protected $typeeng;

    /**
     * main/parent type
     * @ManyToOne(targetEntity="Synrgic\Infox\Materialtype")
     */
    protected $main;
    
}
