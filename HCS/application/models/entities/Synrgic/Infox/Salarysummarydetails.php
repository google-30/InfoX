<?php

namespace Synrgic\Infox;

/**
 * @Entity
 * @Table(name="infox_salarysummarydetails")
 */
class Salarysummarydetails extends \Synrgic_Models_Entity {

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
     * @Column(type="string", nullable=true)
     */
    protected $sheet;    
    
    /**
     * @Column(type="float", nullable=true)
     */
    protected $normalhours;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $normalsalary;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $normalrate;
    
    /**
     * @Column(type="float", nullable=true)
     */
    protected $othours;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $otsalary;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $otrate;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $totalhours;

    /**
     * piecesalary is for whole day salary
     * @Column(type="float", nullable=true)
     */
    protected $piecesalary;      
    
    /**
     * 总工作 金额   
     * @Column(type="float", nullable=true)
     */
    protected $totalsalary;

    /**
     * 考勤天数
     * @Column(type="float", nullable=true)
     */
    protected $attenddays;

    /**
     * 缺勤天数
     * @Column(type="integer", nullable=true)
     */
    protected $absencedays;

    /**
     * 缺勤罚款
     * @Column(type="float", nullable=true)
     */
    protected $absencefines;

    /**
     * 伙食费
     * @Column(type="float", nullable=true)
     */
    protected $foodpay;

    /**
     * 预扣税 =?= release tax(rt)? 
     * @Column(type="float", nullable=true)
     */
    protected $rtmonthpay;

    /**
     * 
     * @Column(type="integer", nullable=true)
     */
    protected $rtmonths;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $rtall;

    /**
     * 水电生活费 utilities fee
     * @Column(type="float", nullable=true)
     */
    protected $utfee;

    /**
     * 水电生活补贴 utilities allowance
     * @Column(type="float", nullable=true)
     */
    protected $utallowance;

    /**
     * 
     * @Column(type="float", nullable=true)
     */
    protected $otherfee;
    
    /**
     * 提前结帐
     * @Column(type="float", nullable=true)
     */
    protected $inadvance;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $salary;

    /**
     * full attendance for this month
     * @Column(type="float", nullable=true)
     */
    protected $fullmonaward;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $netpay;
    
}
