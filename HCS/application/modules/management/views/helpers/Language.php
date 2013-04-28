<?php
/**
 * Grid helper for administrating the adverts
 * 07/02/2013, detang.liu@synrgicresearch.com
 */
class GridHelper_Language extends Grid_Helper_Abstract {
    protected function op__delete($field, $config, $row) {
        $lang = $row['locale'];
        if($lang == 'en_US') {
            //make sure that we at least keep one locale available;
            return $this->_view->translate('Reserved'); 
        }
        return false; //false--let grid continue render in the default way
    }
}