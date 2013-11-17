<?php

class GridHelper_Workerdetails extends Grid_Helper_Abstract 
{
    protected function td_name($field, $row) 
    {
        $name = ($row["namechs"]!="") ? $row["namechs"] : $row["nameeng"];
        return $name;
        //return $row->$field ? $row->$field->format('Y/m/d') : "&nbsp;";
    }

    protected function getDate($field, $row) 
    {
        return $row->$field ? $row->$field->format('Y/m/d') : "&nbsp;";
    }

    protected function td_resignation($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_wpexpiry($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_ppexpiry($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_doa($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_dob($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_issuedate($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_securityexp($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_arrivaldate($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_medicaldate($field, $row)     
    {
        return $this->getDate($field, $row);
    }

    protected function td_csoc($field, $row)     
    {
        return $this->getDate($field, $row);
    }


}

?>
