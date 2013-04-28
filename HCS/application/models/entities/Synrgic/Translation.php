<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic;

/**
 * The Translation Class indicates a translation of
 * a particular element to another language
 * 
 * @Entity
 * @Table(name="translation")
 */
class Translation extends \Synrgic_Models_Entity {

    /**
     * Unique identifier for a translation
     * 
     * @Id
     * @Column(type="integer")
     */
    protected $id;

    /**
     * The translation group id is used to group translations
     * of the same element. Ie For a page in different languages
     * the translation group number is the same but the element id
     * is different
     *
     * @Column(type="integer")
     */
    protected $group;

    /**
     * The type of element that has been translated.
     * ie: 'Page','Term','String'
     *
     * @Column(type="string")
     */
    protected $element_type;
        const PAGE = "page";

    /**
     * The ID of the translated element
     */
    protected $element_id;

    /**
     * language of the translation
     *
     * @ManyToOne(targetEntity="Language")
     */
    protected $language;

    /**
     * Source language
     * @ManyToOne(targetEntity="Language")
     */
    protected $sourceLanguage;
}

?>
