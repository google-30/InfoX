<?php

class GridHelper_Archive extends Grid_Helper_Abstract 
{
    protected function td_path($field, $row) 
    {
    	//return $row->$field;
        return '<a href="' . $row->$field . '" target="_blank"><button data-mini="true">下载</button></a>';
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
