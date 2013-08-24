<?php

class GridHelper_Worker extends Grid_Helper_Abstract 
{
    protected function td_passexp($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }

    protected function td_passportexp($field, $row) 
    {
        return $row[$field] ? $row[$field]->format('Y-m-d') : "&nbsp;";      
    }

    protected function td_arrivesing($field, $row) 
    {
        return $row[$field] ? $row[$field]->format('Y-m-d') : "&nbsp;";      
    }

    protected function td_leavesing($field, $row) 
    {
        return $row[$field] ? $row[$field]->format('Y-m-d') : "&nbsp;";      
    }

    protected function td_hwage($field, $row) 
    {
        $data = $row["workercompanyinfo"]->getHwage();
        return $data ? $data : "&nbsp;";
    }

    protected function td_srvyears($field, $row) 
    {
        $data = $row["workercompanyinfo"]->getSrvyears();
        return $data ? $data : "&nbsp;";
    }

    protected function td_yrsinsing($field, $row) 
    {
        $data = $row["workercompanyinfo"]->getYrsinsing();
        return $data ? $data : "&nbsp;";
    }

    protected function td_worklevel($field, $row) 
    {
        $data = $row["workerskill"]->getWorklevel();
        return $data ? $data : "&nbsp;";
    }

    protected function td_education($field, $row) 
    {
        $data = $row["workerskill"]->getEducation();
        return $data ? $data : "&nbsp;";
    }

    protected function td_pastwork($field, $row) 
    {
        $data = $row["workerskill"]->getPastwork();
        return $data ? $data : "&nbsp;";
    }

    protected function td_skill1($field, $row) 
    {
        $data = $row["workerskill"]->getSkill1();
        return $data ? $data : "&nbsp;";
    }

    protected function td_skill2($field, $row) 
    {
        $data = $row["workerskill"]->getSkill2();
        return $data ? $data : "&nbsp;";
    }

    protected function td_drvlic($field, $row) 
    {
        $data = $row["workerskill"]->getDrvlic();
        return $data ? $data : "&nbsp;";
    }

    protected function td_company($field, $row) 
    {
        /*
        $em = Zend_Registry::get('em');
        $wc = $em->getRepository('Synrgic\Infox\Workercompanyinfo');  
        $wcobj = $wc->findOneBy(array("id"=>$row['id']));                
        $companyobj = $wcobj ? $wcobj->getCompany() : null;
        $companyname = $companyobj ? $companyobj->getNamechs() : "&nbsp;";
        return $companyname;        
        */
        $data = $row["workercompanyinfo"]->getCompany();        
        return $data ? $data->getNamechs() : "&nbsp;";
    }

    protected function td_site($field, $row) 
    {// TODO: show the latest on site 
        $em = Zend_Registry::get('em');
        $wc = $em->getRepository('Synrgic\Infox\Workercompanyinfo');  
        $wcobj = $wc->findOneBy(array("id"=>$row['id']));                
        $siteobj = $wcobj ? $wcobj->getSite() : null;
        $sitename = $siteobj ? $siteobj->getName() : "&nbsp;";        
        return $sitename;

        /*
        $site = $row[$field];
        $sitename = $site ? $site->getName() : "&nbsp;";
        return $sitename;        
        */    
    }

    protected function td_securityexp($field, $row) 
    {
        $dateobj = $row["workerskill"]->getSecurityexp();
        $datestr = $dateobj ? $dateobj->format('Y-m-d') : "&nbsp;";
        return $datestr;
    }  

    protected function td_worktype($field, $row) 
    {
        $worktype = $row["workerskill"]->getWorktype();
        return $worktype ? $worktype : "&nbsp;";
    }


}

?>
