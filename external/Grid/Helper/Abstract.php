<?php
/**
 * Copyright (c) 2010, Liu Detang (liudetang@yahoo.com)
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 

 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution. 
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies, 
 * either expressed or implied, of the FreeBSD Project.
 */

/**
 * To make an abstract contract to all grid helpers
 * 
 * for a head field, we can implement a method with signature: th_<fieldname>($name, $value)
 * if the field is computing field, the signature is th__<fieldname>($name, $value), the ':' of 
 * the compute field has been replaced by an underscore '_'. 
 * for a data field, there is the same rule as the head field except that the prefix 'th_' is
 * replaced by 'td_'.
 * 
 * All grid helpers should be put into application/views/helpers/grid and their class names have the
 * prefix 'Intraco_Grid_Helper'; otherwise Intraco_Autoloader will not be able to see them.
 * 
 * For example, Intraco_Grid_Helper_Device will help the device controller render a complicated grid.
 * 
 * 1. add delegate: Aug 29, 2010, liu.dt@intracotechnology.com
 *    There are lots of situations under which only name is different but all logic are the same.
 *    Thus to reduce such redundant code, for example:
 *    
 *    public function init() {
 *      $this->delegateTo(array('monday', 'tuesday',....), 'weekday');
 *    }
 *    public function td_weekday($name, $r) {
 *       // blah blah
 *    }
 *    Thus, we don't need repeat 7 times
 *    
 * @author liu.dt, Jul 2, 2010
 */
abstract class Grid_Helper_Abstract implements Grid_Helper_Interface {
    private $_th_helpers = array();
    private $_td_helpers = array();
    private $_action_helpers = array();
    private $_td_helper_delegate_map = array('*'=>null);
    protected $_view;
    
    public function __construct() {
        $this->_init();
        $this->init();
    }
    
    public function setView(Zend_View_Interface $view) {
        $this->_view = $view;
        return $this;    
    }
    
    public final function th($name, $value) {
        $th_meth = $this->_getMethodBy($name, 'th_');
        if(in_array($th_meth, $this->_th_helpers)) {
            //return call_user_func_array(array($this, $th_meth), array($name, $value));
            return $this->$th_meth($name, $value);
        }
        return false;
    }
    
    public final function td($name, $row) {
        $td_meth = $this->_getMethodBy($name, 'td_');
        if(in_array($td_meth, $this->_td_helpers)) {
            //return call_user_func_array(array($this, $td_meth), array($name, $row));
            return $this->$td_meth($name, $row);
        }
        else {
            return $this->callDelegate($name, $row);
        }
    }
    
    public final function op($name, $action_config, $row) {
        $action_meth = $this->_getMethodBy($name, 'op_');            
        if(in_array($action_meth, $this->_action_helpers)) {
            return $this->{$action_meth}($name, $action_config, $row);
        }
        return false;
    }

    public function init() {
        
    }
    
    /**
     * delegate helpers to antoher helper
     * @param unknown_type $helpers
     * @param unknown_type $delegate
     */
    protected function delegateTo($helpers, $delegate) {
        if(!empty($helpers) && !empty($delegate)) {
            if(is_array($helpers)) {
                foreach($helpers as $name) {
                    $this->_td_helper_delegate_map[$name] = $delegate;
                }
            }
            else {
                $this->_td_helper_delegate_map[$helpers] = $delegate;
            }
        }
    }
    
    private function callDelegate($name, $row) {
        if(isset($this->_td_helper_delegate_map[$name])) {
            $td_meth = $this->_td_helper_delegate_map[$name];
            return $this->$td_meth($name, $row);
        }
        else {
            // no exact delegate matched, try to find a wildcard match
            if(isset($this->_td_helper_delegate_map['*'])) {
                $td_meth = $this->_td_helper_delegate_map['*'];
                return $this->$td_meth($name, $row);
            }
        }
        return false;
    }
    
    private function _getMethodBy($name, $prefix) {
        return $prefix.str_replace(':', '_', $name);
    }
    
    private function _init() {
        $methods = get_class_methods(get_class($this));
        
        foreach($methods as $m) {
            if(strpos($m, 'th_') !== false) {
                array_push($this->_th_helpers, $m);
            }
            else if(strpos($m, 'td_') !== false) {
                array_push($this->_td_helpers, $m);
            }
            else if(strpos($m, 'op_') !== false) {
                array_push($this->_action_helpers, $m);
            }
        }
    }
}
