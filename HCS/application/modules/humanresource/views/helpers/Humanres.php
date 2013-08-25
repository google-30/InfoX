<?php

class GridHelper_Humanres extends Grid_Helper_Abstract 
{
    protected function td_role($field, $row) 
    {
        return $row[$field] ? $row[$field]->getRolechs(): "&nbsp;";
    }    

    protected function td_date($field, $row) 
    {
        $data = $row[$field] ? $row[$field]->format("Y-m-d") : "&nbsp;";
        return $data;
    }
}

?>
