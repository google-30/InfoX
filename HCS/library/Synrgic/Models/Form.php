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
 * A simple form to wrap Zend_Form
 *
 * @author liu.dt, Jun 30, 2010
 *
 * Modification history:
 * 1. drop the check of supported field types and use throw exception instead
 *    so that the misuse easier reported    -- liu detang, 16/10/2012
 */
class Synrgic_Models_Form extends Zend_Form {
    private $_id;
    private $_form_tpl;
    
    public function __construct($options = null) 
    {
        parent::__construct($options);
        $this->_initForm($options);
    }

    public function addTextField($name, $required=false, $options = array()) 
    {
        return $this->addField($name, 'Text', $required, $options);
    }
    
    public function addSelectField($name, $required=false, $options=array()) 
    {
        return $this->addField($name, 'Select', $required, $options);
    }

    public function addFileField($name, $required = false, $options = array()) {    
        $file = $this->createField($name, 'File', $required, $options);
        $file->setDecorators(array('File'))
             ->addValidator('FilesSize', false, array('max'=>'10MB'))
             ->addValidator('Count', false, 1)
             ->setValueDisabled(true)        
             ->setAttribs(array('size'=>60));
        if(isset($option['extension'])) {
             $file->addValidator('Extension', false, $options['extension']);   
        }
        else {
             $file->addValidator('Extension', false, 'png,jpg,gif');
        }
        return $this;
    }
    
    public function addField($name, $type = 'Text', $required = false, $options = array()) 
    {
        $this->createField($name, $type, $required, $options);
        return $this;
    }
    
    public function addHiddenField($name, $value = '') {
        $field = $this->addField($name, 'Hidden');
        $field->getElement($name)->setValue($value); 
        return $this;       
    }

    public function createField($name, $type = 'Text', $required = false, $options = array()) {
        try {
           $elemtype = 'Zend_Form_Element_'.$type;
           $elm = $this->decorateElement(new $elemtype($name))
                       ->setRequired($required);
           if(!empty($options) && isset($options['_multioptions'])) {
               $elm->setMultiOptions($options['_multioptions']);
               unset($options['_multioptions']);
           } 
           if(!empty($options) && is_array($options)) {
               $elm->setOptions($options);
           }
           $elm->setAttrib('data-mini', true); // make the field more compact
           return $elm;
        }
        catch(Exception $e) {
           Zend_Debug::dump($e);
        }
    }
    
    public function setFormTemplate($form, $params = array()) 
    {
        $this->_form_tpl = $form;
        if(isset($this->_form_tpl)) {
            $vs = array_merge(array('viewScript'=>$this->_form_tpl), $params);
            $this->setDecorators(array(array('ViewScript', $vs)));         
        }
        return $this;
    }
    
    public function getFormId($salt = '') 
    {
        if(!isset($this->_id)) {
            $this->_id = 'f_'.md5(get_class($this)).$salt;
        }
        return $this->_id;
    }
    
    public function addSubmitButton($name = null, $label = null) {
        if($label === null)
            $label = 'Save';
        if($name === null)
            $name = 'save';
        $this->decorateElement(new Zend_Form_Element_Submit($name, $label));
        return $this;
    }
    
    public function addCancelButton($name = null, $label = null, $method = null) {
        if($label === null)
            $label = 'Cancel';
        if($name === null)
            $name = 'cancel';
        $url = $this->getView()->url(array('action'=>$this->camel($name)));
        if(isset($method)) {
            $submit_form = sprintf("javascript:__submitForm('%s', '%s', '%s');", $this->getFormId(), $url, $method);
        }
        else {
            $submit_form = sprintf("javascript:__submitForm('%s', '%s');", $this->getFormId(), $url);
        }
        $this->decorateElement(new Zend_Form_Element_Button($name, $label))
             ->setAttrib('onclick', $submit_form);
        return $this;
    }

    public function addDateTimeField($name, $required=false, $options=array())
    {
	$field = new Synrgic_Models_Form_Element_DateTime($name);
	$field->setRequired($required);
	$this->decorateElement($field);
	return $this;
    }

    public function addPageField($name, $options=array())
    {
        $field = new Synrgic_Models_Form_Element_Page($name);
        $this->decorateElement($field,array(new Synrgic_Models_Form_Decorator_Page(),'Errors'));
        return $this;
    }

    protected function decorateElement(Zend_Form_Element $elem, $decorators = array('ViewHelper', 'Errors')) {
        $elem->setDecorators($decorators);
        $this->addElement($elem);
        return $elem;
    }

    private function _initForm($options) {
    	if(empty($options['moduleName'])) {
    		$base = APPLICATION_PATH.'/views';
    	}
    	else {
    		$base = APPLICATION_PATH.'/modules/'.$options['moduleName'].'/views';
    	}
        $this->addElementPrefixPath('Synrgic_Validate', $base.'/validators', 'validate');
        $this->addElementPrefixPath('Synrgic_Filter', $base.'/filters', 'filter');
        $this->addElementPrefixPath('Synrgic_Decorator', $base.'/decorators', 'decorator');
        $this->setAttrib('id', $this->getFormId());
        $scripts = $this->getView()->headScript();
        $scripts->appendScript(
             '
                function __submitForm(form_id) {
        			var the_form = document.getElementById(form_id);
        			if(arguments.length>1) {
        				the_form.action = arguments[1];
        			}
        			if(arguments.length>2) {
        				the_form.method = arguments[2];
        			}
        			the_form.submit();
        		}
             '
          );
    }
    
    private function camel($str) {
        return implode('-', preg_split("{
                (?<=[a-z])  # A look-behind assertion
                            # for a lowercase letter
                (?=[A-Z])   # A look-ahead assertion
                            # for an uppercase letter
            }x", $str));
    }
}
