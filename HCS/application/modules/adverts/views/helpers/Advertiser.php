<?php

/**
 * Grid helper for administrating the advertisers
 * 04/12/2012, detang.liu@synrgicresearch.com
 */
class GridHelper_Advertiser extends Grid_Helper_Abstract {
    protected function td_name($field, $row) {
        return '<a href="'.$this->_view->url(array('action'=>'search-adverts', 'id'=>null, 'aid'=>$row['id'], '_msg'=>null)).'">'
               .$row[$field].'</a>';    
    }
    
    protected function td_createdTime($field, $row) {
        // convert Y
        return $row[$field]->format("Y-m-d H:i:s");
    }
    
    protected function td__cost($field, $row) {
        $em = Zend_Registry::get('em');
        $adverts = $em->getRepository('Synrgic\Adverts\Adverts')
                      ->findAdvertsByOwner($row['id']);
        $cost = 0;
        foreach($adverts as $ads) {
            $cost += Synrgic_Models_Adverts_Util::calculateCharge($ads);
        }
        return $cost;
    }
}