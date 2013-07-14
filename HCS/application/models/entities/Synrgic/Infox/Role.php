<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_role")
 */
class Role extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", nullable=true)
     */
    protected $role;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $rolechs;

}
