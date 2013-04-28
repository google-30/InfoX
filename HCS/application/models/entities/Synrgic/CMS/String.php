<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\CMS;

/**
 * The String class represents a string in a particular language
 *
 * @Entity
 * @Table(name="strings")
 */
class String extends \Synrgic_Models_Entity {

    /**
     * Unique identifier for a page
     * 
     * @Id
     * @Column(type="integer")
     */
    protected $id;

    /**
     * The text of this string
     *
     * @Column(type="text");
     */
    protected $text;
}

?>
