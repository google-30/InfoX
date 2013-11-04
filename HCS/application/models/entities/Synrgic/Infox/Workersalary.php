<?php
 
namespace Synrgic\Infox;
class Workersalary extends \Synrgic_Models_Entity {
    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Workerdetails")
     */
    protected $worker;

    /**
     * the working month
     *
     * @Column(type="date", nullable=true)
     */
    protected $month;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $normalhours;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $normalpay;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $othours;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $otpay;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $otprice;

    /**
     * @Column(type="float", nullable=true)
     */
    protected $allhours;

    /**
     * 总工作 金额   
     * @Column(type="float", nullable=true)
     */
    protected $allpay;

    /**
     * 考勤天数
     * @Column(type="integer", nullable=true)
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
     * 项目总工资
     * @Column(type="float", nullable=true)
     */
    protected $projectpay;

    /**
     * 伙食费天数
     * @Column(type="integer", nullable=true)
     */
    protected $fooddays;

    /**
     * 伙食费
     * @Column(type="float", nullable=true)
     */
    protected $foodpay;

    /**
     * 预扣税 =?= release tax(rt)? 
     * @Column(type="string", nullable=true)
     */
    protected $rtmonthpay;

    /**
     * 
     * @Column(type="string", nullable=true)
     */
    protected $rtmonths;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $rtall;

    /**
     * 水电生活费 utilities fee
     * @Column(type="string", nullable=true)
     */
    protected $utfee;

    /**
     * 水电生活补贴 utilities allowance
     * @Column(type="string", nullable=true)
     */
    protected $utallowance;

    /**
     * 
     * @Column(type="string", nullable=true)
     */
    protected $otherfee;
    
    /**
     * 提前结帐
     * @Column(type="string", nullable=true)
     */
    protected $inadvance;

    /**
     * 
     * @Column(type="string", nullable=true)
     */
    protected $remark;

    /**
     * calced pay =/= netpay
     * @Column(type="string", nullable=true)
     */
    protected $salary;

    /**
     * final pay for the worker
     * @Column(type="string", nullable=true)
     */
    protected $netpay;

    /**
     * rate for this month
     * @Column(type="float", nullable=true)
     */
    protected $rate;

}
