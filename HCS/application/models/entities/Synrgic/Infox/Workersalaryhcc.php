<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_workersalaryhcc")
 */
//class Workersalaryhcc extends \Synrgic_Models_Entity {
class Workersalaryhcc extends \Synrgic\Infox\Workersalary {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

}
