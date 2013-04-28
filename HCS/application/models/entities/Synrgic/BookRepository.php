<?php

namespace Synrgic;

use Doctrine\ORM\EntityRepository;

class BookRepository extends EntityRepository 
{
    public function findBooksPriceGreaterThan($price) 
    {
        // just show an example, you should have a better query for this
        $qb = $this->_em->createQueryBuilder();
        $qb->select('b')
           ->from('Synrgic\Book', 'b')
           ->add('where', $qb->expr()->gt('b.price', '?1'))
           ->orderBy('b.subject')
           ->setParameter(1, $price);
        return $qb->getQuery()->getResult();
    }
}
