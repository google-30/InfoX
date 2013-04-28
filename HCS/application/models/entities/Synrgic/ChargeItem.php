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

/**
 * @Entity
 * @Table(name="charge_item")
 */
class ChargeItem extends \Synrgic_Models_Entity
{
    /**
     * Unique ID required by the database
     *
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * The name of the charge item
     * @Column(type="text")
     */
    protected $name;
    
    /**
     * How many units
     * @Column(type="integer", nullable=false)
     */
    protected $units = 1;
    
    /**
     * The units price of the charge
     * @Column(type="decimal", precision=8, scale=2)
     */
    protected $price = 0.0;
    
    /**
     * Charge model to which this item belongs
     * @ManyToOne(targetEntity="Synrgic\ChargeModel", cascade={"all"}, fetch="EAGER", inversedBy="chargeItems")
     * @JoinColumn(name="charge_model_id", referencedColumnName="id")
     */
    protected $model;
}
