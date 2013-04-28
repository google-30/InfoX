<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class GridHelper_Media extends Grid_Helper_Abstract 
{
    protected function td_view($field, $row) 
    {
        return "<img src=" . \Synrgic\MediaRepository::getURI($row) . " width=100>";
    }
    
    protected function td_type($field, $row) 
    {
        return $row->getMediaType()->getType();
    }

}
