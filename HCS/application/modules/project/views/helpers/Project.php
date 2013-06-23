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

    protected function td_leader($field, $row) 
    {
        $em = Zend_Registry::get('em');
        $data = $em->getRepository('Synrgic\Infox\Humanresource');   
        $name = $data->findOneBy(array("id"=>$row['id']))->getName();
        $name = ($name == "") ? "&nbsp;" : $name;
        return $name;
    }
    
}

?>
