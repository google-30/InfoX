<?php
 
namespace Synrgic\Infox;
/**
 * provide by the company HT/HC
 *   
 * @Entity
 * @Table(name="infox_workerdetails")
 */
class Workerdetails extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", nullable=true)
     */
    protected $pic;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $sn;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $eeeno;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $namechs;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $nameeng;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $wpno;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $wpexpiry;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $doa;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $issuedate;

    /**
     * Fin number
     * @Column(type="string", nullable=true)
     */
    protected $finno;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $ppno;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $dob;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $ppexpiry;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $rate;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $worktype;    

    /**
     * @Column(type="date", nullable=true)
     */
    protected $arrivaldate;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $medicaldate;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $csoc;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $medicalinsurance;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $securityexp;
        
    /**
     * @Column(type="string", nullable=true)
     */
    protected $workingsite;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $dormitory;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $goodat;    
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $contactno1;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $contactno2;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $certificate;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $agent;   
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $hometown;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $education;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $age;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $marital;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $constructionworker;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $applyfor;

    /**
     * sheet name in excel
     * @Column(type="string", nullable=true)
     */
    protected $sheet;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $company;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $race="c";

    /**
     * @OneToOne(targetEntity="Synrgic\Infox\Workercustominfo")
     */
    protected $workercustominfo;   

    /**
     * hour rate
     * @Column(type="float", nullable=true)
     */
    protected $currentrate;

    /**
     * month rate
     * @Column(type="float", nullable=true)
     */
    protected $monthrate;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $resignremark;    
    
    /**
     * @Column(type="date", nullable=true)
     */
    protected $resigndate;      

}
