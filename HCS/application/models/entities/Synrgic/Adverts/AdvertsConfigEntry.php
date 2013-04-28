<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/*-
 * AdvertsConfigEntry entity
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 11/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Synrgic\Adverts\AdvertsConfigEntryRepository")
 * @Table(name="adverts_config")
 */
class AdvertsConfigEntry extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * Media type: gif, png, txt, html,....
     * @Column(type="string", length=64, unique=true)
     */
    protected $name;

    /**
     * @Column(type="text")
     */
    protected $description;


    /**
     * For example, developer, product, may be system
     * @Column(name="perm_level", type="string")
     */
    protected $permLevel;

    /**
     * what exactly value depends on how to use this entry
     * @Column(type="string")
     */
    protected $value;

   /**
    * @Column(name="created_dt", type="datetime", nullable=false);
    */
    protected $createdTime;

    public function __construct()
    {
        $this->createdTime = new \DateTime('now');
    }
}
