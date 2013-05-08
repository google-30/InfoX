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
     * company label - 公司编号   
     * @Column(type="string",nullable=true)
     */
    protected $companylabel;

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
       

}
