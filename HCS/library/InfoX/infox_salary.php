<?php

class infox_salary
{
    public static $_em;
    public static $_siteatten;
    public static $_workerdetails;
    public static $_salaryhcc;    
    public static $_salaryhcb;    
    public static $_salaryhtc;    
    public static $_salaryhtb;
    public static $_setting;   

    public static function getRepos()
    {
        self::$_em = Zend_Registry::get('em');
        self::$_siteatten = self::$_em->getRepository('Synrgic\Infox\Siteattendance');
        self::$_workerdetails = self::$_em->getRepository('Synrgic\Infox\Workerdetails');

        self::$_salaryhcc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcc');
        self::$_salaryhcb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhcb');
        self::$_salaryhtc = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtc');
        self::$_salaryhtb = self::$_em->getRepository('Synrgic\Infox\Workersalaryhtb');

        self::$_setting = self::$_em->getRepository('Synrgic\Infox\Setting');
    }  

    public static function getSettingArray()
    {
        $settingnames = array("cotmultiple", "botmultiple", "workerfood", "leaderfood", 
                    "absencedays", "absencelow", "absencehigh", "absencetotal");
        return $settingnames;        
    }

    public static function getSettingBySectionName($section, $name)
    {
        self::getRepos();
        $setting = self::$_setting;
        $settingobj = $setting->findOneBy(array("section"=>$section, "name"=>$name)); 
        $value = $settingobj ? $settingobj->getValue() : "";

        return $value;
    }    

    public static function setSettingBySectionName($section, $name, $value)
    {
        self::getRepos();
        $setting = self::$_setting;
        $settingobj = $setting->findOneBy(array("section"=>$section, "name"=>$name)); 
        $querystr = "update Synrgic\Infox\Setting setting set setting.value='". $value 
                . "' where setting.name='" . $name . "' and setting.section='" . $section . "'";    
        //echo $querystr . "<br>";
        $query = self::$_em->createQuery($querystr);
        $result = $query->getResult();
    }
}
