<?
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/** 
 * This class provides the form element for a Page 
 */ 
class Synrgic_Models_Form_Element_Page extends Zend_Form_Element
{
    public $helper="formPage";

    private $_pageHelper;

    public function init()
    {
        $this->_pageHelper= new Synrgic_Models_Page();
    }

    // Proxy all method calls through to the page helper
    // If hot handled by this class
    public function __call($method, $args) 
    {
        if(method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        } else {
            return call_user_func_array(array($this->_pageHelper, $method),$args);
        }
    }

    public function getPageHelper()
    {
        return $this->_pageHelper;
    }

    public function setValue($value)
    {
        if(is_array($value)){
            $this->_pageHelper->fromArray($value);
        } else if($value instanceof \Synrgic\CMS\Page){
            $this->_pageHelper->setPage($value);
        } else {
            throw new Exception('paramater is not a \Synrgic\CMS\Page');
        }
    }

    public function getValue()
    {
        return $this->_pageHelper->toArray();
    }
}

?>
