<?php
namespace Synrgic\Service;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Synrgic\Service\ProviderRepository")
 * @Table(name="service_provider")
 */
class Provider extends \Synrgic_Models_Entity
{

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string", length=255)
     */
    protected $name;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $remark;
    
    /**
     * @OneToMany(targetEntity="Service", cascade={"all"}, mappedBy="provider")
     */
    protected $services;
    
    public function __construct() {
    	$this->services = new ArrayCollection();
    }
}