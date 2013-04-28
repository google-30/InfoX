<?php
namespace Synrgic\Service;

use Doctrine\Common\Collections\ArrayCollection;
/**
 * @Entity(repositoryClass="Synrgic\Service\Confirm_ordersRepository")
 * @Table(name="service_confirm_orders")
 */
class Confirm_orders extends \Synrgic_Models_Entity
{

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string", length=255)
     */
    protected $state;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $remark;

    /**
     * @Column(type="decimal", precision=10, scale=2)
     */
    protected $total_price;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $confirm_time;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $scheduled_time;

    /**
     * @Column(type="integer")
     */
    protected $occupiedroom_id;

    /**
     * @Column(type="integer")
     */
    protected $type;

    /**
     * @Column(type="string", length=1)
     */
    protected $is_deleted;

    /**
    * @OneToMany(targetEntity="Detail_orders", cascade={"all"}, mappedBy="confirm_orders")
    */
    protected $detail_orders;

    public function __construct() {
        $this->confirm_time = new \DateTime('now');
        $this->scheduled_time=new \DateTime('now');
	    $this->detail_orders = new ArrayCollection();
	    $this->state='new';
	    $this->is_deleted='0';
    }
    public function setTotalPrice() {
    	$total_price = 0;
    	foreach($this->detail_orders as $r){
    		$price = $r->getService_price();
    		$num = $r->getQuantity();
    		$total_price += $price*$num;
    	}
    	$this->total_price=$total_price;
    } 
    public function setDelete() {
    	$this->is_deleted='1';
    	foreach($this->detail_orders as $r){
    		$r->setis_deleted('1');
    	}
    }   
    public function refreshState(){    
    	$data=$this->detail_orders;
    	if(count($data)>0)
    	{
    		$finish=0;
    		$state='new';
    		 
    		foreach($data as $r)
    		{
    			if(($r->getState()=='finish')||($r->getState()=='called')||($r->getState()=='transport finish'))
    			{
    				$finish++;
    			}
				if($r->getState()!='new')
				{
					$state='doing';					
				}					
   				
    		}
    		if($finish==count($data))
    		{
    			$state='finish';
    		}
   		 
    		$this->state=$state;
    	}
    }    
}
