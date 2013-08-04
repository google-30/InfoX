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

        $matRepo = $em->getRepository('Synrgic\Infox\Material');
        if(!$cursupplierid)
        {// current supplier is not set, query the default supplier of the material 
            $matid = $row["materialid"];    
            $matobj = $matRepo->findOneBy(array("id"=>$matid));
            if($matobj)
            {
            $supplierobj = $matobj->getSupplier();
            $defsupplierid = $supplierobj ? $supplierobj->getId() : 0;
            }
            $cursupplierid = $defsupplierid;

        }

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
    
    protected function td_sitepart($field, $row) 
    {
        $appobj = $row["application"];        
        $siteobj = $appobj->getSite();
        if(!$siteobj)
        {
            return "&nbsp;";
        }        

        $siteparts = $siteobj->getParts();
        $partArr = explode(";", $siteparts); 
        
        $currsitepart = $row["sitepart"];
        $options = "";
        foreach($partArr as $tmp)
        {
            $name = $tmp;
            if($name=="")
            {
                continue;
            }    
            
            if($currsitepart == $name)
            {
                $options .= "<option value=$name selected>$name</option>";
            }
            else
            {
                $options .= "<option value=$name>$name</option>";
            }
        }        
        //$selects = '<select id="select' . $row['id'] . '" data-mini="true">' . $options . "</select>";        
        
        $selects = '<select id="select' . $row['id'] . '" data-mini="true">';
        $option0 = '<option value="无定义">无定义</option>';
        $selects .= $option0 . $options . "</select>";
        
        //return $partArr[0]; 
        return $selects;
    }
           
}

?>
