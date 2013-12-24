<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_companyinfo")
 */
class Companyinfo extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="date", nullable=true)
     */
    protected $update;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $namechs;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $fullnamechs;

    /**
     * HWA CHENG   
     *    
     * @Column(type="string", nullable=true)
     */
    protected $nameeng;

    /**
     * HWA CHENG DEVELOPMENT PTE LTD
     *
     * @Column(type="string", nullable=true)
     */
    protected $fullnameeng;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $fax;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $email;    	

    /**
     * CO. REG.NO 201105053E
     *      
     * @Column(type="string", nullable=true)
     */
    protected $coregno;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $logo;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $postring;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $sheetprx;    
    
}
