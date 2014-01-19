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
        $supplypriceRepo = $em->getRepository('Synrgic\Infox\Supplyprice');        
        $matRepo = $em->getRepository('Synrgic\Infox\Material');
                
        $materialid = $row["materialid"]; //$matappobj->getMaterialid();
        $materialobj = $matRepo->findOneBy(array('id'=>$materialid));
        $pricearr = $supplypriceRepo->findBy(array("material"=>$materialobj));
        
        $options="";
        foreach($pricearr as $tmp)
        {
            $id = $tmp->getId();
            $supplier = $tmp->getSupplier();
            $supname = $supplier->getName();
            $rate = $tmp->getRate();
            $unit = $tmp->getUnit();
            //$quantity = $tmp->getQuantity();
                        
            $value = $id;
            //$str = "$supname:$rate*$quantity($unit)=$amount"; 
            $appamount = $row["amount"];
            $amount = $rate * $appamount;
            $str = "$supname:$rate*$appamount($unit)=$amount"; 
            $option = "<option value=$value>$str</option>";
            $options.=$option;
        }
        $selects = '<select id="select' . $row['id'] . '" data-mini="true">' . $options . "</select>";
    	return $selects;                
    }

    protected function td_supplier0($field, $row) 
    {
        $em = Zend_Registry::get('em');
        $supplypriceRepo = $em->getRepository('Synrgic\Infox\Supplyprice');
        $matRepo = $em->getRepository('Synrgic\Infox\Material');
        $suppliers = $em->getRepository('Synrgic\Infox\Supplier')->findAll();
 
        $cursupplier = $row[$field];
        $cursupplierid = $cursupplier ? $cursupplier->getId() : 0;

        // material in sys or not
        $matid = $row["materialid"];    
        $matobj = $matRepo->findOneBy(array("id"=>$matid));            
        $options = "";
        if($matobj)
        {// in sys
            // current supplier is not set, query the default supplier of the material 
            $supplierobj = $matobj->getSupplier();
            $defsupplierid = $supplierobj ? $supplierobj->getId() : 0;
            $cursupplierid = $cursupplierid ? $cursupplierid : $defsupplierid;            

            foreach($suppliers as $tmp)
            {
                $suppriceobj = $supplypriceRepo->findOneBy(array("material"=>$matobj, "supplier"=>$tmp));
                $price = $suppriceobj ? $suppriceobj->getPrice() : 0;

                $id = $tmp->getId();
                $name = $tmp->getName();
                if($cursupplierid == $id)
                {
                    $options .= "<option value=$id selected>$name::$price</option>";
                }
                else
                {
                    $options .= "<option value=$id>$name::$price</option>";
                }
            }                    
        }   
        else
        {// not in sys
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
        
        $selects = '<select id="sitepart' . $row['id'] . '" data-mini="true">';
        $option0 = '<option value="无定义">无定义</option>';
        $selects .= $option0 . $options . "</select>";
        
        //return $partArr[0]; 
        return $selects;
    }
      
    protected function td_description($field, $row) 
    {
        $materialid = $row['materialid'];
        $em = Zend_Registry::get('em');
        $matobj = $em->getRepository('Synrgic\Infox\Material')->findOneBy(array("id"=>$materialid));   
        $return = $matobj ? $matobj->getDescription() : "&nbsp;";             
        return $return;    
    }      
}

?>
