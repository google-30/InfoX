<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class GridHelper_Device extends Grid_Helper_Abstract 
{
    protected function td_deviceType($field,$row)
    {
	return $row->getDeviceTypeAsString();
    }

    protected function td_room($field,$row)
    {
	return $row->getRoom()->getName();
    }

    protected function td_groups($field, $row) 
    {
	$output = "";
	foreach($row->getGroups() as $group){
	     $output.=$group->getName();

	}
	return $output;
    }
}
