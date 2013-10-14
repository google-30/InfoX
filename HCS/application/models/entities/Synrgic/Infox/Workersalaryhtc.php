<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workersalaryhtc")
 */
//class Workersalaryhtc extends \Synrgic_Models_Entity {
class Workersalaryhtc extends \Synrgic\Infox\Workersalary {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

}
