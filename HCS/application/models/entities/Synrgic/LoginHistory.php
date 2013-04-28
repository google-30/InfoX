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
 * The User class represents a user who is able
 * to login to the system. This is not a guest.
 * 
 * @Entity
 * @Table(name="loginHistories")
 */
class LoginHistory extends \Synrgic_Models_Entity {
    /**
	 * Unique identifier for a page
	 * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

	/**
	 * The preferred language of the guest
	 * 
	 * @OneToOne(targetEntity="User")
	 * @JoinColumn(name="userid", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * Time of login
	 *
	 * @Column(type="datetime")
	 */
	protected $when;

	/**
	 * Attempt result
	 *
	 * @Column(type="boolean")
	 */
	protected $result;
}
