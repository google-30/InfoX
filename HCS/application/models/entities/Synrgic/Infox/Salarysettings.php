<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_salarysettings")
 */
class Salarysettings extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * 华人加班倍率
     * @Column(type="float", nullable=true)
     */
    protected $cotmultiple;

    /**
     * 孟加拉人加班倍率
     * @Column(type="float", nullable=true)
     */
    protected $botmultiple;

    /**
     * 工人伙食
     * @Column(type="float", nullable=true)
     */
    protected $workerfood;

    /**
     * 工长伙食
     * @Column(type="float", nullable=true)
     */
    protected $leaderfood;

    /**
     * 旷工天数
     * @Column(type="integer", nullable=true)
     */
    protected $absencethreshold;

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


}
