<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/

use Synrgic\ChargeModel;
/*-
 * A framework to calculate charges following the charge model definition
 *
 * History:
 *    add 'AccumulateByItems' and 'Fixed' algorithms - liudt; 14/02/2013
 */
class Synrgic_Models_Charge 
{
    private static $ALGORITHMS = null;
    private static $instance;
    
    private function __construct() {
    }
    
    private static function init() {
        // register algorithms here
        if(self::$ALGORITHMS == null) {
            $tr = \Zend_Registry::get('Zend_Translate');
            self::$ALGORITHMS = array(
                        'AccumulateByItems' => $tr->_('Accumulate by Charge Items'),
                        'Fixed' => $tr->_('Fixed Price'),
                        'Adverts'=>$tr->_('Ads Charge')
                    );
        }
    }
    
    public static function listAlgorithms() {
        if(self::$ALGORITHMS == null) {
            self::init();
        }
        return array_merge(array(),self::$ALGORITHMS);
    }
    
    // make sure that we have only one framework for charge
    public static function getInstance() {
        if(self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function calculateCharge(ChargeModel $model, array $data) {
        $alg = "calculateCharge_".$model->algorithm;
        if(method_exists($this, $alg)) {
            $amount = call_user_func_array(array($this, $alg), array($model, $data));
            return round($amount, 2);
        }
        throw new Exception("The charge model: ".$model->name." not supported");
    }
    
    /**
     * An algorithm is specially designed for adverts
     * @param ChargeModel $model
     * @param unknown $data
     */
    private function calculateCharge_Adverts(ChargeModel $model, $data) {
        $this->AssertAlgorithmMatched($model, 'Adverts');
        if(isset($data['blocks']) && isset($data['minutes'])) {
            foreach($model->getChargeItems() as $item) {
                if($item->name == 'minutes') {
                    return $data['blocks'] * ($data['minutes']/$item->units) * $item->price;
                }
            }
        }
        return 0.0;
    }
    /**
     * Calculate the total charges according to the 'AccumulateByItems' algorithm
     * This algorithm accumulates the charges for each matched item
     * @param ChargeModel $model
     * @param array $data 
     * @return the total charges
     */
    private function calculateCharge_AccumulateByItems(ChargeModel $model, array $data) {
       // $data = array('months'=>0.5,'days'=>0.3)
       $this->AssertAlgorithmMatched($model, 'AccumulateByItems');
       $amount = 0.0;
       foreach($model->getChargeItems() as $item) {
           if(isset($data[$item->name])) {
               $amount += $data[$item->name] * $item->price/$item->units;
           }
       }
       return $amount;
    }
    
    /**
     * Calculate the charges according to 'Fixed' algorithm. This algoirthm always
     * This algorithm calculates the charge according to the first matched item name
     * @param ChargeModel $model
     * @param array $data
     * @return number
     */
    private function calculateCharge_Fixed(ChargeModel $model, array $data) {
        // $data = array('<matched item name>'=>amount)
        $this->AssertAlgorithmMatched($model, 'Fixed');
        foreach($model->getChargeItems() as $item) {
            if(isset($data[$item->name])) {
                //use first matched item as the fixed charge
                return $item->price * $data[$item->name]/$item->units;
            }
        }
        return 0.0;
    }
    
    private function AssertAlgorithmMatched(ChargeModel $model, $alg) {
        if($model->algorithm !== $alg) {
            throw new Exception("The algorithm 'AccumulateByItems' not support the charge model: ".$model->name);
        }
    }
}
