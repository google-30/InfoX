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
 * The Page class represents user editble content
 * in a particular language. A page is versioned so
 * history of changes for that page may be retained
 * 
 * @Entity
 * @Table(name="terms")
 */
class Term extends \Synrgic_Models_Entity 
{
    /**
     * @Id
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * Associated Taxonomy
     *
     * @OneToOne(targetEntity="Taxonomy",mappedBy="term");
     */
    protected $taxonomy;
}

?>
