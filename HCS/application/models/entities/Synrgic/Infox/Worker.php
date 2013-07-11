<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_worker")
 */
class Worker extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string")
     */
    protected $nameeng;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $namechs;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $pic;

    /**
     * Fin number
     * @Column(type="string")
     */
    protected $fin;

    /**
     * work pass expire
     * @Column(type="date", nullable=true)
     */
    protected $passexp;

    /**
     * passport number
     * @Column(type="string")
     */
    protected $passport;

    /**
     * work pass expire
     * @Column(type="date", nullable=true)
     */
    protected $passportexp;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $gender;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $age;

    /**
     * birthday
     * @Column(type="date", nullable=true)
     */
    protected $birth;

    /**
     * Marital status - 婚姻状况  
     * @Column(type="string", nullable=true)
     */
    protected $marital;

    /**
     * singapore address
     * @Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * home town - 籍贯
     * @Column(type="string", nullable=true)
     */
    protected $hometown;

    /**
     * @OneToOne(targetEntity="Synrgic\Infox\Workerskill")
     */
    protected $workerskill;

    /**
     * @OneToOne(targetEntity="Synrgic\Infox\Workerfamily")
     */
    protected $workerfamily;

    /**
     * @OneToOne(targetEntity="Synrgic\Infox\Workercompanyinfo", fetch="EAGER")
     */
    protected $workercompanyinfo;
   
}
