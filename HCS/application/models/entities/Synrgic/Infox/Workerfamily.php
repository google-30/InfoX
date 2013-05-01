<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workerfamily")
 */
class Workerfamily extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * home address   
     * @Column(type="string",nullable=true)
     */
    protected $homeaddr;

    /**
     * family member 1
     * @Column(type="string",nullable=true)
     */
    protected $member1;   	

    /**
     * family member 1 contact
     * @Column(type="string",nullable=true)
     */
    protected $contact1;   	

    /**
     * family member 2
     * @Column(type="string",nullable=true)
     */
    protected $member2;   	

    /**
     * family member 2 contact
     * @Column(type="string",nullable=true)
     */
    protected $contact2;   	

    /**
     * family member 3
     * @Column(type="string",nullable=true)
     */
    protected $member3;   	

    /**
     * family member 3 contact
     * @Column(type="string",nullable=true)
     */
    protected $contact3;   	

    
}
