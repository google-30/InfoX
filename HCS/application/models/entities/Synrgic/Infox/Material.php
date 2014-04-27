<?php
 
namespace Synrgic\Infox;
/**
 * @Entity
 * @Table(name="infox_material")
 */
class Material extends \Synrgic_Models_Entity {
    /**
     * @Id
     * @GeneratedValue(strategy="AUTO")
     * @Column(type="integer")
     */
    protected $id;	

    /**
     * @Column(type="string", nullable=true)
     */
    protected $nameeng;

    /**
     * @Column(type="string")
     */
    protected $name;
    	
    /**
     * @Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $update;
    
    /**
     * default supplier
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Supplier")
     */
    protected $supplier;

    /**
     * default price from supplier
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Supplyprice")
     */
    protected $supplyprice;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $pic;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $usage;

    /**
     * material type
     * 
     * @ManyToOne(targetEntity="Synrgic\Infox\Materialtype")
     */
    protected $type;

    /**
     * sheet name in material_list.xls, it's also equal to main material type   
     * @Column(type="string", nullable=true)
     */
    protected $sheet;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $unit;

    /**
     * @Column(type="integer", nullable=true)
     */
    protected $sn;    
}
