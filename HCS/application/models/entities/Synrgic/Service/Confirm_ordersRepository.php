<?php

namespace Synrgic\Service;

use Doctrine\ORM\EntityRepository;

class Confirm_ordersRepository extends EntityRepository 
{
    public function pageView()
    {
		$qb = $this->_em->createQueryBuilder();
		$qb->select('b')
			->from('Synrgic\Service\Confirm_orders', 'b')
			->add('where', $qb->expr()->neq('b.is_deleted', '?1'))
			->setParameter(1, '1')
			->orderBy("b.id",'ASC');	
		return $qb->getQuery()->getResult();
    }
    
    public function count()
    {
    	$sql = "SELECT count(l.id)  FROM \Synrgic\Service\Confirm_orders l";
    	$count=$this->_em->createQuery($sql)->getResult ();
    	return $count[0][1];
    }
    
    public function removeOrdersOfRoom($roomId)
    {
    	$qb = $this->_em->createQueryBuilder();
    	$qb->select('b')
    	->from('Synrgic\Service\Confirm_orders', 'b')
    	->add('where', $qb->expr()->eq('b.occupiedroom_id', '?1'))
    	->setParameter(1, $roomId)
    	->orderBy("b.id",'ASC');
    	return $qb->getQuery()->getResult();
    	
    }    
    
	
	public function deleteConfirm_orders($id)
	{
		$confirm_orders=$this->find($id);		
		if(isset($confirm_orders)){			
			$this->_em->remove($confirm_orders);
			$this->_em->flush();
			return true;
		}
		return false;
	}
	public function deleteDetail_ordersByOwnerId($id){
		$confirm_orders=$this->find($id);
		if(isset($confirm_orders)){
			$detail_orders=$confirm_orders->getDetail_orders();
			foreach($detail_orders as $v){
				$this->_em->remove($v);
			}
			$this->_em->flush();
			return true;
		}
		return false;
	}
}
