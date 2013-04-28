<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/*-
 * AdvertsConfigEntry Repository 
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 12/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\ORM\EntityRepository;

class AdvertsConfigEntryRepository extends EntityRepository 
{
    /**
     * Find config entries by level 
     */
    public function findConfigEntriesByPerm($level = 'all') 
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('e')
           ->from('Synrgic\Adverts\AdvertsConfigEntry', 'e');
        if(!empty($level) && $level != 'all') {
           $qb->add('where', $qb->expr()->eq('e.permLevel', '?1'))
              ->setParameter(1, $level);
        }
        $qb->orderBy('e.name');
        return $qb->getQuery()->getResult();
    }

    public function updateConfigEntry($name, $value) 
    {
        $ce = $this->findOneBy(array('name'=>$name));
        $ce->setValue($value);
        $this->_em->persist($ce);
        $this->_em->flush();
    }

}

