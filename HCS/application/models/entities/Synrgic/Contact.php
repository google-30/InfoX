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
 * 
 * @Entity
 * @Table(name="contacts")
 */
class Contact extends \Synrgic_Models_Entity {
    /**
     * Unique identifier for a page
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * title
     * 
     * @Column(type="string")
     */
    protected $title;

    /** 
     * category
     *
     * @Column(type="string", length=50) 
     */
    protected $category;

    /** 
     * detail
     *
     * @Column(type="string") 
     */
    protected $detail;

}

?>
