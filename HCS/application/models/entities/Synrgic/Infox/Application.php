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

    /**
     * leader can set status for this application
     * only when this submit to upper level, 
     * then officer/manager can see it   
     *   
     * @Column(type="string")
     */
    protected $status0;

    /**
     * @Column(type="string")
     */
    protected $status1;

    /**
     * @Column(type="string")
     */
    protected $status2;

    /**
     * @Column(type="string")
     */
    protected $remark;
    
}
