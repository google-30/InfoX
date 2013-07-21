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
        /*
        $em = Zend_Registry::get('em');
        $humanres = $em->getRepository('Synrgic\Infox\Humanresource');  
        $data = $humanres->findOneBy(array("id"=>$row['id']));                
        $name = (isset($data)) ? $data->getName() : "&nbsp;";
        return $name;
        */
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
    
}

?>
