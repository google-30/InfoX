<?php

class GridHelper_Matapps extends Grid_Helper_Abstract 
{
    protected function td_amount($field, $row) 
    {
    	return '<input type="number" id="amount' . $row['id'] . '" value="'. $row[$field] . '" data-mini="true">';
    }    
    
    protected function td_remark($field, $row) 
    {
    	//return '<input type="text" id="remark' . $row['id'] . '" value="'. $row[$field] . '" data-mini="true">';
    	return '<textarea name="remark" id="remark' . $row['id'] . '" placeholder="填写补充说明">'. $row[$field] .'</textarea>';
    }    

    protected function td_supplier($field, $row) 
    {
        $em = Zend_Registry::get('em');
        $suppliers = $em->getRepository('Synrgic\Infox\Supplier')->findAll();    
        $cursupplier = $row[$field];
        $cursupplierid = $cursupplier ? $cursupplier->getId() : 0;

        $options = "";
        foreach($suppliers as $tmp)
        {
            $id = $tmp->getId();
            $name = $tmp->getName();
            if($cursupplierid == $id)
            {
                $options .= "<option value=$id selected>$name</option>";
            }
            else
            {
                $options .= "<option value=$id>$name</option>";
            }
        }        
        $selects = '<select id="select' . $row['id'] . '" data-mini="true">' . $options . "</select>";
    	return $selects;
    }

    protected function td_price($field, $row) 
    {
    	return '<input type="text" id="price' . $row['id'] . '" value="'. $row[$field] . '" data-mini="true" placeholder="0">';
    }  
          
    protected function td_update($field, $row) 
    {
    	return '<button onclick="updaterow(' . $row['id'] . ')" data-inline="true" data-mini="true">更新</button>';
    }           
}

?>
