<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_setting")
 */
class Setting extends \Synrgic_Models_Entity {
    /**
     * Unique identifier for a page
     * 
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * Name of this setting
     * 
     * @Column(type="text")
     */
    protected $name;

    /**
     * Description of the setting
     *
     * @Column(type="text")
     */
    protected $description;

    /**
     * Section the setting belongs to
     *
     * @Column(type="text")
     */
    protected $section;

    /**
     * A basic value for the setting
     *
     * @Column(type="text")
     */
    protected $value;
}
