<?php
namespace Synrgic\Service;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="service_catalog_translate")
 */
class CatalogTranslate extends \Synrgic_Models_Entity
{

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * @ManyToOne(targetEntity="Catalog", cascade={"all"}, fetch="LAZY",inversedBy="translate")
     * @JoinColumn(name="catalog_id", referencedColumnName="id")
     */
    protected $catalog;

    /**
     * @Column(type="string", length=64)
     */
    protected $name;
    
     /**
     * @Column(type="text")
     */
    protected $language;
    
    public function __construct() {
    	$this->catalog = new ArrayCollection();
    }
    
}

