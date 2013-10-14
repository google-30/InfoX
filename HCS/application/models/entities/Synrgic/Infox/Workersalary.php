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
     * @Column(type="float", nullable=true)
     */
    protected $allpay;


}
