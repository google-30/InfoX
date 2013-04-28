<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class GridHelper_Services extends Grid_Helper_Abstract 
{
    private $language;

    public function init()
    {
        $session = Zend_Registry::get(SYNRGIC_SESSION);
        //$this->language = $session->language->getLocale();
        $this->language = "en_US";
    }

    protected function td_is_sale($field,$row)
    {
        return $row->$field ? 'Published' : 'Draft';
    }

    protected function td_category($field,$row)
    {
        return $row->$field->getTranslateName($this->language);
    }
}
