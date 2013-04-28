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

/*
 * Media entity
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 10/10/2012
 */


use \Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Synrgic\MediaRepository")
 */
class Media extends \Synrgic_Models_Entity {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * Internal Path ie(adverts://blah)
     * @Column(type="string")
     */
    protected $ipath;

    /**
     * @Column(type="text", nullable=false)
     * Relative path on disk
     */
    protected $path;

    /**
     * @ManyToOne(targetEntity="MediaType", fetch="EAGER", inversedBy="medias")
     * @JoinColumn(name="media_type_id", referencedColumnName="id")
     */
    protected $mediaType;

    /**
     * @Column(name="created_dt", type="datetime")
     */
    protected $createdTime;

    public function __construct() 
    {
	$this->createdTime = new \DateTime('now');
	$this->adverts = new ArrayCollection();
    }
}
