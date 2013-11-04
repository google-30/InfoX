<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workersalaryhcb")
 */
class Workersalaryhcb extends \Synrgic\Infox\Workersalary {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

}
