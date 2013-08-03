<?php

class GridHelper_Worker extends Grid_Helper_Abstract 
{
    protected function td_passexp($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }

    protected function td_company($field, $row) 
    {
        $em = Zend_Registry::get('em');
        $wc = $em->getRepository('Synrgic\Infox\Workercompanyinfo');  
        $wcobj = $wc->findOneBy(array("id"=>$row['id']));                
        $companyobj = $wcobj ? $wcobj->getCompany() : null;
        $companyname = $companyobj ? $companyobj->getNamechs() : "&nbsp;";

        return $companyname;        
    }

    protected function td_site($field, $row) 
    {
        $em = Zend_Registry::get('em');
        $wc = $em->getRepository('Synrgic\Infox\Workercompanyinfo');  
        $wcobj = $wc->findOneBy(array("id"=>$row['id']));                
        $siteobj = $wcobj ? $wcobj->getSite() : null;
        $sitename = $siteobj ? $siteobj->getName() : "&nbsp;";

        return $sitename;
    }

    protected function td_securityexp($field, $row) 
    {
        $em = Zend_Registry::get('em');
        $ws = $em->getRepository('Synrgic\Infox\Workerskill');  
        $wsobj = $ws->findOneBy(array("id"=>$row['id']));                
        $sedate = $wsobj ? $wsobj->getSecurityexp() : null;
        $date = $sedate ? $sedate->format('Y-m-d') : "&nbsp;";

        return $date;
    }

}

?>
