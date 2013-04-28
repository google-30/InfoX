<?php

namespace Synrgic;

/** @Entity @Table(name="tv_ircode") */
class Tv_ircode extends \Synrgic_Models_Entity
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

     /** @Column(type="string", length=500) */
    protected $power;
    
     /** @Column(type="string", length=500) */
    protected $ch_u;
    
     /** @Column(type="string", length=500) */
    protected $ch_d;
    
    /** @Column(type="string", length=500) */
    protected $module;
    
    /** @Column(type="string", length=500) */
    protected $brand;
    
    /** @Column(type="string", length=500) */
    protected $val_u;
    
    /** @Column(type="string", length=500) */
    protected $val_d;
    
     /** @Column(type="string", length=500) */
    protected $mute;
    
    /** @Column(type="string", length=500) */
    protected $ok;
    
    /** @Column(type="string", length=500) */
    protected $digi_0;
    
     /** @Column(type="string", length=500) */
    protected $digi_1;
    
     /** @Column(type="string", length=500) */
    protected $digi_2;
    
     /** @Column(type="string", length=500) */
    protected $digi_3;
    
     /** @Column(type="string", length=500) */
    protected $digi_4;
    
     /** @Column(type="string", length=500) */
    protected $digi_5;
    
     /** @Column(type="string", length=500) */
    protected $digi_6;
    
     /** @Column(type="string", length=500) */
    protected $digi_7;
    
     /** @Column(type="string", length=500) */
    protected $digi_8;
    
     /** @Column(type="string", length=500) */
    protected $digi_9;
   
}