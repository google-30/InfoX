<?php

namespace Synrgic\Service;

use Doctrine\ORM\EntityRepository;

class Detail_ordersRepository extends EntityRepository 
{
    public function getConfOrderPage($confid, $start, $limit) 
    {
		$qb = $this->_em->createQueryBuilder();
		$qb->select('b')
			->from('Synrgic\Service\Detail_orders', 'b')
			->add('where', $qb->expr()->eq('b.confirm_orders', '?1'))
			->setParameter(1, $confid)
			->setFirstResult($start)
			->setMaxResults($limit);
		return $qb->getQuery()->getResult();
    }
	
	public function addDetail_order($post){
		$detail_order=new \Synrgic\Service\Detail_orders();
		
		$confirm_orders=$this->_em->getRepository('Synrgic\Service\Confirm_orders')->find($post['cid']);
		$detail_order->setConfirm_orders($confirm_orders);
		$confirm_orders->getDetail_orders()->add($detail_order);
		$detail_order->fromArray($post);		
		$this->_em->persist($detail_order);
		$this->_em->flush();
		return $detail_order;
	}

	public function pageAll($curPage,$limit)
	{
		// just show an example, you should have a better query for this
		$offset=$curPage*$limit;
		$qb = $this->_em->createQueryBuilder();
		$qb->select('b')
		->from('Synrgic\Service\Detail_orders', 'b')
		->innerJoin('b.confirm_orders', 'c')
		->add('where', $qb->expr()->neq('b.is_deleted', '?1'))
		->setParameter(1, '1')
		->orderBy('b.id','ASC')
		->setFirstResult($offset)
		->setMaxResults($limit);
		return $qb->getQuery()->getResult();
	}	
	
	public function pageOfProvider($curPage,$limit,$providerId)
	{
		// just show an example, you should have a better query for this
		$offset=$curPage*$limit;
		$qb = $this->_em->createQueryBuilder();
		$qb->select('b')
		->from('Synrgic\Service\Detail_orders', 'b')
		->innerJoin('b.confirm_orders', 'c')
		->add('where', $qb->expr()->eq('b.provider_id', '?1'))
		->setParameter(1, $providerId)
		->andWhere($qb->expr()->neq('b.is_deleted', '?2'))
		->setParameter(2, '1')
		->orderBy('b.id','ASC')		
		->setFirstResult($offset)
		->setMaxResults($limit);
		return $qb->getQuery()->getResult();
	}

}
