<?php
/**
 * Grid helper for administrating the adverts
 * 07/02/2013, detang.liu@synrgicresearch.com
 */
class GridHelper_Translation extends Grid_Helper_Abstract {
    private $_lang = null;
    protected function td_lang($field, $row) {
        if($this->_lang !== $row['lang']) {
            $this->_lang = $row['lang'];
            return $this->_lang;
        }
        else {
            return '&nbsp;';
        }
    }
}