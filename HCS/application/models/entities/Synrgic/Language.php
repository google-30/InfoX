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
 * The Language class 
 * indicate a particular language the system 
 * supports
 * 
 * @Entity(repositoryClass="Synrgic\LanguageRepository")
 * @Table(name="Language")
 */
class Language extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * English Name of the language
     * 
     * @Column(type="string")
     */
    protected $name;



    /**
     * Locale of the language
     *
     * @Column(type="string", length=5)
     */
    protected $locale;

    /**
     * @Column(type="integer")
     */
    protected $active;
}
