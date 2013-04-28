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
 * A translation management
 *
 * @author liu.dt, Feb 25, 2013
 *
 * Modification history:
 *     add refreshCache when new/old translation changed & uploaded
 */
class Synrgic_Models_Translate {
    private static $_helper;
    private $_frontend_options;
    private $_backend_options;
    
    private $_translate = null;    
    private $_clear_cache = false;
    private $_cache_disable = false;
    private $_dir = '.';
    private $_lang = 'en';
    private $_suffix = 'csv';
    private $_options = array();
    
    private function __construct() {
        $this->_dir = APPLICATION_PATH.'/data/languages';        
    }
    
    public static function getInstance(array $options=array(), array $frontend_options = array(), array $backend_options = array()) {
        if(!isset(self::$_helper)) {
            $helper = new self();
            $helper->setFrontendOptions($frontend_options);
            $helper->setBackendOptions($backend_options);
            $helper->_options = $options;
            self::$_helper = $helper;
        }
        return self::$_helper;
    }
    
    public function getTranslate(array $options = array()) {
        $this->_options = array_merge($this->_options, $options);
        return $this->_getTranslate($this->_options);
    }
    
    public function setFrontendOptions(array $options = array()) {
        $this->_frontend_options = $options;
    }
    
    public function getFrontendOptions() {
        if(empty($this->_frontend_options)) {
            $this->_frontend_options = array(
                'lifetime' => 60 * 60 * 24 * 7,  // 1 weeks
            	'automatic_serialization' => true
            );
        }
        return $this->_frontend_options;
    }
    
    public function setBackendOptions(array $options = array()) {
        $this->_backend_options = $options;
    }
    
    public function getBackendOptions() {
        if(empty($this->_backend_options)) {
            $this->_backend_options = array(
                'cache_dir' => APPLICATION_PATH . '/data/cache',
                'file_name_prefix' => 'lang',
                'hashed_directory_level' => 2,
            );
        }
        return $this->_backend_options;
    }
    
    public function refreshCache($locale) {
        if($this->_cache_disable === false) {
            $cache = $this->_getCache();
            list($lang,) = explode('_', $locale, 2);
            if($lang === $locale) {
                $cache->remove($lang);
            }
            else {
                $cache->remove($lang);
                $cache->remove($locale);
            }
        }
    }
    
    public function addTranslation($file, $lang) {
        $pathinfo = pathinfo($file);
        if(strtolower($pathinfo['extension']) === $this->_suffix) {
            if(empty($pathinfo['dirname'])) {
                $file = APPLICATION_PATH.'/data/languages/'.$file;
            }
            if(file_exists($file)) {
                $filename = $pathinfo['filename'];
                if($this->_checkLangSpec($filename, $lang)) {
                    $this->_translate->addTranslation($file, $lang);
                }
            }
        }
    }
    private function _checkLangSpec($filename, $locale) {
        list($name, $lang) = explode('_', $filename, 2);
        if(!isset($lang)) {
            return $locale === 'en';
        }
        else {
            return $lang === $locale || strpos($lang, $locale, 0) === 0;
        }
    }
        
    private function _getTranslate($options) {
        if(!empty($options)) {
            if(isset($options['lang_dir'])) {
                $this->_dir = $options['lang_dir'];
                //unset($options['lang_dir']);
            }
            if(isset($options['lang'])) {
                $this->_lang = $options['lang'];
                //unset($options['lang']);
            }
            if(isset($options['locale'])) {
                //sometime ones use locale, 'locale' has priority to 'lang'
                $this->_lang = $options['locale'];
            }
            if(isset($options['clear_cache'])) {
               $this->_clear_cache = (bool)$options['clear_cache'];
               //unset($options['clear_cache']); 
            }
            
            if(isset($options['cache_disable'])) {
                $this->_cache_disable =  (bool)$options['cache_disable'];
                //unset($options['cache_disable']);
            }
            if(isset($options['adapter']) && !empty($options['adapter'])) {
                $this->_suffix = strtolower($options['adapter']);
                //unset($options['adapter']);                
            }
        }
        
        if($this->_cache_disable === false) {
            $cache = $this->_getCache();
            $cachename = $this->_lang;
        
            if($this->_clear_cache === true) {
                $cache->remove($cachename);
            }
        
            if(!($this->_translate = $cache->load($cachename))) {
                $this->_addTranslations($this->_dir, $this->_lang);
                $cache->save($this->_translate, $cachename);
            }                                
        }
        else {
            $this->_addTranslations($this->_dir, $this->_lang);
        }
        return $this->_translate;
    }

    private function _getCache() {
        return Zend_Cache::factory('Core',
                                   'File',
                                   $this->getFrontendOptions(),
                                   $this->getBackendOptions());            
    }

    private function _addTranslations_x($dir) {
        //seems like zf1.12 filename scanning does not work?
        //waste much time to test. ??  
        $this->_translate = new Zend_Translate(
                array(
                    'adapter'=>$this->_suffix,
                    'content'=>$dir,
                    'locale'=>$this->_lang,  //'locale'=>'auto',
                    'disableNotices'=>true,  //XXX:do this what wrong, zf1.12?
                    'scan'=>Zend_Translate::LOCALE_FILENAME,
                    'ignore'=>'system',
                ));
        //make sure that 'system' level translation not been overwritten
        $system = 'system_'.$this->_lang.'.'.$this->_suffix;
        $this->addTranslation($system, $this->_lang);
    }
    
    private function _addTranslations($dir, $locale) {
        $this->_translate = new Zend_Translate(
                array('adapter'=>$this->_suffix, 
                      'locale'=>$locale,
                      'disableNotices'=>true));
        
        $files = $this->scanDirectory($dir, $locale);
        
        //system resource first
        if(isset($files['system']) && !empty($files['system'])) {
            foreach($files['system'] as $lang=>$list) {
                $this->_addTranslationResources($lang, $list);
            }
            unset($files['system']);
        }
        
        //language firstly, ie en prior to en_AU, en_US, en_BR etc
        list($lang,) = explode('_', $locale, 2);
        if(isset($files[$lang])) {
            $this->_addTranslationResources($lang, $files[$lang]);
            unset($files[$lang]);
        }
        
        // regions secondly,
        // give chances to overwrite all previous existing ones if same
        foreach($files as $lang=>$list) {
            $this->_addTranslationResources($lang, $list);
        }
    }
    
    private function _addTranslationResources($lang, $list) {
        if(!empty($list)) {
            foreach($list as $file) {
                $this->_translate->addTranslation($this->_dir.'/'.$file, $lang);
            } 
        }
    }
    
    /**
     * Scan translation resources according to the locale:
     *    filename = <name>_<lang>.csv | <name>_<lang>_<region>.csv
     * to make compitable with zend_translate filename scaning specification.
     * assume that <name> contains no '_' for simplification
     * 
     * @param unknown $dir
     * @param unknown $locale
     * @param unknown $files
     * @return Ambigous <multitype:, unknown, string>
     */                        
    public function scanDirectory($dir, $locale) {
        $files = array('system'=>array());
        list($lang,) = explode('_', $locale);
        if($handle = opendir($this->_dir)) {        
            while(false !== ($file = readdir($handle))) {
                if($file !== '.' && $file !== '..') {
                    $pathinfo = pathinfo($file);
                    if(strtolower($pathinfo['extension']) === 'csv') {
                        list($namepart, $langpart) = explode('_', $pathinfo['filename'], 2);
                        if($langpart === $lang) {
                            //the language part matches with the language of the locale
                            //we have to include it
                            if(!isset($files[$lang])) {
                                $files[$lang] = array();
                            }
                            if(stripos($namepart, 'system', 0) === 0) {
                                if(!isset($files['system'][$lang])) {
                                    $files['system'][$lang] = array();
                                }
                                $files['system'][$lang][] = $file;
                            }
                            else {
                                $files[$lang][] = $file;
                            }
                        }
                        else if($langpart === $locale || strpos($langpart, $locale,0) === 0) {
                            //the language part matches with the whole locale or part of the locale
                            if(!isset($files[$langpart])) {
                                $files[$langpart] = array();
                            }
                            if(stripos($namepart, 'system', 0) === 0) {
                                if(!isset($files['system'][$langpart])) {
                                    $files['system'][$langpart] = array();
                                }
                                $files['system'][$langpart][] = $file;
                            }
                            else {
                                $files[$langpart][] = $file;
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $files;
    }
}