<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_miscinfo")
 */
class Miscinfo extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string")
     */
    protected $namechs;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $nameeng;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $values;

}
