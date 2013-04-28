<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class GridHelper_Page extends Grid_Helper_Abstract 
{
    protected function td_user($field, $row) 
    {
        return $row->getEditor()->getName();
    }
    
    protected function td_revisions($field, $row) 
    {
        $em = Zend_Registry::get('em');
        $pageRepo = $em->getRepository('\Synrgic\CMS\Page');
        return count($pageRepo->getPastRevisions($row));
    }
}
