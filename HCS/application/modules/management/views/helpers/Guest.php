<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class GridHelper_Guest extends Grid_Helper_Abstract 
{
    protected function td_name($field, $row) 
    {
        return '<a href="'. $this->_view->url(array('action'=>'edit', 'id'=>$row['id'], '_msg'=>null)).'">'
               . $row[$field].'</a>';    
    }
    
    protected function td_room($field, $row) 
    {
	$output = "";
	foreach($row->getRooms() as $room){
	     $output.=$room->getName();

	}
	return $output;
    }

    protected function op__hotel($field, $config, $row)
    {
	if( $config['url']['action'] == 'checkout') {

	    // Check if the guest is actually checked in first
	    if( !count($row->getRooms())){
	    	return "";
	    }
	}
    }
}
