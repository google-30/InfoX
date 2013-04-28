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
 * The Page class represents user editble content
 * in a particular language. A page is versioned so
 * history of changes for that page may be retained
 * 
 * @Entity
 * @Table(name="infopages")
 */
class Page extends \Synrgic_Models_Entity {

    const DRAFT = 0;
    const PUBLISHED = 1;
    const DELETED = 2;
    const OBSOLETED = 3;
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

    /**
     * @OneToOne(targetEntity="Page")
     */
    protected $previousRevision;

    /**
     * The language of the a page in the group
     * 
     * @ManyToOne(targetEntity="Language")
     */
    protected $language;

    /**
     * User entered content
     * 
     * @Column(type="text")
     */
    protected $content;

    /**
     * Date this page revision was created
     *
     * @Column(type="datetime", name="created");
     */
    protected $created;

    /**
     * The page group that this page belongs too
     *
     * @ManyToOne(targetEntity="PageGroup", inversedBy="pages")
     */
    protected $group;

    /**
     * User remark
     * 
     * @Column(type="text", nullable=true)
     */
    protected $remark;
    	
    public function setLanguage(Language $c) {
        $this->language = $c;
    }

    public function setGroup(PageGroup $c) {
        $this->group = $c;
    }

    public function setPreviousRevision(Page $c) {
        $this->previousRevision = $c;
    }

    public function getLanguageName()
    {
        return $this->language->getName();
    }    
}

?>
