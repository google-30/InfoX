<?php

namespace Synrgic\Infox;

/**
 * @Entity
 * @Table(name="infox_salaryreceipt")
 */
class Salaryreceipt extends \Synrgic_Models_Entity {

    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;

    /**
     * the working month
     *
     * @Column(type="date", nullable=true)
     */
    protected $month;

    /**
     * receipt date
     * @Column(type="date", nullable=true)
     */
    protected $date;

}
