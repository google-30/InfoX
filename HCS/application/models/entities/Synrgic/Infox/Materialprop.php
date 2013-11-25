<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_materialprop")
 */
class Materialprop extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * 
     * @Column(type="string", nullable=true)
     */
    protected $catalogchs;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $catalogeng;
    
}
