<?php

class GridHelper_Application extends Grid_Helper_Abstract 
{
    protected function td_applicant($field, $row) 
    {
        $applicant = "&nbsp;";
        if($row[$field])
    	{
            $applicant = $row[$field]->getName();
        }
        return $applicant;
    }

    protected function td_site($field, $row) 
    {
        $content = "&nbsp;";
        if($row[$field])
    	{
            $content = $row[$field]->getName();
        }
        return $content;
    }
}

?>
