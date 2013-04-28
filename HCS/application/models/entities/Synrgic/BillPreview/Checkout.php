<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */


namespace Synrgic\BillPreview;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * 
 * @Entity
 * @Table(name="checkout")
 */
class Checkout extends \Synrgic_Models_Entity {
    /**
     * Unique identifier for a page
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * Name of the guest
     * 
     * @Column(type="string")
     */
    protected $name;

    /** 
     * The name of the room
     *
     * @Column(type="string", length=50) 
     */
    protected $room;

    /** 
     * hard copy
     *
     * @Column(type="boolean") 
     */
    protected $hardcopy;

    /** 
     * soft copy
     *
     * @Column(type="boolean") 
     */
    protected $softcopy;

    /** 
     * email address
     *
     * @Column(type="string", length=50, nullable=true) 
     */
    protected $email;

    /** 
     * email content
     *
     * @Column(type="text", nullable=true) 
     */
    protected $mailcontent;

}

?>
