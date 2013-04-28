<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */
class GridHelper_Accounts extends Grid_Helper_Abstract 
{
    protected function td_disabled($field, $row) 
    {
	return $row[$field]?"X":"";
    }

    protected function op__delete($field,$config,$row)
    {
	$url = "/management/accounts/delete/id/" . $row->getId();
	return "<a href='' onclick='checkYesNo(\"$url\")'>" .  $this->_view->translate('Delete') . "</a>";
    }
	
}

?>
