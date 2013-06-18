<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_humanresource")
 */
class Humanresource extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $nameeng;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $date;
    	
    /**
     * @Column(type="string", nullable=true)
     */
    protected $phone1;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $phone2;
  
    /**
     * @Column(type="string", nullable=true)
     */
    protected $email1;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $email2;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $othercontact;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $position; 

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;    
 
    /**
     * @Column(type="string", nullable=true)
     */
    protected $username;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $password;    
     
    
}
