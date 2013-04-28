<?php
 
namespace Synrgic\Information;
/**
 * @Entity
 * @Table(name="infodata")
 */
class Infodata extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", length=128)
     */
    protected $title;
 
    /**
     * @Column(type="text")
     */
    protected $content;
  
    /**
     * The language of the a page in the group
     * 
     * @ManyToOne(targetEntity="Synrgic\Language")
     */
    protected $language;

    /**
     * The page that this information belongs too
     *
     * @ManyToOne(targetEntity="Synrgic\Page")
     */
    protected $page;

    /**
     * level decides displaying order  
     * @Column(type="integer", nullable=true)
     */
    protected $level;
    
}

