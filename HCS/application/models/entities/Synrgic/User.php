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
 * @Table(name="users")
 */
class User extends \Synrgic_Models_Entity {
    /**
     * Unique identifier for a page
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * Username of the user
     *
     * @Column(type="string", length=32)
     */
    protected $username;

    /**
     * Password of the user. 
     *
     * @Column(type="text")
     */
    protected $password;

    /**
     * Name of the User
     * 
     * @Column(type="string")
     */
    protected $name;

    /**
     * The preferred language of the guest
     * 
     * @ManyToOne(targetEntity="Language", fetch="EAGER" )
     */
    protected $preferredLanguage;

    /**
     * Indicate if the account is diabled or not
     *
     * @Column(type="boolean");
     */
    protected $disabled;

    /**
     * The role that the user posesses. These map back to the 
     * ACL role names
     *
     * @Column(type="string",length=20)
     */
    protected $role;

    /**
     * relation between human resource and user
     * 
     * @OneToOne(targetEntity="Synrgic\Infox\Humanresource", fetch="EAGER" )
     */
    protected $humanresource;

}

