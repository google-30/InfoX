<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */
class GridHelper_Information extends Grid_Helper_Abstract 
{
    protected function td_created($field, $row) 
    {
	return $row->$field->format('Y-m-d H:i:s');
    }

    protected function td_content($field,$row)
    {
	// We override content so that we actually put the
	// raw html in place without stripping characters
	return $row->$field;
    }
}

?>
