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
 * A page group represents a group of common pages
 * separated by language. Ie PageGroup Id = 1
 * has pageids 55,123 and 343 each relate to the
 * same content in different languages.
 *
 * @Entity
 * @Table(name="pagegroups")
 */
class PageGroup extends \Synrgic_Models_Entity {
    /**
     * Unique identifier for a group of pages
     * 
     * @Id
     * @Column(type="integer")
     */
    protected $id;

    /**
     * The page in the requested language
     * 
     * @OneToMany(targetEntity="Page", mappedBy="group")
     */
    protected $pages;
}

?>
