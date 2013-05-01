<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workerskill")
 */
class Workerskill extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string",nullable=true)
     */
    protected $worktype;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $worklevel;

    /**
     * education
     * @Column(type="string")
     */
    protected $education;
 
    /**
     * occupation in past
     * @Column(type="string",nullable=true)
     */
    protected $pastwork;   	

    /**
     * skill 1   
     * @Column(type="string")
     */
    protected $skill1;

    /**
     * skill 2   
     * @Column(type="string")
     */
    protected $skill2;

    /**
     * Driver license  
     * @Column(type="string")
     */
    protected $drvlic;

    /**
     * Security certificate - 安全证期限
     * @Column(type="date", nullable=true)
     */
    protected $securityexp;

}
