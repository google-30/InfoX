<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_emachinery")
 */
class Emachinery extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * emachinery is also a kind of material
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Material")
     */
    protected $material;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $purchasedate;

    /**
     * serial number   
     * 
     * @Column(type="string", nullable=true)
     */
    protected $sn;    	

    /**
     * on site
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Site")
     */
    protected $site;

    /**
     * machine status
     * 
     * @Column(type="string", nullable=true)
     */
    protected $status;    	

    /**
     * @Column(type="text", nullable=true)
     */
    protected $remark;

    /**
     * when a machine comes to its life end
     *      
     * @Column(type="date", nullable=true)
     */
    protected $scrapdate;

     
}
