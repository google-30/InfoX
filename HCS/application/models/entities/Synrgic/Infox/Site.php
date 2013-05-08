<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_site")
 */
class Site extends \Synrgic_Models_Entity {
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
     * @Column(type="string")
     */
    protected $address;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $start;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $stop;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $workerno;    	


}
