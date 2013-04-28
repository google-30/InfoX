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
 * The Setting class provides a key value pair
 * indicating a particular setting in a section
 * 
 * @Entity
 * @Table(name="settings")
 */
class Setting extends \Synrgic_Models_Entity {
    /**
     * Unique identifier for a page
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * Name of this setting
     * 
     * @Column(type="text")
     */
    protected $name;

    /**
     * Description of the setting
     *
     * @Column(type="text")
     */
    protected $description;

    /**
     * Section the setting belongs to
     *
     * @Column(type="text")
     */
    protected $section;

    /**
     * A basic value for the setting
     *
     * @Column(type="text")
     */
    protected $value;
}

?>
