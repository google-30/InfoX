<?php
namespace Synrgic\Service;

/**
 * @Entity(repositoryClass="Synrgic\Service\Detail_ordersRepository")
 * @Table(name="service_detail_orders")
 */
class Detail_orders extends \Synrgic_Models_Entity
{

    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

   /**
    * @ManyToOne(targetEntity="Confirm_orders", cascade={"all"}, fetch="EAGER",inversedBy="detail_orders")
    * @JoinColumn(name="confirm_orders_id", referencedColumnName="id")
    */
    protected $confirm_orders;

    /**
     * @Column(type="integer")
     */
    protected $service_id;

    /**
     * @Column(type="string", length=255)
     */
    protected $service_name;

    /**
     * @Column(type="decimal", precision=10, scale=2)
     */
    protected $service_price;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $remark;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $quantity;

    /**
     * @Column(type="integer")
     */
    protected $provider_id;

    /**
     * @Column(type="text", nullable=true)
     */
    protected $provider_name;

    /**
     * @Column(type="string", length=255)
     */
    protected $state;

    /**
     * @Column(type="datetime")
     */
    protected $operate_time;

    /**
     * @Column(type="string", length=1)
     */
    protected $is_deleted;

    public function __construct() {
        $this->operate_time = new \DateTime('now');
        $this->state='new';
        $this->is_deleted='0';
    }
}
