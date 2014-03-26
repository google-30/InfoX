<?php

namespace Synrgic\Infox;

/**
 * worker renew record information
 *   
 * @Entity
 * @Table(name="infox_workerrenew")
 */
class Workerrenew extends \Synrgic_Models_Entity {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	    
    
    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Workerdetails")
     */
    protected $worker;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $wpexpiry;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $issuedate;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $ppexpiry;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $rate;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $medicaldate;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $csoc;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $securityexp;
    
    /**
     * @Column(type="date", nullable=true)
     */
    protected $renewdate;    

}
