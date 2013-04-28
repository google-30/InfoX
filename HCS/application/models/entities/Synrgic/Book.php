<?php

namespace Synrgic;

/**
 * @Entity(repositoryClass="Synrgic\BookRepository")
 * @Table(name="books")
 */
class Book extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * @Column(type="string", length=128)
     */
    protected $subject;

    /**
     * @Column(type="string", length=64)
     */
    protected $author;

    /**
     * @Column(type="decimal", precision=10, scale=2)
     */
    protected $price;
}
