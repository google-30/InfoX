<?php

namespace Synrgic\Infox;

/**
 * @Entity
 * @Table(name="infox_salarysummary")
 */
class Salarysummary extends \Synrgic_Models_Entity {

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

    /**
     * hcc salary
     * @Column(type="float", nullable=true)
     */
    protected $hccsalary;

    /**
     * hcb salary
     * @Column(type="float", nullable=true)
     */
    protected $hcbsalary;

    /**
     * htc salary
     * @Column(type="float", nullable=true)
     */
    protected $htcsalary;

    /**
     * htb salary
     * @Column(type="float", nullable=true)
     */
    protected $htbsalary;
    
    /**
     * others salary
     * @Column(type="float", nullable=true)
     */
    protected $otherssalary;
}
