<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/

namespace Synrgic;

use Doctrine\ORM\EntityRepository;

class ChargeModelRepository extends EntityRepository
{
    /**
     * Add a charge item
     */
    public function addChargeItem($post) {
        $item = new \Synrgic\ChargeItem();
        $model = $this->_em->getRepository('Synrgic\ChargeModel')->find($post['mid']);
        $item->setModel($model);
        $model->getChargeItems()->add($item);
        unset($post['mid']); 
        $item->fromArray($post);
        $this->_em->persist($item);
        $this->_em->flush();
        return $item;
    }

    /**
     * Update a charge item
     * @param unknown $post
     * @return Ambigous <object, NULL, unknown>
     */
    public function updateChargeItem($post) {
        $item = $this->_em->getRepository('Synrgic\ChargeItem')->find($post['id']);
        $item->fromArray($post);
        $this->_em->persist($item);
        $this->_em->flush();
        return $item;
    }

    /**
     * Delete a charge item
     * @param unknown $id
     * @return Ambigous <object, NULL, unknown>
     */
    public function deleteChargeItem($id) {
        $item = $this->_em->getReference('Synrgic\ChargeItem', $id);
        $this->_em->remove($item);
        $this->_em->persist($item);
        $this->_em->flush();
        return $item;
    }
        
    public function calculateCharge(ChargeModel $model, array $data) {
        $alg = "calculateCharge_".$model->algorithm;
        if(method_exists($this, $alg)) {
            return call_user_func_array(array($this, $method), array($model, $data));
        }
        throw new Exception("The charge model: ".$model->name." not supported");
    }
    
    /**
     * Calculate the total charges according to the 'AccumulateByItems' algorithm
     * This algorithm accumulates the charges for each matched item
     * @param ChargeModel $model
     * @param array $data 
     * @return the total charges
     */
    private function calculateCharge_AccumulateByItems(ChargeModel $model, array $data) {
       // $data = array('Block'=>0.5,'Minute'=>0.3)
       AssertAlgorithmMatched($model, 'AccumulateByItems');
       $amount = 0.0;
       foreach($model->getChargeItems() as $item) {
           if(isset($data[$item->name])) {
               $amount += $data[$item->name] * $item->price/$item->units;
           }
       }
       return $amount;
    }   
}

