<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\Service;
use Doctrine\ORM\EntityRepository;

class ServiceRepository extends EntityRepository
{

    public function findGoodsPriceGreaterThan ($price)
    {
        // just show an example, you should have a better query for this
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from('Synrgic\Service\Service', 'b')
            ->add('where', $qb->expr()
            ->gt('b.price', '?1'))
            ->orderBy('b.id')
            ->setParameter(1, $price);
        return $qb->getQuery()->getResult();
    }
    
    public function searchByServicesName ($key)
    {
    	// just show an example, you should have a better query for this
    	$qb = $this->_em->createQueryBuilder();
    	$qb->select('b')
    	->from('Synrgic\Service\Service', 'b')
    	->add('where', $qb->expr()->like('b.name', '?1'))
    	->orderBy('b.id')
    	->setParameter(1, '%' . $key . '%')
    	->andWhere($qb->expr()->neq('b.type', '?2'))
		->setParameter(2, '2');
    	return $qb->getQuery()->getResult();
    }
    
    public function searchByKey ($key)
    {
        // just show an example, you should have a better query for this
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from('Synrgic\Service\Service', 'b')
            ->add('where', $qb->expr()
            ->like('b.key_words', '?1'))
            ->orderBy('b.id')
            ->setParameter(1, '%' . $key . '%');
        return $qb->getQuery()->getResult();
    }
	
	public function searchByKeyAndCatalog ($key,$categoryId)
    {
        // just show an example, you should have a better query for this
		
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from('Synrgic\Service\Service', 'b')			
            ->add('where', $qb->expr()
            ->like('b.key_words', '?1'))			
            ->orderBy('b.id')			
            ->setParameter(1, '%' . $key . '%')
			->andWhere($qb->expr()->eq('b.type', '?2'))
			->setParameter(2, $categoryId)
			->andWhere($qb->expr()->eq('b.is_sale', '?3'))
			->setParameter(3, '1');							
        return $qb->getQuery()->getResult();
			
		//$sql = "SELECT b  FROM \Synrgic\Service\Service b WHERE b.id LIKE $key AND b.type=$categoryId";	
		//$this->_em->createQuery($sql)->getResult ();
    }

    public function searchByCatalog ($categoryId)
    {
        // just show an example, you should have a better query for this
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from('Synrgic\Service\Service', 'b')
            ->innerJoin('b.category', 'c')
            ->add('where', $qb->expr()->eq('c.id', '?1'))
			->setParameter(1, $categoryId)
            ->orderBy('b.id')
			->andWhere($qb->expr()->eq('b.is_sale', '?2'))
			->setParameter(2, '1');
        return $qb->getQuery()->getResult();
    }


    public function searchByCatalogTree($categoryId)
    {
        $allservices = array();
        $tree = $this->_em->getRepository('\Synrgic\Service\Catalog')->getSubTree($categoryId);
        foreach($tree as $leaf){
            $services = $this->searchByCatalog($leaf->getId());
            $allservices = array_merge($services,$allservices);
        }

        return $allservices;
    }

    public function searchFavoritesByCatalog ($categoryId)
    {
        // just show an example, you should have a better query for this
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from('Synrgic\Service\Service', 'b')
            ->add('where', $qb->expr()->eq('b.is_top', 1))
			->andWhere($qb->expr()->eq('b.is_sale', '?1'))
			->setParameter(1, '1')
            ->orderBy('b.id');
        return $qb->getQuery()->getResult();
    }

    public function searchTopGoods ()
    {
        // just show an example, you should have a better query for this
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from('Synrgic\Service\Service', 'b')
			->innerJoin('b.category', 'c')
            ->add('where', $qb->expr()->eq('b.is_top', '?1'))
			->setParameter(1, '1')
			->andWhere($qb->expr()->eq('b.is_sale', '?2'))
			->setParameter(2, '1')
            ->orderBy('b.id');
			
        return $qb->getQuery()->getResult();
    }   

    public function getSameTypeServices ($type)
    {
    	// just show an example, you should have a better query for this
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
            ->from('Synrgic\Service\Service', 'b')
            ->add('where', $qb->expr()->eq('b.type', '?1'))
            ->orderBy('b.id')
            ->setParameter(1, $type);
        return $qb->getQuery()->getResult();
    }
    
    public function page($curPage,$limit,$sortKey)
    {
    	// just show an example, you should have a better query for this
    	$offset=$curPage*$limit;
    	if($sortKey==null)
    		$sortKey='id';
    	$qb = $this->_em->createQueryBuilder();
    	$qb->select('b')
    	->from('Synrgic\Service\Service', 'b')
    	->orderBy("b.$sortKey",'ASC')
    	->setFirstResult($offset)
    	->setMaxResults($limit);
    	return $qb->getQuery()->getResult();
    }

    public function count()
    {
    	$sql = "SELECT count(l.id)  FROM \Synrgic\Service\Service l";
    	$page=$this->_em->createQuery($sql)->getResult ();
    	return $page[0][1];
    	 
    }
}
