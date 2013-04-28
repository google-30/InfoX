<?php
namespace Synrgic\Service;
use Doctrine\Common\Collections\ArrayCollection;
/**
 * @Entity(repositoryClass="Synrgic\Service\ServiceRepository")
 * @Table(name="services")
 */
class Service extends \Synrgic_Models_Entity
{

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;

    /**
     * @Column(type="string", length=64)
     */
    protected $name;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $click_count;

    /**
     * @Column(type="decimal", precision=10, scale=2,nullable=true)
     */
    protected $price;

    /**
     * @Column(type="string", length=255,nullable=true)
     */
    protected $key_words;

    /**
     * @Column(type="string", length=1)
     */
    protected $is_sale;

    /**
     * @Column(type="string", length=1,nullable=true)
     */
    protected $is_top;

    /**
     * @Column(type="string", length=1, nullable=true)
     */
    protected $is_deleted;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $top_index;

    /**
     * @Column(type="string", length=1,nullable=true)
     */
    protected $is_new;

    /**
     * @Column(type="integer",nullable=true)
     */
    protected $new_index;    

    /**
     * @Column(type="string", length=1, nullable=true)
     */
    protected $has_quantity;
    
    /**
     * @Column(type="string", length=1, nullable=true)
     */
    protected $has_remark;  
            
    /**
     * @Column(type="string", length=1, nullable=true)
     */
    protected $has_starttime;
    
    /**
     * @Column(type="string", length=1, nullable=true)
     */
    protected $has_stoptime;  

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $starttime;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $stoptime;  
    
    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $org_picture;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $icon;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $introduction;

    /**
     * @Column(type="text",nullable=true)
     */
    protected $remark;

    /**
     * @Column(type="datetime",nullable=true)
     */
    protected $add_time;
    
    /**
     * @Column(type="integer")
     */
    protected $type;
    
    /**
     * Who own this service
     * @ManyToOne(targetEntity="Catalog", cascade={"all"}, fetch="LAZY",inversedBy="services")
     * @JoinColumn(name="catalog_id", referencedColumnName="id")
     */
    protected $category;
    
    /**
     * Who own this service
     * @ManyToOne(targetEntity="Provider", cascade={"all"}, fetch="LAZY",inversedBy="services")
     * @JoinColumn(name="provider_id", referencedColumnName="id")
     */
    protected $provider;
    
    /**
     * @OneToMany(targetEntity="ServiceTranslate", cascade={"all"}, mappedBy="service")
     */
    protected $translate;
    
    public function __construct() {
    	$this->category = new ArrayCollection();
    	$this->provider = new ArrayCollection();
    	$this->translate = new ArrayCollection();
    	$this->is_top='1';
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
    public function getTranslateIntroduction($lang) {
    	$introduction=$this->introduction;
    	foreach($this->translate as $t )
    	{
    		if(($t->getLanguage()==$lang)&&($t->getIntroduction()<>NULL))
    			$introduction=$t->getIntroduction();
    	}
    	return   $introduction;
    }    
}
