<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workercompanyinfo")
 */
class Workercompanyinfo extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * hourly wage
     * @Column(type="float",nullable=true)   
     */
    protected $hwage;

    /**
     * Construction site
     * @ManyToOne(targetEntity="Synrgic\Infox\Site")
     */
    protected $site;

    /**
     * service time
     * @Column(type="integer")
     */
    protected $srvyears;

    /**
     * service time in Singapore
     * @Column(type="integer")
     */
    protected $yrsinsing;

    /**
     * worker serves this company
     * @ManyToOne(targetEntity="Synrgic\Infox\Companyinfo")
     */
    protected $company;

}
