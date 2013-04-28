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
 * Adverts utilities
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 15/10/2012
 */

class Synrgic_Models_Adverts_Util {

    public static function getMediaUrl($media) 
    {
        $name = str_replace('adverts://', '', $media->getPath(), $count);
        if($count==1) {
            // filename-$id.$ext
            return sprintf('/ads/%s-%d.%s',$name, $media->getId(), $media->getMediaType()->getType());
        }
        else {
            // throw exception or return null, which is better?
            return null;
        }
    }
    
    public static function getMediaUrlById($id) {
        $em = Zend_Registry::get('em');
        return self::getMediaUrl($em->find('Synrgic\Media', $id));
    }

    public static function getMediaFilePath($media) 
    {
        $name = str_replace('adverts://', '', $media->getPath(), $count);
        if($count==1) {
            // filename-$id.$ext
            return sprintf(APPLICATION_PATH.'/data/uploads/adverts/%s-%d.%s',$name, $media->getId(), $media->getMediaType()->getType());
        }
        else {
            // throw exception or return null, which is better?
            return null;
        }
    }

    public static function getMediaPath($path, &$ext) 
    {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return 'adverts://' . rtrim(basename($path, $ext), '.');
    }

    public static function uploadMediaFile($media) 
    {
        $adapter = new Zend_File_Transfer_Adapter_Http();
        $target = APPLICATION_PATH.'/data/uploads/adverts';
        $adapter->setDestination($target);
        // Set a new destination path
        $adapter->addFilter('Rename', $target);
        // Set a new destination path and overwrites existing files
        $adapter->addFilter('Rename', array('target'=>$target, 'overwrite'=>true));
        if($adapter->isValid()) {
            foreach($adapter->getFileInfo() as $file=>$info) {
                if($adapter->isUploaded($file)) {
                    try {
                        $adapter->addFilter('Rename', Synrgic_Models_Adverts_Util::getMediaFilePath($media), $file);
                        $adapter->receive($file);
                    }
                    catch(Exception $e) {
                        //file exists
                    }
                }
            }
        }
    }
    
    public static function calculateCharge(Synrgic\Adverts\Adverts $ads) {
        $blocks = $ads->getSize();
        $sdate = $ads->getStartDate();
        $edate = $ads->getEndDate();
        $stime = $ads->getStartTime();
        $etime = $ads->getEndTime();
        
        $diffdate = $edate->diff($sdate, true);
        
        $difftime = $etime->diff($stime, true);
        $minutes = ($diffdate->days + 1) * (24*$difftime->h + $difftime->i);
        
        $data = array('blocks'=>$blocks, 'minutes'=>$minutes);
        return Synrgic_Models_Charge::getInstance()->calculateCharge($ads->getChargeModel(), $data);
    }
    
    public static function doHousekeeping(&$ads, $period) {
        $em = Zend_Registry::get('em');
        $deleted = $em->getRepository('Synrgic\Adverts\Adverts')->doHousekeeping($ads, $period);
        foreach($deleted as $path) {
            unlink($path);
        }
        return count($deleted);
    } 
    
    /**
     * Convert an array of arrays or a doctrine collection into the options of select input
     * @param $data an array of arrays or a doctrine collection
     * @param $valname the value associated with $valname into the value of select options
     * @param $textname the value associated with $textname into the text of select options
     * @param $inival the first option, not in the select options if null
     * @param $initext
     * @param $textgen a callback for dynamically generating display text
     *                    function gentext($valname, $textname, $item) 
     */
    public static function toMultiOptions($data, $valname, $textname, $inival = null, $initext = '',  $textgen = null)
    {
        $options = array();
        if($inival !== null) {
            // for init select item
            $options[$inival] = $initext;
        }
    
        if(!empty($data)) {
            if(is_array($data)) {
                if(!textgen) {
                    // mixed array: [{...}, {...},...{}]
                    foreach($data as $e) {
                        if(is_array($e) && in_array($valname, $e)) {
                            $options[$e[$valname]] = $e[$textname];
                        }
                    }
                }
                else {
                    foreach($data as $e) {
                        if(is_array($e) && in_array($valname, $e)) {
                            $options[$e[$valname]] = call_user_func_array($textgen, array($valname, $textname, $e));
                        }
                    }
                }
            }
            else if($data instanceof \Doctrine\Common\Collections\Collection) {
                if(!$textgen) {
                    foreach($data as $e) {
                        $options[$e->{$valname}] = $e->{$textname};
                    }
                }
                else {
                    foreach($data as $e) {
                        $options[$e->{$valname}] = call_user_func_array($textgen, array($valname, $textname, $e));
                    }
                }
            }
        }
        return $options;
    }
}

