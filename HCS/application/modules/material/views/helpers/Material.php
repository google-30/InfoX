<?php

class GridHelper_Material extends Grid_Helper_Abstract 
{
    protected function td_supplier($field, $row) 
    {
        $this->_em = Zend_Registry::get('em');
        $this->_material = $this->_em->getRepository('Synrgic\Infox\Material');
        $supplier = $this->_em->getRepository('Synrgic\Infox\Supplier');
        //$data = $supplier->findOneBy(array("id"=>$row[]));
    	//return $row->$field;
        return var_dump($row->$field);
        //return '<a href="' . $row->$field . '" target="_blank"><button data-mini="true">下载</button></a>';
    }

    protected function td_update($field, $row) 
    {
    	return $row->$field->format('Y-m-d');
    }

    protected function td_content($field,$row)
    {
	    // We override content so that we actually put the
	    // raw html in place without stripping characters
	    return $row->$field;
    }
}

?>
