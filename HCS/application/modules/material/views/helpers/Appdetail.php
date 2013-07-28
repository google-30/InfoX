<?php

class GridHelper_Appdetail extends Grid_Helper_Abstract 
{
    protected function td_amount($field, $row) 
    {
        return $row[$field];
    	//return '<input type="number" id="amount' . $row['id'] . '" value="'. $row[$field] . '" data-mini="true">';
    }    
    
    protected function td_remark($field, $row) 
    {
        return  $row[$field];
    }    

    protected function td_supplier($field, $row) 
    {
        $supplier = $row[$field];
        $name = "&nbsp;";
        if($supplier)
        {
            $name = $supplier->getName();
        }
        return $name;
    }

    protected function td_price($field, $row) 
    {
        $price = $row[$field];
        if($price)
        {
            return $price;
        }
        else
        {
            return "未提供单价";
        }    	
    }  
          
    protected function td_sitepart($field, $row) 
    {
        return  $row[$field];
    }
           
}

?>
