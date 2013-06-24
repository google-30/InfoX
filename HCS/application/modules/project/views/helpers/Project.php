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
        return $row[$field]->getName();
        /*
        $em = Zend_Registry::get('em');
        $humanres = $em->getRepository('Synrgic\Infox\Humanresource');  
        $data = $humanres->findOneBy(array("id"=>$row['id']));                
        $name = (isset($data)) ? $data->getName() : "&nbsp;";
        return $name;
        */
    }
    
}

?>
