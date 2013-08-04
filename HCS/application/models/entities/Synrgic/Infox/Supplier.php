<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_supplier")
 */
class Supplier extends \Synrgic_Models_Entity {
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
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $officephone;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $mobilephone;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $email;    	

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $business;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $contact;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $fullname;

    /**
     * this string is for generating PO NO: HCD/WGS/HHL-001
     *   
     * @Column(type="string", nullable=true)
     */
    protected $postring;

    /**
     * attn name 
     *   
     * @Column(type="string", nullable=true)
     */
    protected $attn;


}
