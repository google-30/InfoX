<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workerattendance")
 */
class Workerattendance extends \Synrgic_Models_Entity {
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
     * @Column(type="float", nullable=true)
     */
    protected $days;

    /**
     * misc info: info03   
     *
     * @Column(type="string", nullable=true)
     */
    protected $reason;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;
    
}
