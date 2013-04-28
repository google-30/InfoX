<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/

namespace Synrgic;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * A charge model is a collection of charge items
 * 
 * @Entity(repositoryClass="\Synrgic\ChargeModelRepository")
 * @Table(name="charge_model")
 */
class ChargeModel extends \Synrgic_Models_Entity
{
    /**
     * Unique ID required by the database
     *
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The name of charge model
     * @Column(type="text", nullable=false)
     */
    protected $name;
    
    
    /**
     * Indicate if a charge model is active
     * @Column(type="boolean", nullable=false)
     */
    protected $activated = true;
    
    /**
     * Charge items belong to this model
     * @OneToMany(targetEntity="ChargeItem", cascade={"all"}, mappedBy="model", fetch="EAGER")
     */
    protected $chargeItems;
    
    /**
     * Algorithm to calculate the charges according to this model
     * @Column(type="text")
     */
    protected $algorithm = 'AccumulateByItems'; //default is 'AccumulateByItems'
    
    public function __construct() {
        $this->chargeItems = new ArrayCollection();
    }
}
