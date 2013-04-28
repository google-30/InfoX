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
 * Adverts board 
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 23/10/2012
 */

class Adverts_IndexController extends Zend_Controller_Action
{
    private $em;
    private $repo;
    public function init()
    {
        $this->em = Zend_Registry::get('em');        
        
        /*
        <iframe> cause tablet lots of issues
        we use <div> intead of <iframe>, thereof the advert board
        does not need its own layout any more. -- dtliu
        ----------------------------------------------------------
        //advert board is playing in iframe
        //so we need our own layout for javascript injection
        $device = strtolower(\Synrgic\DeviceRepository::getDeviceTypeString());
        $path = APPLICATION_PATH .  '/modules/default/layouts/scripts/' . $device;
        $this->_helper->layout->disableLayout();
        $this->_helper->layout->setLayoutPath($path);        
        $this->_helper->layout->setLayout('advertboard');
        */
        
        //set a flippy animation for adverts swapping
        $this->view->headScript()->appendFile('/common/js/jquery.flip.min.js');

        $this->view->headScript()->appendFile('/common/js/synrgic/adverts/ads.js');
        $this->view->headStyle()->appendStyle('/common/js/synrgic/adverts/ads.css');
        
        $this->repo = $this->em->getRepository('Synrgic\Adverts\Schedule');
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('updschedule', 'json');
        $ajaxContext->initContext();
    }

    public function indexAction()
    {
        $this->view->settings = Zend_Json::encode($this->_getSettings());
    }
    
    private function _getSettings() {
        $device = Zend_Registry::get('device-type');
        $settings = Zend_Registry::get('settings');
        try {
            if($device === 'tablet') {
                $boardw = $settings->Tablet->AdvertBoardWidth->Value;
                $boardh = $settings->Tablet->AdvertBoardHeight->Value;
                $w = $settings->Tablet->AdvertWidth->Value;
                $h = $settings->Tablet->AdvertHeight->Value;
                $n = intval($boardh/$h);
                return array(
                        'N'=>$n,
                        'BW'=>$boardw,
                        'BH'=>$boardh,
                        'W'=>$w,
                        'H'=>$h,
                        );
            }
            else if($device === 'phone') {
                $boardw = $settings->Phone->AdvertBoardWidth->Value;
                $boardh = $settings->Phone->AdvertBoardHeight->Value;
                $w = $settings->Phone->AdvertWidth->Value;
                $h = $settings->Phone->AdvertHeight->Value;
                $n = intval($boardh/$h);
                return array(
                        'N'=>$n,
                        'BW'=>$boardw,
                        'BH'=>$boardh,
                        'W'=>$w,
                        'H'=>$h,
                );
            }
            else {
                $boardw = $settings->Table->AdvertBoardWidth->Value;
                $boardh = $settings->Table->AdvertBoardHeight->Value;
                $w = $settings->Table->AdvertWidth->Value;
                $h = $settings->Table->AdvertHeight->Value;
                $n = intval($boardh/$h);
                return array(
                        'N'=>$n,
                        'BW'=>$boardw,
                        'BH'=>$boardh,
                        'W'=>$w,
                        'H'=>$h,
                );
            }
        }
        catch(Exception $e) {
            return array(
                    'N'=>3,
                    'BW'=>266,
                    'BH'=>800,
                    'W'=>266,
                    'H'=>266,
                );
        }
    }

    public function updscheduleAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $timestamp = $this->_getParam('timestamp');
        $now = new \DateTime('now');
        $schedule = $this->repo->createOrFindSchedule($now);
        
        $this->_helper->json($this->_filterSchedule($now, $timestamp, $schedule)); 
    }

    private function _filterSchedule($now, $timestamp, $schedule) {
        $sch = new stdClass();
        $sch->id = 0;
        $sch->timestamp = $timestamp;
        $sch->time = $this->_toSeconds($now);
        $sch->entries = array();
        if($schedule && !$schedule->getLocked() && $schedule->updatedTime > $timestamp) {
            // filter and map entries to json
            $entries = $sch->entries;
            foreach($this->repo->findActiveScheduleEntries($schedule->getId()) as $e) {
                if($now <= $e->getEndTime()) {
                    $entries[] = array(
                        'id' => $e->getId(),
                        'startTime' => $this->_toSeconds($e->getStartTime()),
                        'endTime' => $this->_toSeconds($e->getEndTime()),
                        'size' => $e->getSize(),
                        'duration' => $e->getDuration(),
                        'playMode' => $e->getPlayMode(),
                        'permanent' => $e->getPermanent(),
                        'clickUrl' => $e->getClickUrl(),
                        'img' => Synrgic_Models_Adverts_Util::getMediaUrlById($e->getMediaId()),
                        
                    );
                } 
            }
            $sch->id = $schedule->getId();
            $sch->timestamp = $schedule->getUpdatedTime();
            $sch->entries = $entries;
        }
        
        return $sch;
    }
    
    private function _toSeconds($datetime) {
        // x*60*60
        list($h, $m, $s) = explode(':', $datetime->format('H:i:s'));
        return $h*3600 + $m*60 + $s;
    }
}
