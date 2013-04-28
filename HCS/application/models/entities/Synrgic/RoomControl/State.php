<?php
namespace Synrgic\RoomControl;
/**
 * @Entity
 * @Table(name="roomctrl_state")
 */
class State extends \Synrgic_Models_Entity
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
    protected $changeid;
    
    /**
     * @Column(type="integer",nullable=true)
     */
    protected $roomid;

    /**
     * @Column(type="string", length=64,nullable=true)
     */
    protected $changeItem;
    
    /**
     * @Column(type="text",nullable=true)
     */
    protected $state;
    
    /**
     * @Column(type="integer")
     */
    protected $presetsid;    

    public function __construct() {
    	$this->changeid = 0;
    	$this->changeItem='all';
    	$this->presetsid=-1;
    }
    
    public function setChangeid() {
    	$this->changeid++;
    }
}

