<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\Service;

use Doctrine\ORM\EntityRepository;
class CatalogRepository extends EntityRepository 
{
    public function find($fid) 
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
           ->from('Synrgic\Service\Catalog', 'b')
           ->where($qb->expr()->andX(
                   $qb->expr()->eq('b.fid', '?1'),
                   $qb->expr()->eq('b.is_display', '1')
                   ))
//           ->add('where', $qb->expr()->eq('b.fid', '?1'))
           ->orderBy('b.id')
           ->setParameter(1, $fid);
        return $qb->getQuery()->getResult();
    }
    public function delete($fid) 
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->delete('Synrgic\Service\Catalog', 'b')
           ->add('where', $qb->expr()->eq('b.fid', '?1'))
           ->setParameter(1, $fid);
        return $qb->getQuery()->getResult();
    }
    public function init($fid) 
    {
        $category=new \Synrgic\Service\Catalog();
        $category->setfid($fid);
        $category->setName('category');
        $category->setIs_display(1);
        $this->_em->persist($category);
        $this->_em->flush();
    }
    public function getroot() 
    {
        $category=$this->find(1);
        return $category;
    }
    public function getTree($id) 
    {
        $qb = $this->_em->createQueryBuilder();
        $list = array();
        $item=$this->_em->getReference('Synrgic\Service\Catalog', $id);
        while(!empty($item)&&$item->getId()>0){
            $list[]=$item;
            $item=$this->_em->getReference('Synrgic\Service\Catalog', $item->getFid());
        }
         
        return $list;
    }

    
    public function getTopCatalogs()
    {
    	$qb = $this->_em->createQueryBuilder();
    	$qb->select('b')
    	->from('Synrgic\Service\Catalog', 'b')
    	->where($qb->expr()->andX(
    			$qb->expr()->eq('b.fid', '?1'),
    			$qb->expr()->eq('b.is_display', '1')
    	))
    	->orderBy('b.id')
    	->setParameter(1, '-1');
    	return $qb->getQuery()->getResult();
    }

    public function getAllTopCatalogs()
    {
        return $this->findBy(array('fid'=>\Synrgic\Service\Catalog::TOP_CATALOG));
    }
    
    public function getSubTree($id)
    {
    	$qb = $this->_em->createQueryBuilder();

    	$list=array();
    	
    	$sql = "SELECT l  FROM \Synrgic\Service\Catalog l WHERE l.id=$id";
    	$list=$this->_em->createQuery($sql)->getResult ();
    	
    	for($i=0,$oldCount=0;$i<($oldCount+1);$i++)
    	{
    		$fid=$list[$i]->getid();
    		$sql = "SELECT l  FROM \Synrgic\Service\Catalog l WHERE l.fid=$fid";
    		$list1=$this->_em->createQuery($sql)->getResult ();
    	
    		for($j=0;$j<count($list1);$j++)
    		{
    			$list[$oldCount+$j+1]=$list1[$j];
    		}
    		$oldCount+=count($list1);
    	}

    	return $list;
    }
    
    public function pageData($curPage,$limit) 
    {
    	$offset=$curPage*$limit;
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
           ->from('Synrgic\Service\Catalog', 'b')
           ->orderBy('b.id')
	   ->setFirstResult($offset)
	   ->setMaxResults($limit);
        return $qb->getQuery()->getResult();
    }
    public function count()
    {
    	$sql = "SELECT count(l.id)  FROM \Synrgic\Service\Catalog l";
    	$count=$this->_em->createQuery($sql)->getResult ();
    	return $count[0][1];   	
    }  
}
