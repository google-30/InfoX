<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * An abstract entity class
 * @author liu.dt, Oct 5, 2012
 */
abstract class Synrgic_Models_Entity implements ArrayAccess, Serializable {
    private $_props = null;
    private $_ignoreException = false; // just for simplifying calls
    
    /**
     * Assign an entity from an array
     * @param array $values
     * @param string $ignoreException throw exception for undefined fields if false
     */
    public function fromArray(array $values, $ignoreException = true) 
    {
        if(!empty($values)) {
            $this->_ignoreException = $ignoreException;
            foreach($values as $k=>$v) {
                $setter = 'set'.ucfirst($k);
                if(is_array($v)) {
                    $this->{$setter}(array($v));
                }
                else {
                    $this->{$setter}($v);
                }
            }
            $this->_ignoreException = false;
        }
        return $this;
    }

    /**
     * Convert an entity intstance to an array
     * @param string $ignoreException throw exception for undefined fields if false
     * @return array
     */
    public function toArray($ignoreException = true) 
    {
        $this->_ignoreException = $ignoreException;
        $a = array();
        foreach($this->_getProperties() as $f) {
            $getter = 'get'.ucfirst($f);
            $a[$f] = $this->{$getter}(); 
        } 
        $this->_ignoreException = false;
        return $a;
    }

    // short hand in code
    public function __get($property) {
        return $this->{$property};
    }

    public function __set($property, $val) {
        $this->{$property} = $val;
    }
    
    public function __call($method, $args) 
    {
        if(method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $args);
        }
        else {
	    $pos = strpos($method,'set');
            $fname = substr($method,3);
            $props = $this->_getProperties();
            if($pos===0 && !empty($args)) {
                $fname = lcfirst($fname);
                if($fname && in_array($fname, $props)) {
                    $this->{$fname} = $args[0]; 
                }
                else if(in_array($method, $props)) {
                    $this->{$method} = $args[0];
                } 
		        else {
		            $this->_throwException("no such field defined: ". $fname . " in " .get_class($this));
		        }
            }
            else {
		$pos = strpos($method,'get');
                $fname = substr($method,3);
                if($pos===0) {
                    $fname = lcfirst($fname);
                    if($fname && in_array($fname, $props)) {
                        // try return a default value
                        return empty($this->{$fname}) && !empty($args)?$args[0]:$this->{$fname};
                    } 
                    else if(in_array($method, $props)) {
                        return empty($this->{$method}) && !empty($args)?$args[0]:$this->{$method};
                    }
		            else {
			            $this->_throwException("no such field defined: ". $fname . " in " .get_class($this));
		            }
                } 
                else {
                    $this->_throwException("no such field defined: ".$fname." in ".get_class($this));
                }
            }
        }
    }
    private function _throwException($msg) {
        if(!$this->_ignoreException) {
            throw new \Exception($msg);
        }
    }

    //make sure that our entity also can be accessed like array 
    public function offsetExists($offset) {
        $props = $this->_getProperties();
        return in_array($offset, $props);
    }
    
    public function offsetUnset($offset) {
        //never allow to unset an entity property
    }
    
    public function offsetGet($offset) {
        return $this->{$offset};
    }
    
    public function offsetSet($offset, $value) {
        $this->{$offset} = $value;
    }

    /**
     * A convenient method for datetime convertion
     *
     * @param $fname the name of a datetime property
     * @param $val the value need to be set to the property
     * @param $format
     */
    protected function _setDateTime($fname, $val, $format = '') 
    {
        $fname = lcfirst($fname);
        $props = $this->_getProperties();
        if(!empty($val) && in_array($fname, $props)) {
            if($val instanceof \DateTime) {
                $this->{$fname} = $val;
            }
            else { 
                $dt = new \DateTime();
                $dt->modify($val); 
                $this->{$fname} = $dt;
            }
        }
    } 

    private function _getProperties() 
    {
        if($this->_props == null) {
            $vars = array();
            foreach(get_object_vars($this) as $k=>$v) {
                if(substr($k,0,1) <> '_') {
                    $vars[] = $k;
                }
            }
            $this->_props = $vars;
        }
        return $this->_props;
    }
    
    public function serialize() {
    	return base64_encode(serialize($this->toArray()));
    }
    	
    public function unserialize($serialized) {
    	return $this->fromArray(unserialize(base64_decode($serialized)));
    }
}
