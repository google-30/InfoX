<?php

class GridHelper_Humanres extends Grid_Helper_Abstract 
{
    protected function td_role($field, $row) 
    {
        $role = "&nbsp";
        if($row->$field)
        {
            $role = $row->$field->getRolechs();
        } 
    	return $role;
    }    
}

?>
