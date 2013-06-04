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
     * @Column(type="string", nullable=true)
     */
    protected $title;

    /**
     * @Column(type="string")
     */
    protected $path;

    /**
     * @Column(type="integer")
     */
    protected $size;    	

    /**
     * @Column(type="string")
     */
    protected $remark;

   /**
     * @Column(type="string")
     */
    protected $type;


}
