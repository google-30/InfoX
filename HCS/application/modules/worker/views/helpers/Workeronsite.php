<?php

class GridHelper_Workeronsite extends Grid_Helper_Abstract 
{
    protected function td_begindate($field, $row) 
    {
        $value = $row[$field] ? $row[$field]->format("Y/m/d") : "";
        $input = '<input type="text" id="begindate" class="datepicker" value="' . $value . '">';
        return $input;
    }    

    protected function td_enddate($field, $row) 
    {
        $value = $row[$field] ? $row[$field]->format("Y/m/d") : "";
        $input = '<input type="text" id="enddate" class="datepicker" value="' . $value . '">';
        return $input;
    }    

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


    protected function td_update($field, $row) 
    {
    	return '<button onclick="updaterow(' . $row['id'] . ')" data-inline="true" data-mini="true">更新</button>';
    }    

    protected function td_delete($field, $row) 
    {
    	return '<button onclick="updaterow(' . $row['id'] . ')" data-inline="true" data-mini="true">删除</button>';
    }    

    protected function td_begindate1($field, $row) 
    {
        $col = 'begindate';
        return $row[$col] ? $row[$col]->format("Y/m/d") : "&nbsp;";
    }    

    protected function td_enddate1($field, $row) 
    {
        $col = 'enddate';
        return $row[$col] ? $row[$col]->format("Y/m/d") : "&nbsp;";
    }    

    protected function td_site1($field, $row) 
    {
        $col = "site";
        $site = $row[$col];
        $sitename = $site ? $site->getName() : "&nbsp;";

        return $sitename;        
    }
}

?>
