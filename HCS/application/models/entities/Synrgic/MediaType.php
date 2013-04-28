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
 * MediaType entity
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 11/10/2012
 */

namespace Synrgic;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="media_type")
 */
class MediaType extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     @ @Column(type="integer")
     */
    protected $id = null;

    /**
     * Media type: gif, png, txt, html,....
     * @Column(type="string", length=32, unique=true)
     */
    protected $type;

    /**
     * @Column(type="text")
     */
    protected $description;

    /**
     * @OneToMany(targetEntity="Media", cascade={"all"}, mappedBy="mediaType")
     */
    protected $medias;

   /**
    * @Column(name="created_dt", type="datetime", nullable=false);
    */
    protected $createdTime;

    public function __construct()
    {
        $this->createdTime = new \DateTime('now');
        $this->medias = new ArrayCollection(); 
    }
}
