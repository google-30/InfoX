<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workersalaryall")
 */
class Workersalaryall extends \Synrgic\Infox\Workersalary {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

}