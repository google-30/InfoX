<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_worker")
 */
class Worker extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", nullable=true)
     */
    protected $nameeng;

    /**
     * @Column(type="string")
     */
    protected $namechs;

    /**
     * @Column(type="integer")
     */
    protected $age;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $worktype;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $worklevel;

    /**
     * @Column(type="float",nullable=true)
     * houly wage   
     */
    protected $hwage;

    /**
     * Construction site
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Site")
     */
    protected $site;

    /**
     * Fin number
     * @Column(type="string")
     */
    protected $finno;

    /**
     * work pass expire
     * @Column(type="date", nullable=true)
     */
    protected $passexp;

    /**
     * passport number
     * @Column(type="string")
     */
    protected $passport;

    /**
     * work pass expire
     * @Column(type="date", nullable=true)
     */
    protected $passportexp;

    /**
     * Security certificate
     * @Column(type="date", nullable=true)
     */
    protected $securityexp;


    /**
     * @Column(type="date", nullable=true)
     */
    protected $birth;
    	
    /**
     * @Column(type="string")
     */
    protected $skills;
    
    /**
     * @Column(type="string")
     */
    protected $pic;
    
}
