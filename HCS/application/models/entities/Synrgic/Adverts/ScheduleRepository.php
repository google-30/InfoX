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
 * ScheduleRepository Repository 
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 22/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\ORM\EntityRepository;

class ScheduleRepository extends EntityRepository 
{
    public function createOrFindSchedule($date) {
        $schedule = $this->findSchedule($date); // find again
        if(!$schedule) {
            // quickly create one and locked it &
            // make sure others can see if the schedule is locked.
            // difficult to handle the asychnonized web requests
            // ???
            $schedule = new \Synrgic\Adverts\Schedule();
            $schedule->setLocked(true);
            $this->_em->persist($schedule);
            $this->_em->flush();

            $adverts = $this->_em->getRepository('Synrgic\Adverts\Adverts')->findEffectiveAdverts($date);

            try {
                foreach($adverts as $ad) {
                    $entry = $this->_createScheduleEntry($ad);
                    $entry->schedule = $schedule;
                    $schedule->getScheduleEntries()->add($entry);
                } 
                $schedule->setLocked(false);
                $this->_em->persist($schedule);
                $this->_em->flush();
            }
            catch(\Exception $e) {
                // something wrong, remove this schedule
                $this->_em->remove($schedule);
                $this->_em->flush();
                $schedule = false;
            }
        }

        return $schedule;
    }

    public function findSchedule($date) {
        $qb = $this->_em->createQueryBuilder();
        /*
        // D2's YEAR, MONTH, DAY not supported for postgres
        // need write d2 parse interface
        $qb->select('s')
           ->from('Synrgic\Adverts\Schedule', 's')
           ->where('YEAR(s.createdTime) = :year')
           ->andWhere('MONTH(s.createdTime) = :month')
           ->andWhere('DAY(s.createdTime) = :day');
        $qb->setParameter('year', $date->format('Y'))
           ->setParameter('month', $date->format('m'))
           ->setParameter('day', $date->format('d'));
        */
        $date = clone $date;
        $next = clone $date;
        $date->setTime(0, 0, 0);
        $next->add(new \DateInterval('P1D'));
        $qb->select('s')
           ->from('Synrgic\Adverts\Schedule', 's')
           ->where('s.createdTime >= :date')
           ->andWhere('s.createdTime < :next')
           ->setParameter('date', $date)
           ->setParameter('next', $next);
        $result = $qb->getQuery()
                     ->useQueryCache(true)
                     ->useResultCache(true, 3600, "schedule_find_schedule")
                     ->getResult();

        return !empty($result)?$result[0]:false; 
    }

    /**
     * Update schedule when an adverts is added/deleted/edited 
     * @param $op: add, delete, edit
     */
    public function updateSchedule($adverts, $op) {
        $schedule = $this->findSchedule(new \DateTime('now'));
        if($schedule && $this->_isActiveAdverts($adverts)) {
            $this->_updateSchedule($schedule, $adverts, $op);
        }
        return $schedule;
    }
    private function _updateSchedule($schedule, $adverts, $op) {
        $schedule->setUpdatedTime(time());
        if($op == 'add') {
            $entry = $this->_createScheduleEntry($adverts);
            $schedule->getScheduleEntries()->add($entry);
            $entry->schedule = $schedule;
            $entry->createdTime = $schedule->updatedTime;
            $this->_em->persist($schedule);
            $this->_em->flush();
        }
        else {
            $found = $this->_findScheduleEntry($schedule, $adverts);
            if($found) {
                $found->setStoppedTime(new \DateTime('now'));
            }
            if($op == 'edit') {
                $found = $this->_createScheduleEntry($adverts);
                $found->schedule = $schedule;
                $found->createdTime = $schedule->updatedTime;
                $schedule->getScheduleEntries()->add($found);
            }
            $this->_em->persist($schedule);
            $this->_em->flush();
        }
    }
    
    public function findActiveScheduleEntries($schid) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('se')
           ->from('Synrgic\Adverts\ScheduleEntry', 'se')
           ->innerJoin('se.schedule', 's')
           ->where('s.id = :id')
           ->andWhere('se.stoppedTime is NULL')
           ->andWhere('se.housekeptTime is NULL')
           ->addOrderBy('se.createdTime','DESC')
           ->addOrderBy('se.endTime', 'ASC')
           ->setParameter('id', $schid)
           ->distinct(true);
        return $qb->getQuery()
                  ->useQueryCache(true)
                  ->getResult();
    }
    
    private function _createScheduleEntry($adverts) {
        $entry = new \Synrgic\Adverts\ScheduleEntry();

        /*
         * 1. copy the ads info into the schedule entry
         *    since each ads may be changed at anytime
         * 2. the schedule entry may become the source of
         *    computing the ads charging (since an ads
         *    itself may be modified and so uncertain) 
         * 3. drop the reference to ads so that the 
         *    schedule entry cannot be removed when
         *    doing ads' housekeeping
         */
        $entry->startTime = $adverts->getStartTime();
        $entry->endTime = $adverts->getEndTime();
        $entry->size = $adverts->getSize();
        $entry->duration = $adverts->getDuration();
        $entry->playMode = $adverts->getPlayMode();
        $entry->clickUrl = $adverts->getClickUrl();
        $entry->permanent = $adverts->getPermanent();
        $entry->mediaId = $adverts->getMedia()->getId();
        $entry->advertiserId = $adverts->getAdvertiser()->getId();
        $entry->createdTime = time();
        
        return $entry;
    }

    private function _findScheduleEntry($schedule, $adverts) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('se')
           ->distinct(true)
           ->from('Synrgic\Adverts\ScheduleEntry', 'se')
           ->innerJoin('se.schedule', 's')
           ->where('s.id = ?1')
           ->andWhere('se.stoppedTime is NULL')
           ->andWhere('se.housekeptTime is NULL')
           ->andWhere('se.mediaId = ?2')
           ->andWhere('se.advertiserId = ?3')
           ->setParameter(1, $schedule->getId())
           ->setParameter(2, $adverts->getMedia()->getId())
           ->setparameter(3, $adverts->getAdvertiser()->getId());
        $result = $qb->getQuery()
                     ->useQueryCache(true)
                     ->getResult();
        return $result?$result[0]:false;
    }

    private function _isActiveAdverts($adverts) {
        $now = new \DateTime();
        $today = clone $now;
        $today->setTime(0,0,0);
        return $now <= $adverts->getEndTime() && $today <= $adverts->getEndDate();
    }
}

