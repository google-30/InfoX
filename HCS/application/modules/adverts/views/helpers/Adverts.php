<?php
/**
 * Grid helper for administrating the adverts
 * 04/12/2012, detang.liu@synrgicresearch.com
 */
class GridHelper_Adverts extends Grid_Helper_Abstract {
    protected function td__img($field, $row) {
        return '<a href="'.$this->_view->url(array('action'=>'edit-adverts', 'id'=>$row['id'])).'">'
               .'<img src="'.$row->_imageUrl.'" width="32" height="32"/>'
               .'</a>';
    }
    
    protected function td_permanent($field, $row) {
        return $row[$field]?'Yes':'No';
    }
    
    protected function td_startDate($field, $row) {
        return $row[$field]->format("Y-m-d");
    }
    
    protected function td_startTime($field, $row) {
        return $row[$field]->format("H:i:s");
    }
    
    protected function td_endDate($field, $row) {
        return $row[$field]->format("Y-m-d");
    }

    protected function td_endTime($field, $row) {
        return $row[$field]->format("H:i:s");
    }
    
    protected function td__charge($field, $row) {
        return Synrgic_Models_Adverts_Util::calculateCharge($row);
    }
}