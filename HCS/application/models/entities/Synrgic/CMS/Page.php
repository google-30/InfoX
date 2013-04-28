<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
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
 * @Entity(repositoryClass="CMSRepository")
 * @Table(name="pages")
 */
class Page extends \Synrgic_Models_Entity {

    /**
     * Unique identifier for a page
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * The state of the page (based on the defines in this class)
     *
     * @Column(type="integer")
     */
    protected $state;
        const DRAFT = 0;
        const PUBLISHED = 1;
        const DELETED = 2;
        const INHERIT = 3;

    /**
     * @ManyToOne(targetEntity="Page")
     */
    protected $parent;

    /**
     * Type of post. Ie: 'CMSPage, InfoPage', 'CatalogItem'
     * @Column(type="string");
     */
    protected $type;
        const PAGE="page";
        const REVISION="revision";

    /**
     * Terms used to describe this post. Ie TagName, CategoryName
     *
     * @ManyToMany(targetEntity="Term")
     */
    protected $terms;

    /**
     * Date this page was created
     *
     * @Column(type="datetime");
     */
    protected $created;

    /**
     * The person who created this revision of the page
     *
     * @ManyToOne(targetEntity="\Synrgic\User");
     */
    protected $editor;

    /**
     * User entered content
     * 
     * @Column(type="text")
     */
    protected $content;

    /**
     * The user visible title of the page
     *
     * @Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * User comment on the page
     * 
     * @Column(type="text", nullable=true)
     */
    protected $remark;

    public function __construct()
    {
	$this->title = "";
	$this->terms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parent = NULL;
        $this->state = self::DRAFT;
        $this->created = new \DateTime();
        $this->type= self::PAGE;
    }

}

?>
