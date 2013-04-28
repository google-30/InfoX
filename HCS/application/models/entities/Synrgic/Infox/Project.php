<?php
 
namespace Synrgic;
/**
 * @Entity
 * @Table(name="infox_project")
 */
class Project extends \Synrgic_Models_Entity {
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
     * @Column(name="start", type="datetime", nullable=true)
     */
    protected $start;

    /**
     * @Column(name="stop", type="datetime", nullable=true)
     */
    protected $stop;

    /**
     * @Column(type="integer")
     */
    protected $workerno;    	
}
