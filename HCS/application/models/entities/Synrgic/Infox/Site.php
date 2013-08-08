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
     * leader: person in charge 
     *   
     * @ManyToOne(targetEntity="Synrgic\Infox\Humanresource")
     */
    protected $leader;

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


}
