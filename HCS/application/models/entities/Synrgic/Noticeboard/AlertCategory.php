<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\Noticeboard;

/**
 * The AlertCategory class is a simple class which defines the category of alerts
 * 
 * @Entity
 */
class AlertCategory extends \Synrgic_Models_Entity {

    /**
     * Unique identifier
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * name of the category
     *
     * @Column(type="string")
     */
    protected $name;
}

?>
