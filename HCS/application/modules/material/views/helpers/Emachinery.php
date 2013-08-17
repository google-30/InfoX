<?php

class GridHelper_Emachinery extends Grid_Helper_Abstract 
{
    protected function td_site($field, $row) 
    {
        return $row->$field ? $row->$field->getName() : "&nbsp;";   
    }

    protected function td_purchasedate($field, $row) 
    {
    	return $row[$field] ? $row->$field->format('Y-m-d') : "&nbsp;";
    }

    protected function td_material($field, $row) 
    {
        if(is_null($row->$field))
        {
            return "&nbsp;";
        }
        else
        {
            $name = $row->$field->getName();
            $nameeng = $row->$field->getNameeng();
            $namelong = $name . "/" . $nameeng;
            return $namelong;
        }    
    }

    
}

?>
