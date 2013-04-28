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
 * Adverts Repository 
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 11/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\ORM\EntityRepository;
use \Synrgic\Media;

class AdvertsRepository extends EntityRepository 
{
    const CACHE_TIME = 3600; // 60*60
    const ALL_CACHE_ID = "adverts_*";
    /**
     * Find active adverts 
     */
    public function findActiveAdverts($date) 
    {
        $time = new \DateTime();
        $time->modify($date->format('H:i:s'));
        $date = clone $date;
        $date->setTime(0, 0, 0);
        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
           ->from('Synrgic\Adverts\Adverts', 'a')
           ->add('where', $qb->expr()->andX(
                   $qb->expr()->lte('a.startDate', '?1'),
                   $qb->expr()->gte('a.endDate', '?2'),
                   $qb->expr()->lte('a.startTime', '?3'),
                   $qb->expr()->gte('a.endTime', '?4'),
                   $qb->expr()->isNull('a.deletedTime')
                 ))
           ->orderBy('a.startTime')
           ->setParameter(1, $date)
           ->setParameter(2, $date)
           ->setParameter(3, $time)
           ->setParameter(4, $time);
           
        return $qb->getQuery()
                  ->useQueryCache(true)
                  ->useResultCache(true, self::CACHE_TIME, 'adverts_find_active_adverts')
                  ->getResult();
    }
    
    //fixbug when generating schedule.
    //note: logical difference between active adverts and effective ones below.
    //an effective adverts is not necessarily active but an active one must be effective
    //and an effective adverts may become active in the future !!!
    //
    //when first generating the schedule for a fixed date, all effective adverts should be fetched
    //into the schedule, otherwise some effective adverts (will go active later) may never get
    //to be played. --dtliu
    function findEffectiveAdverts($date) {
        $time = new \DateTime();
        $time->modify($date->format('H:i:s'));
        $date = clone $date;
        $date->setTime(0, 0, 0);
        
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
           ->from('Synrgic\Adverts\Adverts', 'a')
           ->add('where', $qb->expr()->andX(
                   $qb->expr()->lte('a.startDate', '?1'),
                   $qb->expr()->gte('a.endDate', '?2'),
                   $qb->expr()->gte('a.endTime', '?3'),
                   $qb->expr()->isNull('a.deletedTime')
                 ))
           ->orderBy('a.startTime')
           ->setParameter(1, $date)
           ->setParameter(2, $date)
           ->setParameter(3, $time);
        return $qb->getQuery()
                  ->useQueryCache(true)
                  ->useResultCache(true, self::CACHE_TIME, 'adverts_find_effective_adverts')
                  ->getResult();
    }

    public function findAdvertsByOwner($advertiserId) 
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
           ->from('Synrgic\Adverts\Adverts', 'a')
           ->innerJoin('a.advertiser', 'ad')
           ->leftJoin('a.media', 'm')
           ->where('ad.id = ?1')
           ->andWhere('a.deletedTime is NULL')
           ->orderBy('a.startDate', 'DESC')
           ->setParameter(1, $advertiserId);
        return $qb->getQuery()
                  ->useQueryCache(true)
                  ->useResultCache(true, self::CACHE_TIME, 'adverts_find_adverts_by_owner_'.$advertiserId)
                  ->getResult();
    }

    /**
     * Add an adverts
     */
    public function addAdverts($post, &$newfile) {
        $newfile = false;
        $adverts = new \Synrgic\Adverts\Adverts();
        $advertiser = $this->_em->getRepository('Synrgic\Adverts\Advertiser')->find($post['aid']);
        $adverts->setAdvertiser($advertiser);
        $advertiser->getAdverts()->add($adverts); 
        if(!empty($post['path'])) {
            unset($post['media_id']);
            $mediaRepo = $this->_em->getRepository('Synrgic\Media');
            $media = $mediaRepo->createMedia($post['path']);
            //$media->setAdvertiser($advertiser);
            $advertiser->getMedias()->add($media);
            $adverts->setMedia($media);
            //$media->getAdverts()->add($adverts);
            $newfile = true;
        }
        else if(isset($post['media_id']) && $post['media_id'] <> 0) {
            $media = $this->_em->getRepository('Synrgic\Media')->find($post['media_id']);
            $adverts->setMedia($media);
            //$media->getAdverts($adverts);
        }
        else {
           throw new \Exception('no media chosen, please upload a new media'); 
        }
        $adverts->fromArray($post);
        $adverts->chargeModel = $this->_em->getReference('Synrgic\ChargeModel', $post['charge_model_id']);
        $this->_em->persist($adverts);
        $this->_em->flush();
        $this->clearAdvertsCache();
        $this->clearCache('adverts_find_adverts_by_owner_'.$advertiser->getId());
        return $adverts;
    }

    public function updateAdverts($post, &$newfile) {
        $newfile = false;
        $adverts = $this->_em->getRepository('Synrgic\Adverts\Adverts')->find($post['id']);
        $advertiser = $adverts->getAdvertiser();
        if(!empty($post['path'])) {
            unset($post['media_id']);
            $mediaRepo = $this->_em->getRepository('Synrgic\Media');
            $media = $mediaRepo->createMedia($post['path']);
            //$media->setAdvertiser($advertiser);
            $advertiser->getMedias()->add($media);
            $adverts->setMedia($media);
            //$media->getAdverts()->add($adverts);
            $newfile = true;
        }
        else if(isset($post['media_id']) && $post['media_id'] <> $adverts->getMedia()->getId()) {
            $media = $adverts->getMedia();
            //$media->getAdverts()->remove($adverts);
            $media = $this->_em->getRepository('Synrgic\Media')->find($post['media_id']);
            $adverts->setMedia($media);
            //$media->getAdverts()->add($adverts);
        }
        $adverts->fromArray($post);
        $adverts->updatedTime = new \DateTime('now');
        $this->_em->persist($adverts);
        $this->_em->flush();
        $this->clearAdvertsCache();
        $this->clearCache('adverts_find_adverts_by_owner_'.$advertiser->getId());
        return $adverts;
    }

    public function deleteAdverts($id) {
        $adverts = $this->find($id);
        $adverts->setDeletedTime(new \DateTime('now'));
        $this->_em->persist($adverts);
        $this->_em->flush();
        $this->clearAdvertsCache();
        $this->clearCache('adverts_find_adverts_by_owner_'.$adverts->getAdvertiser()->getId());
        return $adverts;
    }
    
    public function findAttractions() {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('a')
           ->from('Synrgic\LocalAttractions\Attractiondata', 'a')
           ->groupBy('a.type');
        return $qb->getQuery()->getResult();
    }
    
    /**
     * f4.6 house keeping
     * Adverts which are no longer valid should expire from the system
     * after a 120 day period
     * 
     * return the array of paths of deleted media
     */
    public function doHousekeeping(&$ads, $period = 120) {
        $now = new \DateTime('now');
        $date = clone $now;
        if($period>1) {
            $date->sub(new \DateInterval('P'.($period-1).'D'));
        }
        $date->setTime(0, 0, 0); // clear time part
        
        //bulk delete adverts
        $q = $this->_em->createQuery('delete from Synrgic\Adverts\Adverts a where a.updatedTime < ?1');
        $q->setParameter(1, $date);
        $ads = $q->execute();

        //mark the schedule entries as housekept
        $q = $this->_em->createQuery('update Synrgic\Adverts\ScheduleEntry se set se.housekeptTime = ?1 where se.housekeptTime is NULL and se.createdTime < ?2');
        $q->setParameter(1, $now)
          ->setParameter(2, $date->getTimestamp());
        $q->execute();
        
        //delete obsolete medais
        $batchSize = 20;
        $i = 0;
        $q = $this->_em->createQuery('select m from Synrgic\Media m where m.createdTime < ?1');
        $q->setParameter(1, $date);
        $it = $q->iterate();
        $deleted = array();
        
        while(($row = $it->next()) !== false) {
            if($row[0]->getAdverts()->count() == 0) {
                $deleted[] = \Synrgic_Models_Adverts_Util::getMediaFilePath($row[0]);
                $row[0]->advertiser = null;
                $this->_em->remove($row[0]);
            }
            if(($i % $batchSize) == 0) {
                $this->_em->flush();
                $this->_em->clear();
            }
            $i++;    
        }
        $this->_em->flush();
        $this->clearCache(self::ALL_CACHE_ID);
        return $deleted;
    }
    
    private function clearAdvertsCache() {
        $this->clearCache(array(
                'adverts_find_active_adverts', 
                'adverts_find_effective_adverts'));
    }
    
    private function clearCache($cacheid) {
        $cachedriver = \Zend_Registry::get('cachedriver');
        if(isset($cachedriver)) {
            if(is_string($cache_id)) {
                $cachedriver->delete($cacheid);
            }
            else if(is_array($cacheid)) {
                foreach($cacheid as $id) {
                    $cachedriver->delete($id);
                }
            }
        }
    }
}
