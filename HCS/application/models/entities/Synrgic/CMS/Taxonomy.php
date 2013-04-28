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
 * A Taxonomy is a way of grouping things, ie category or keyword.
 * A number of terms are associate with a taxonomy. Taxonomies can be
 * heirarchical in nature. Ie a category can contain a subcategory or
 * A category consists of keywords. The members of a taxonomy are terms.
 * Ie: Computers, Birds, Cars are all types(terms) of a category.
 * Objects are then associated with a taxonomy. Ie: 
 *
 *      Post1 -> Taxonomy(Category)->Term(Birds)
 *
 * The advantage to using a taxonomy is it allows association with more than
 * just one grouping. Ie A Post can have categories or keywords. Later a
 * suddenly a new grouping method is required (ie language). This can be 
 * represented using a taxonomy.
 *
 * @Entity
 * @Table(name="taxonomies")
 */
class Taxonomy extends \Synrgic_Models_Entity 
{
    /**
     * @Id
     * @Column(type="integer")
     */
    protected $id;

    /**
     * A list of all groups the device belongs to
     *
     * @OneToOne(targetEntity="Term",inversedBy="taxonomy")
     */
    protected $term;

    /**
     * @OneToOne(targetEntity="Taxonomy")
     */
    protected $parent;

    /**
     * The type of this taxonomy ie: Category, Keyword, etc
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="text")
     */
    protected $description;
}

?>
