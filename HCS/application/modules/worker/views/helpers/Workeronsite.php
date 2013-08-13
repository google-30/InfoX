<?php

class GridHelper_Workeronsite extends Grid_Helper_Abstract 
{
    protected function td_site($field, $row) 
    {
        /*
        $em = Zend_Registry::get('em');
        $wc = $em->getRepository('Synrgic\Infox\Workercompanyinfo');  
        $wcobj = $wc->findOneBy(array("id"=>$row['id']));                
        $siteobj = $wcobj ? $wcobj->getSite() : null;
        $sitename = $siteobj ? $siteobj->getName() : "&nbsp;";        
        return $sitename;
        */

        $site = $row[$field];
        $sitename = $site ? $site->getName() : "&nbsp;";

        return $sitename;        
    }

    protected function td_begindate($field, $row) 
    {
        if($row[$field])
        {
            return $row[$field]->format("Y/m/d");
        }
        
        return "&nbsp;";
    }    

    protected function td_enddate($field, $row) 
    {
        if($row[$field])
        {
            return $row[$field]->format("Y/m/d");
        }
        
        return "&nbsp;";
    }    

}

?>
