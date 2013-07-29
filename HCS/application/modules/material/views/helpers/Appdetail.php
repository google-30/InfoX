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

    protected function td_unit($field, $row) 
    {
        $materialid = $row['materialid'];
        $em = Zend_Registry::get('em');
        $matobj = $em->getRepository('Synrgic\Infox\Material')->findOneBy(array("id"=>$materialid));   
        $unit = $matobj ? $matobj->getUnit() : "&nbsp;";             
        return $unit;     
    }           
    
    protected function td_spec($field, $row) 
    {
        $materialid = $row['materialid'];
        $em = Zend_Registry::get('em');
        $matobj = $em->getRepository('Synrgic\Infox\Material')->findOneBy(array("id"=>$materialid));   
        $spec = $matobj ? $matobj->getSpec() : "&nbsp;";             
        return $spec;    
    }      
}

?>
