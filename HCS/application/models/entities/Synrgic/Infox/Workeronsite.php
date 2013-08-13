<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workeronsite")
 */
class Workeronsite extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Worker")
     */
    protected $worker;

    /**
     * begin date
     *
     * @Column(type="date", nullable=true)
     */
    protected $begindate;

    /**
     * end date
     *
     * @Column(type="date", nullable=true)
     */
    protected $enddate;

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Site")
     */
    protected $site;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;
    
}
