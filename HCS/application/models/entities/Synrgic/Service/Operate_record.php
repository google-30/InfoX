<?php

namespace Synrgic\Service;

/**
 * @Entity(repositoryClass="Synrgic\Service\Operate_recordRepository")
 * @Table(name="service_operate_record")
 */
class Operate_record extends \Synrgic_Models_Entity
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /** @Column(type="integer") */
    protected $user_id;
    
    /** @Column(type="datetime") */
    protected $time;
    
    /** @Column(type="text") */
    protected $event;
    
}