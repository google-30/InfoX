<?php
namespace Synrgic\Service;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Synrgic\Service\CatalogRepository")
 * @Table(name="service_catalog")
 */
class Catalog extends \Synrgic_Models_Entity
{

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * @Column(type="integer")
     */
    protected $fid;
        const TOP_CATALOG=-1;

    /**
     * @Column(type="string", length=64)
     */
    protected $name;

    /**
     * @OneToOne(targetEntity="\Synrgic\Media")
     */
    protected $icon;

    /**
     * @Column(type="integer", length=1)
     */
    protected $is_display;
    
    /**
     * @OneToMany(targetEntity="Service", cascade={"all"}, mappedBy="category")
     */
    protected $services;
    
    /**
     * @OneToMany(targetEntity="CatalogTranslate", cascade={"all"}, mappedBy="catalog")
     */
    protected $translate;
    
    public function __construct() {
    	$this->services = new ArrayCollection();
    	$this->translate = new ArrayCollection();    	
        $this->icon = null;
    }
    
    public function getTranslateName($lang) {
    	$name=$this->name;
    	foreach($this->translate as $t )
    	{
    		if(($t->getLanguage()==$lang)&&($t->getName()<>NULL))
    			$name=$t->getName();
    	}
    	return   $name;
    }   
}

