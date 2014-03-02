<?php

namespace Synrgic\Infox;

/**
 * @Entity
 * @Table(name="infox_salarysummarybysite")
 */
class Salarysummarybysite extends \Synrgic_Models_Entity {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * the working month
     *
     * @Column(type="date", nullable=true)
     */
    protected $month;

    /**
     * Construction site
     * @ManyToOne(targetEntity="Synrgic\Infox\Site")
     */
    protected $site;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $attendance=0.0;
    
    /**
     * piecesalary is for whole day salary
     * @Column(type="float", nullable=true)
     */
    protected $piecesalary=0.0;      
    
    /**
     * 总工作 金额   
     * @Column(type="float", nullable=true)
     */
    protected $totalsalary=0.0;

    /**
     * 考勤天数
     * @Column(type="float", nullable=true)
     */
    protected $attenddays=0;

}
