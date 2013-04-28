<?php

namespace Synrgic\Service;

use Doctrine\ORM\EntityRepository;

class Operate_recordRepository extends EntityRepository
{
    public function addOperationLog($operator_id, $event)
    {
    	$oplog = new \Synrgic\Service\Operate_record();
        $oplog->setTime(new \Datetime('now'));
        $oplog->setUser_id($operator_id);
        $oplog->setEvent($event);
        $this->_em->persist($oplog);
        $this->_em->flush();
    }

    public function pageView($start, $limit)
    {
		$qb = $this->_em->createQueryBuilder();
		$qb->select('b')
			->from('Synrgic\Service\Operate_record', 'b')
			->setFirstResult($start)
			->setMaxResults($limit)
			->orderBy('b.id');
		return $qb->getQuery()->getResult();
    }
}
