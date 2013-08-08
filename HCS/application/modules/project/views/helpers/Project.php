<?php

class GridHelper_Project extends Grid_Helper_Abstract 
{
    protected function td_start($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }

    protected function td_stop($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }

    protected function td_company($field, $row) 
    {
        if($row[$field])
        {           
            return $row[$field]->getNamechs();
            //return "&nbsp;";    
        }
        else
        {
            return "&nbsp;";
        }
    }

    // $row is row data in grid
    protected function td_leader($field, $row) 
    {
        if($row[$field])
        {
            return $row[$field]->getName();
        }
        else
        {
            return "&nbsp;";
        }
    }

    protected function td_leaders($field, $row) 
    { // "2,4,7"
        if(!$row[$field]) return "&nbsp;";

        $array = explode(";", $row[$field]);
        $namesStr = "";
        
        $em = Zend_Registry::get('em');
        $humanres = $em->getRepository('Synrgic\Infox\Humanresource');  

        foreach($array as $tmp)
        {
            $data = $humanres->findOneBy(array("id"=>$tmp));
            $name = $data ? $data->getName() : "&nbsp;";
            $namesStr .= $name . "；&nbsp;";
        }
                
        return $namesStr;
    }

    
}

?>
