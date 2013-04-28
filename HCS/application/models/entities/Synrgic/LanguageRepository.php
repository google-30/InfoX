<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic;

use Doctrine\ORM\EntityRepository;

class LanguageRepository extends EntityRepository 
{
    public function getLocaleToNameList()
    {
	$languages = $this->_em->getRepository('\Synrgic\Language')->findAll();
	$list = array();
	foreach($languages as $lang ) {
	    $code = $this->_ISO369($lang->getLocale());
	    if(\Zend_Locale::isLocale($code)) {
	        //some very ocient lang cannot get translated by zend_locale
    	    try {
    	        $desc = \Zend_Locale::getTranslation($code, 'language', $code);
        	    if(!empty($desc)) {
                    $list[$lang->getLocale()] = $desc;
        	    }
        	    else {
        	        $list[$lang->getLocale()] = $lang->getName();
        	    }
    	    }
    	    catch(Zend_Locale_Exception $e) {
    	        //zend cannot translate this lang to native
    	    }
	    }
    }
	// Failsafe incase there is no languages configured in the database
	if(empty($list))
	    $list[SYNRGIC_LOCALE_DEFAULT] = 'Default';

	return $list;
    }
    
    /*
     * Zend_Locale looks like using ISO369 (-1 or -2) standards
     */
    private static $ISO369 = array('zh_CN'=>'zh_Hans', 'zh_TW'=>'zh_Hant', 'zh_SG'=>'zh_Hans');
    private function _ISO369($locale) {
        if(isset(self::$ISO369[$locale])) {
            return self::$ISO369[$locale];
        }
        return $locale;
    }
}
