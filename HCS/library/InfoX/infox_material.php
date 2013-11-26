<?php

class infox_material
{
    public static $_em;
    public static $_material;
    public static $_supplyprice;
    public static $_materialtype;
    
    public static function getRepos()
    {
        //echo "infox_worker::getRepos";
        self::$_em = Zend_Registry::get('em');
        self::$_material = self::$_em->getRepository('Synrgic\Infox\Material');
        self::$_supplyprice = self::$_em->getRepository('Synrgic\Infox\Supplyprice');
        self::$_materialtype = self::$_em->getRepository('Synrgic\Infox\Materialtype');

    }

    public static function getSupplypricesByMaterial($material)
    {
        self::getRepos();
        $prices = self::$_supplyprice->findBy(array("material"=>$material));
        return $prices;
    }
    
    public static function getSummarySheets()
    {
        $sheets = array("Punggol", "Singapore Poly", "Orchard", "Feng Shan",
            "Gan Eng Seng", "Townsville", "Jurong Point");
        return $sheets;
    }
}
