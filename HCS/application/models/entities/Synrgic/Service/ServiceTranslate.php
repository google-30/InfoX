<?php
namespace Synrgic\Service;

/**
 * @Entity
 * @Table(name="services_translate")
 */
class ServiceTranslate extends \Synrgic_Models_Entity
{

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id = null;
    
    /**
     * @ManyToOne(targetEntity="Service", cascade={"all"}, fetch="LAZY",inversedBy="translate")
     * @JoinColumn(name="service_id", referencedColumnName="id")
     */
    protected $service;

    /**
     * @Column(type="string", length=64, nullable=true))
     */
    protected $name;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $org_picture;

    /**
     * @Column(type="string", length=255, nullable=true)
     */
    protected $icon;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $introduction;
    
    /**
     * @Column(type="text")
     */
    protected $language;
    
}
