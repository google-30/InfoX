<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_application")
 */
class Application extends \Synrgic_Models_Entity {
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
     * @Column(type="datetime", nullable=true)
     */
    protected $createdate;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $updatedate;

    /**
     * Construction site
     * @ManyToOne(targetEntity="Synrgic\Infox\Site")
     */
    protected $site;

    /**
     * Applicant
     * @ManyToOne(targetEntity="Synrgic\Infox\Humanresource")
     */
    protected $applicant;

    /**
     * delivery contact person
     * @ManyToOne(targetEntity="Synrgic\Infox\Humanresource")
     */
    protected $porter;

    /**
     * @Column(type="text")
     */
    protected $materials;

    
}
