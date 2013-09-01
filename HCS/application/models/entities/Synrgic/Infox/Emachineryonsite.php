<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_emachineryonsite")
 */
class Emachineryonsite extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Emachinery")
     */
    protected $emachinery;

    /**
     * begin date
     *
     * @Column(type="date", nullable=true)
     */
    protected $begindate;

    /**
     * end date
     *
     * @Column(type="date", nullable=true)
     */
    protected $enddate;

    /**
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Site")
     */
    protected $site;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $remark;
    
}
