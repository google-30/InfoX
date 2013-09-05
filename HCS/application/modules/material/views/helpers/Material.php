<?php

class GridHelper_Material extends Grid_Helper_Abstract 
{
    protected function td_supplier($field, $row) 
    {
        return $row->$field ? $row->$field->getName() : "&nbsp;";   
    }

    protected function td_supplier1($field, $row) 
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
        $selects = '<select id="select' . $row['id'] . '" data-mini="true" class="suppliersel">' . $options . "</select>";

        $html = $selects;
    	return $html;
    }

    protected function td_price($field, $row) 
    {
        $supplier = $row['supplier'];
        //return $supplier ? "yyy" : "xxx";        
        $id = $row['id'];

        $em = Zend_Registry::get('em');
        $supplyprices = $em->getRepository('Synrgic\Infox\Supplyprice');
        $supplypriceobj = $supplyprices->findOneBy(array("material"=>$row, "supplier"=>$supplier));
        $price = $supplypriceobj ? $supplypriceobj->getPrice() : "&nbsp;";

        return $price;        
    }  

    protected function td_update($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }

    protected function td_type($field, $row) 
    {
        if(is_null($row->$field))
        {
            return "&nbsp;";
        }
        else
        {
            $main = $row->$field->getMain();
            $maintypechs = $main->getTypechs();
            $typechs = $row->$field->getTypechs();
            return $maintypechs . "::" . $typechs;
        }    
    }

    protected function td_dodate($field, $row) 
    {        
    	return ($row->$field) ? $row->$field->format('Y-m-d') : "&nbsp;";
    }

}

?>
