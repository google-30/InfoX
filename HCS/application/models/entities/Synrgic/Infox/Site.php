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
     * @Column(type="string", nullable=true)
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

    /**
     * manager
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Humanresource")
     */
    protected $manager;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;

    /**
     * company in charge of this site
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Companyinfo")
     */
    protected $company;

    /**
     * site parts: floor1;floor2;roof;
     * @Column(type="string", nullable=true)
     */
    protected $parts;

    /**
     * general contractor总包单位
     *   
     * @Column(type="string", nullable=true)
     */
    protected $contractor;

    /**
     * site property: school, factory, condo
     *
     * @Column(type="string", nullable=true)
     */
    protected $property;

    /**
     * leader: person in charge 
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Humanresource")
     */
    protected $leader;

    /**
     * multiple leaders may be in charge in same time
     * "2,7,10" means id 2,7,10 in humanres table in charge of this site 
     *    
     * @Column(type="string", nullable=true)
     */
    protected $leaders;

    /**
     * material applicaiton status
     * true: allow requests from leaders
     * false: not allowed leaders to apply
     *   
     * @Column(type="boolean", nullable=true)
     */
    protected $permission1;

    /**
     * site progress or status
     * for example: pending, pause, done, etc.
     *    
     * @Column(type="string", nullable=true)
     */
    protected $status;

    /**
     * site purchase order(po) string
     * for example: tampines condo = tc
     *    
     * @Column(type="string", nullable=true)
     */
    protected $postr;
    
    /**
     * completed     
     *    
     * @Column(type="boolean", nullable=true)
     */
    protected $completed = false;       
    
}
