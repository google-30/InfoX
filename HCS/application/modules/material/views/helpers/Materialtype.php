<?php

class GridHelper_Materialtype extends Grid_Helper_Abstract 
{
    protected function td_main($field, $row) 
    {
        if(is_null($row->$field))
        {
            return "&nbsp;";
        }
        else
        {
            $typechs = $row->$field->getTypechs();
            $typeeng = $row->$field->getTypeeng();

            if($typechs == "" || $typeeng == "")
            {
                $type = $typechs . $typeeng;  
            }
            else
            {
                $type = $typechs ."/" . $typeeng;
            }
            return $type;
        }            
    }

    protected function td_typechs($field, $row) 
    {
        if(is_null($row->$field))
        {
            return "&nbsp;";
        }
        else
        {
            $typechs = $row["typechs"];
            $typeeng = $row["typeeng"];
            if($typechs == "" || $typeeng == "")
            {
                $type = $typechs . $typeeng;  
            }
            else
            {
                $type = $typechs ."/" . $typeeng;
            }
            return $type;
        }            
    }


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

    protected function td_price1($field, $row) 
    {
        $price = '<div style="float: left;"><input type="text" id="price' . $row['id'] . '" value="'. '" data-mini="true" placeholder="0" style="width:60px" ></div>';
        $updatebtn = '<div style="float: right;"><button onclick="updaterow(' . $row['id'] . ')" data-mini="true">更新</button></div>';
        $html = "<div>". $price . $updatebtn . "</div>";
        return $html;
    }  

    protected function td_update($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }
}

?>
