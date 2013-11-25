<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_ordermaterialsummary")
 */
class Ordermaterialsummary extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", nullable=true)
     */
    protected $sn;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $catalog;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $itemc;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $iteme;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $unit;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $qty;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $price;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $orderdate;
    
    /**
     * @Column(type="string", nullable=true)
     */
    protected $supplier;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $totalamount;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $workerqty;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;
    
}
