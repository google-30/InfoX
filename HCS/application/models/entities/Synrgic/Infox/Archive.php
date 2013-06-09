<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_archive")
 */
class Archive extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="date", nullable=true)
     */
    protected $update;

    /**
     * @Column(type="string")
     */
    protected $title;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $path;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $size;    	

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;

   /**
     * @Column(type="string", nullable=true)
     */
    protected $type;


}
