<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workersalaryhtb")
 */
class Workersalaryhtb extends \Synrgic_Models_Entity {
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


}
