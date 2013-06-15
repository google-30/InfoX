<?php

class GridHelper_Material extends Grid_Helper_Abstract 
{
    protected function td_supplier($field, $row) 
    {
        if(is_null($row->$field))
        {
            return "&nbsp;";
        }
        else
        {
            return $row->$field->getName();
        }    
    }

    protected function td_update($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }
}

?>
