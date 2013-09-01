<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workercustominfo")
 */
class Workercustominfo extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string",nullable=true)
     */
    protected $custom1;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $custom2;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $custom3;

    /**
     * @Column(type="string",nullable=true)
     */
    protected $custom4;

}
