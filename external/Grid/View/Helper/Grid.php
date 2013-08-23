<?php

/**
 * Copyright (c) 2010, Liu Detang (liudetang@yahoo.com)
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 

 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
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
 * To display a dataset
 * 
 * @author liu.dt, Jun 2, 2010
 * 
 * History:
 *    1. modify internal parameter to avoid the potential conflictions with the app as below,
 *         s = sort
 *         b = by
 *         p = page
 *         n = number per page
 *       -- liu.dt, Dec 5, 2012
 *    2. bulk action support, ie, checkall
 *       -- Liu.dt, Mar 6, 2013
 *    3. add acl on actions
 *       -- Liu.dt, Mar 18, 2013
 */
class Grid_View_Helper_Grid extends Zend_View_Helper_Abstract {  
    public $view;
    
    private $_fields = array();
    private $_name = null;
    
    private $_data;
    private $_keys = array('id'); // default primary key is 'id'
    private $_sorting = false;
    private $_empty_message = 'No data found';
    private $_attribs = array('class' => 'grid');
    private $_th_attribs = array();
    private $_tr_attribs = array('valign'=>'middle');
    private $_td_attribs = array('align'=>'left');
    private $_tf_attribs = array('align'=>'center');
    private $_alt_tr_attribs = null;
    private $_alternativeClass = 'even';
    private $_paginator;
    private $_pagination_tpl = null; 
    private $_item_count_per_page = 10;
    private $_current_page_number = 1;
    private $_paginator_enabled = true;
    private $_action_separator = "&nbsp;|&nbsp;";
    private $_action_field_list = null;   // field => action separator
    private $_action_list = array();      // field => action list for each action field
    private $_request = null;
    private $_icon_asc;
    private $_icon_desc;
    private $_paginate_action = array('action' => 'index');
    private $_helper = null;
    private $_s;
    private $_b;
    private $_jssorting = false;
    private $_form_id = null;
    private $_acl = null;

    public function setView(Zend_View_Interface $view) {  
        $this->view = $view;  
        return $this;  
    }  

    /**  
     *  
     * @param string $name
     * @param Bool  $sorting  
     *  
     * @return $this
     */
    public function grid($name, $sorting=true) {
        $this->_init();
        $this->_name = $name;
        $this->setSorting($sorting, '/images/arrow_up.png', '/images/arrow_down.png');
        $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        return $this;
    }
    
    public function setData($data) {
        if((is_array($data) && count($data)>0)) {
            $this->_data = $data;
        }
        
        return $this;
    }
    
    public function setAcl(Zend_Acl $acl) {
    	$this->_acl = $acl;
    }

    public function setField($name, $title = null, $attribs = null, $defvalue = null, $sort = true) {
        if(!empty($name)) {      
            $this->_fields[$name]['title'] = $title;
            $this->_fields[$name]['default_value'] = $defvalue;
            $this->_fields[$name]['attribs'] = $attribs;
            $this->_fields[$name]['sort'] = $sort;
            $this->_fields[$name]['#allowed'] = true;
        }    
        return $this;
    }
    
    public function setActionField($name, $title = null, $separator = null, $attribs = array('align'=>'center')) {
        $this->setField($name, $title, $attribs);
        $this->_action_field_list[$name] = array(
        	'separator' => empty($separator)? $this->_action_separator: $separator,
        );
        $this->_action_list[$name]=array();
        return $this;
    }
    
    public function setBulk($name, $title = null, $config, $attribs = array('align'=>'center')) {
        $this->setField($name, $title, $attribs, null, false);
        $uid = uniqid($name.'_');
        $this->_actionAllowed($config);
        $this->_fields[$name]['#bulk'] = $config;
        $this->_fields[$name]['#uid'] = $uid;
        $this->_fields[$name]['#message'] = $uid.'_msg';
        $this->_fields[$name]['#action'] = $uid.'_js';
        $this->_fields[$name]['#checkall'] = $uid."_ckall";
        $this->_fields[$name]['#allowed'] = $config['#allowed'];
        if($this->_form_id === null) {
            $this->_form_id = uniqid('f_');
        }
        return $this;
    }
    
    /**
     * multiple actions can be grouped in the same column
     * @param $name the name of action field, we can attach an action name to it, 
     *        :edit_delete/delete
     * @param $value the value for display, when $value not given, retrieve from $data[$name]
     * @param $config the config of the action which is an association array:
     *        'keys'=>array(....), 
     *        'params'=>array(...), 
     *        'url'=> array(...) or 'url'=>'<url>' 
     *        
     */
    public function setAction($name, $value = null, $config = array()) {
        $pos = strrpos($name, '/');
        if($pos !== false) {
            // for example: $name = ':action/edit', or ':action/delete'
            $action = substr($name, $pos+1);
            $name = substr($name, 0, $pos);
            $config['action'] = $action;
        }
        if(!isset($this->_action_field_list[$name])) {
            throw new InvalidArgumentException('No such action field defined: '.$name);
        }
        if(isset($value) && !empty($value)) {
            $config['value']=$value;
        }
        $this->_actionAllowed($config);
        array_push($this->_action_list[$name], $config);
        return $this;
    }
         
    public function setSorting($sorting = false, $asc_icon = null, $desc_icon = null) {
        $this->_sorting = $sorting;
        if(!empty($asc_icon)) {
            if(substr($asc_icon, 0, 1) === '/') {
                $this->_icon_asc = $this->view->baseUrl().$asc_icon;
            }
            else {
                $this->_icon_asc=$this->view->baseUrl().'/'.$asc_icon;
            }
        }
        if(!empty($desc_icon)) {
            if(substr($desc_icon, 0, 1) === '/') {
                $this->_icon_desc=$this->view->baseUrl().$desc_icon;
            }
            else {
                $this->_icon_desc=$this->view->baseUrl().'/'.$desc_icon;
            }
        }
        
        return $this;
    }
    
    public function setJsSorting($yes = true) {
        $this->_jssorting = $yes;
    }
    
    public function setEmptyMessage($msg = null) {
        $this->_empty_message = $msg;
        return $this;
    }
       
    public function setAlternativeClass($class = 'even') {
        $this->$_alternativeClass = $class;
        return $this;
    }
    
    public function setThead($attribs = array()) {
        if(!empty($attribs)) {
            $this->_th_attribs = array_merge($this->_th_attribs, $attribs);
        }
        return $this;
    }
    
    public function setTfoot($attribs = array()) {
        if(!empty($attribs)) {
            $this->_tf_attribs = array_merge($this->_tr_attribs, $attribs);
        }
        return $this;
    }
    
    public function setTr($attribs = array()) {
        if(!empty($attribs)) {
            $this->_tr_attribs = array_merge($this->_tr_attribs, $attribs);
        }
        return $this;
    }
        
    public function setAttribs($attribs = array()) {
        if(!empty($attribs)) {
            $this->_attribs = array_merge($this->_attribs, $attribs);
        }
        return $this;
    }
    
    public function setTdAttribs($attribs = null) {
        if(!empty($attribs)) {
            $this->_td_attribs = $attribs;
        }
        return $this;
    }
    
    public function setHelper(Grid_Helper_Interface $helper = null) {
        if($helper !== null) {
            $this->_helper = $helper;
            $this->_helper->setView($this->view);
        }
        return $this;
    }
    
    public function setPagination($pagination = null) {
        if(!empty($pagination)) {
            $this->_pagination_tpl = $pagination;
        }
        return $this;
    }
    
    public function setPaginateAction(array $options = array()) {
        if(!empty($options)) {
            $this->_paginate_action = $options;
        }
        return $this;
    }
    
    public function setItemCountPerPage($count) {
        if($count>0) {
            $this->_item_count_per_page = $count;
        }
        return $this;
    }
    
    public function setCurrentPageNumber($num) {
        if($num>0) {
            $this->_current_page_number = $num;
        }
        return $this;
    }
    
    public function setPaginatorEnabled($enabled = true) {
        $this->_paginator_enabled = $enabled;
        return $this;
    }

    /**
     * Magic overload: Proxy calls to the navigation container
     *
     * @param  string $method             method/property name in this method
     * @param  array  $arguments          [optional] arguments to pass
     * @return mixed                      returns what the container returns
     * @throws Zend_Navigation_Exception  if method does not exist in container
     */
    public function __call($method, array $arguments = array())
    {
        if(substr($method, 0, 3) != 'set') {
            $method = 'set' . @ucfirst($method);
        }
        return call_user_func_array(
                array($this, $method), $arguments);
    }
     
    /**
     * Magic overload: Proxy to {@link render()}.
     *
     * This method will trigger an E_USER_ERROR if rendering the helper causes
     * an exception to be thrown.
     *
     * Implements {@link Zend_View_Helper_Navigation_Helper::__toString()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Exception $e) {
            $msg = get_class($e) . ': ' . $e->getMessage();
            trigger_error($msg, E_USER_ERROR);
            return '';
        }
    }
         
    public function render() {
        $style = $this->view->headStyle();
        // hack jqm style
        //$style->appendStyle(".ui-checkbox input {text-align:center; left:0px;right:0px; padding:0px; margin:2px 0px 2px 0px;position:relative;}");
        
        // scan action fields
        $this->_scanActionPrivileges();
        
        $output = $this->_startHtmlElement('div', array('id' => $this->_name.'_div'));
        if(!empty($this->_fields) && ($this->_isNotEmpty())) {
            $output .= $this->_grid();
        }
        else {
            $output .= $this->_emptyGrid();
        }
        $output .= $this->_endHtmlElement('div');
        if($this->_form_id !== null) {
            $output = '<form id="'.$this->_form_id.'" method="post">'.$output.'</form>';
        }
        return $output;
    }

    private function _grid() {
        $this->_attribs['id'] = $this->_name;
        $this->_attribs['data-role'] = "table";
        $this->_attribs['data-mode'] = "columntoggle";
        $output = $this->_startHtmlElement('table', $this->_attribs);
        $output .= $this->_thread();
        $output .= $this->_tbody();
        $output .= $this->_pagination();
        $output .= $this->_endHtmlElement('table');
        return $output;
    }

    private function _thread() {
        $output = $this->_startHtmlElement('thead');
        $output .= $this->_startHtmlElement('tr', $this->_th_attribs);
 
        $sort = $this->_request->getParam('s','DESC');  
        
        // checking and handling sorting.  
        if ($sort  ==  'ASC') {  
            $sort = 'DESC';
        }  else {  
            $sort = 'ASC';  
        }
        
        $sortedfield = $this->_getSortedField();
        // this foreach loop display the column header  in “th” tag.  
        foreach ($this->_fields as $key => $field) {
        	if($field['#allowed']) {
	            $output .= $this->_startHtmlElement('th', $field['attribs']);
	        	// Check if Sorting is set to True  
	            if($this->_sorting) {
	                // Disable Sorting if Key is in action columns  
	                if(isset($this->_action_field_list[$key])) {
	               		$output .= $this->_th($key, $field);
	                } else {
	                    if(strpos($key, ':')===false && $field['sort']) {
	                        if(!$this->_jssorting) {
	                            $output .= $this->_startHtmlElement('a', 
	                                array('href'=>$this->_makeUrl($this->_paginate_action, array('s'=>$sort,'b'=>$key))));                        
	                            $output .= $this->_th($key, $field);
	                            if($sortedfield === $key) {
	                                $output .= $this->_sortIcon($sort);
	                            }
	                            $output .= $this->_endHtmlElement('a');
	                        }
	                        else {
	                            $output .= $this->_th($key, $field);
	                        }
	                    }
	                    else {
	                        $output .= $this->_th($key, $field);
	                    }
	                }  
	            } else {
	                $output .= $this->_th($key, $field); 
	            }
            	$output .= $this->_endHtmlElement('th');
        	}
        }  
        $output .= $this->_endHtmlElement('tr');
        $output .= $this->_endHtmlElement('thead');
        return $output;
    }
    
    private function _tbody() {
        $output = $this->_startHtmlElement('tbody');
        $ii=0;
        if(!empty($this->_alternativeClass)) {
            $this->_alt_tr_attribs = array_merge(array(), $this->_tr_attribs);
            $this->_alt_tr_attribs['class'] = $this->_alternativeClass;
        }
        else {
            $this->_alt_tr_attribs = $this->_tr_attribs;
        }
        
        // tr attributes
        $odd = $this->_htmlAttribs($this->_tr_attribs);
        $even = $this->_htmlAttribs($this->_alt_tr_attribs);
        
        $this->_paginator = Zend_Paginator::factory($this->_sortBy($this->_data));
        if($this->_paginator_enabled) {
            $this->_paginator->setItemCountPerPage($this->_item_count_per_page);
            //$this->_paginator->setCurrentPageNumber($this->_current_page_number);
            $this->_paginator->setCurrentPageNumber($this->_request->getParam('p', 1));
        }
        else {
            //when paginator disabled, we show all rows rather than any itemcountperpage (including default 10)
            $this->_paginator->setItemCountPerPage(count($this->_data));
        }
              
        foreach($this->_paginator as $p) {  
            $cell_color = ($ii % 2 == 0) ? $odd : $even;  
            $ii++;  
            $output .=$this->_startHtmlElement('tr', $cell_color);
            
            foreach($this->_fields as $k => $field) {
            	if($field['#allowed']) {
					$output .= $this->_startHtmlElement('td', empty($field['attribs'])?$this->_td_attribs:$field['attribs']);
	            	// Check to see if this field is the action column
	                if(isset($this->_action_field_list[$k])) {
	                	$output .= $this->_action($k, $p);
	                } else {
	                    $output .= $this->_td($k, $field, $p);
	                    /*
	                    if(isset($p[$k])) {
	                        $output .= $p[$k]; 
	                    } 
	                    else {
	                        $output .= $field[$k]['empty']; 
	                    }
	                    */
	                }  
   					$output .= $this->_endHtmlElement('td');
            	}
            }  
            $output .= $this->_endHtmlElement('tr');
        }  
        $output .= $this->_endHtmlElement('tbody');
        return $output;
    }
    
    private function _pagination() {
        $output = "";
        if(!empty($this->_pagination_tpl) && $this->_paginator_enabled) {  
             $output .= $this->_startHtmlElement('tfoot');
             $output .= $this->_startHtmlElement('tr');
             $this->_tf_attribs['colspan'] = strval(count($this->_fields));
             $output .= $this->_startHtmlElement('td', $this->_tf_attribs);
            
             $output .= $this->view->paginationControl(
                           $this->_paginator, 'Sliding', $this->_pagination_tpl, array('paginate_action'=>$this->_paginate_action));
               
             $output .= $this->_endHtmlElement('td');
             $output .= $this->_endHtmlElement('tr');
             $output .= $this->_endHtmlElement('tfoot');  
         }  
		
         return $output; 
     }

     private function _emptyGrid() {
         if(!empty($this->_empty_message)) {
             return $this->_renderEmptyMessage();
         }
         return "";
     }
     
     private function _renderEmptyMessage1() {
         $output = $this->_startHtmlElement('table', $this->_attribs);
         $output .= "<tr><td><div class='info'>".$this->_empty_message."</div></td></tr>";
         $output .= $this->_endHtmlElement('table');
         return $output;
     }
     
     private function _renderEmptyMessage() {
         $sorting = $this->_sorting;
         $this->_sorting = false;
         $output = $this->_startHtmlElement('table', $this->_attribs);
         $output .= $this->_thread();
         $this->_tf_attribs['colspan'] = strval(count($this->_fields));
         $output .= "<tr><td colspan='".$this->_countAllowableFields()."'><div class='info'>".$this->_empty_message."</div></td></tr>";
         $output .= $this->_endHtmlElement('table');
         $this->_sorting = $sorting;
         return $output;
     }
     private function _countAllowableFields() {
     	$i = 0;
     	foreach($this->_fields as $name=>$field) {
     		if($field['#allowed']) {
     			$i++;
     		}
     	}
     	return $i;
     }
     
     private function _htmlAttribs($attribs = null) {
         $atts = "";
         if(!empty($attribs)) {
             if(is_array($attribs)) {
                 foreach($attribs as $k => $v) {
                     if(is_string($v) && !empty($v)) {
                         $atts .= " ".$k."='".$v."'";
                     }
                     else if(is_array($v) && !empty($v)) {
                         $a = join(' ', $v);
                         $atts .= " ".$k."='".$a."'";
                     }
                 }
             }
             else if(is_string($attribs)) {
                 $atts = ' '.$attribs;
             }
         }
         return $atts;
     }
     
     private function _startHtmlElement($elem, $attribs = null, $closing = false) {
         $output = "\n";
         $output .= '<'.$elem.$this->_htmlAttribs($attribs);
         if($closing) {
             $output.='/>';
         }
         else {
             $output.='>';
         }
         $output .= "\n";
         return $output;
     }
     
     private function _endHtmlElement($elem) {
         return "\n</".$elem.">\n"; 
     }
     
     private function _action($fldname, $row) {
        $action_field = $this->_action_field_list[$fldname];
        $separator = $action_field['separator'];
        $output = "";
        foreach($this->_action_list[$fldname] as $config) {
        	if($config['#allowed']) {
	            $actionlink = false;
	            if(isset($this->_helper)) {
	                $actionlink = $this->_helper->op($fldname, $config, $row);
	            }
	            if($actionlink === false || !isset($actionlink)) {
	                if(!empty($output)) {
	                    $output .= $separator;
	                }
	                $output .= $this->_actionLink($config, $row);
	            }
	            else {
	                $output .= $actionlink;
	            }
        	}
        }
        return empty($output)?'&nbsp;':$output;
     }
    
     private function _actionLink($config, $row) {
         $params = array();
         $keys = $this->_getKeys($config);
         foreach($keys as $id) {
             if(isset($row[$id])) {
               $params[$id] = $row[$id];
             }
         }
         
         if(isset($config['params'])) {
             foreach($config['params'] as $pk => $pv) {
                 if(isset($row["$pv"])) {
                     // param name is mapped to the column of this row
                     $params[$pk] = $row[$pv];
                 }
                 else {
                     // extra common constant data to complete this action
                     $params[$pk] = $pv;
                 }
             }
         }

         if(empty($config['value'])) {
             // not set the label
             // try to get the label from data source
             $label = $row[$fldname];
         }
         else {
             $label = $config['value'];
         }
         $action = $config['url'];
         
         if(!empty($action['url'])) {
             return $this->_link($label, $action['url'], $params);
         }
         else {
             return $this->_link($label, $action, $params);
         }
     }
     
     private function _linkUrl($action, $params = array()) {
         $url = "";
         if(!empty($action)) {
             if(is_array($action)) {
             	// filter #name meta data
             	$this->_filterMeta($action); 
                $url .= $this->_makeUrl($action, $params);
             }
             else if(is_string($action)) {
                 // normal html url
                 $urlparts = explode('?', $action);
                 $url = array_shift($urlparts);
                 if(!empty($urlparts)) {
                     // chop all last ampands
                     $url = rtrim($action, '&').'&'.$this->_paramsToString($params, false);
                 }
                 else {
                     $url = $url.'?'.$this->_paramsToString($params, false);
                 }
             }
         }
         return $url;
     }
     private function _filterMeta(array &$action) {
     	//make sure no metas are passed to browser
     	//meta data may be useful for gridhelper handler 
     	$keys = array_filter(array_keys($action), function($k) {
     		return strpos($k, '#') === 0;
     	});
     	foreach($keys as $k) {
     		unset($action[$k]);
     	}
     }
     
     private function _link($label, $action, $params=array()) {
         $label = $this->view->translate($label);
         return "<a href = '".$this->_linkUrl($action, $params)."'>".$label."</a>";
     }
     
     private function _sortBy($data) {
         $this->_s = $this->_request->getParam('s', 'ASC');
         $this->_b = $this->_request->getParam('b');
         if(!empty($this->_b) && array_key_exists($this->_b, $this->_fields)) {
            usort($data, array($this, "_compObj"));
         }
         return $data;
     }
     private function _compObj($a, $b) {
         if($a[$this->_b] == $b[$this->_b]) {
             return 0;
         }
         $r = ($a[$this->_b]>$b[$this->_b])?1:-1;
         return $this->_s === 'ASC'?$r:(-$r);
     }

     private function _makeUrl($options, array $params = array()) {
         if(empty($options)) {
             $options = array();
         }
         else if(is_string($options)) {
             $options = array('action'=>$options);
         }
         $options = array_merge($options, $params);
         return $this->view->url($options);
     }
     
     private function _paramsToString($params = array(), $routepath = true) {
         $str = "";
         if($routepath === true) {
             // zend route path
             foreach($params as $k => $v) {
                 if(is_array($v) && !empty($v)) {
                     $v = join(';', $v);
                     $str .= '/'.$k.'/'.$this->view->escape($v);
                 }
                 else if (!empty($v)) {
                     $str .= '/'.$k.'/'.$this->view->escape($v);
                 }
             }
         }
         else {
             // query string
             foreach($params as $k => $v) {
                 if(is_string($v) && !empty($v)) {
                     $str .= $k.'='.$this->view->escape($v).'&';
                 }
                 else if(is_array($v) && !empty($v)) {
                     $v = join(';', $v);
                     $str .= $k.'='.$this->view->escape($v).'&';
                 }
             }
             $str = rtrim($str, '&');
         }
         return $str;
     }
     
     private function _th($fname, $field) {
         if(isset($this->_helper)) {
             $r = $this->_helper->th($fname, $field['title']);
             if($r !== false) {
                 return $r;
             }
         }
         if(isset($field['#bulk'])) {
              return $this->_bulk_th($fname, $field);       
         }
         else {
	         $fieldTitle = $field['title']!=NULL ?  $this->view->translate($field['title']) : $field['title'];
             return $this->_normalizeValue($fieldTitle);
         }
     }
     
     private function _td($fname, $field, $row) {
             if(isset($this->_helper) && array_key_exists($fname, $this->_fields)) {
             $r = $this->_helper->td($fname, $row);
             if($r !== false) {
                 return $r;
             }
         }
         if(!isset($field['#bulk'])) {
             if(isset($row[$fname])) {
                 return htmlspecialchars($this->_cvt($row[$fname]));
             }
             else {
                 return htmlspecialchars($this->_cvt($field['default_value']));
             }
         }
         else {
             return $this->_bulk_td($fname, $field, $row);
         }
     }

     /**
      * bulk action field support
      * <a></a>
      * <checkbox>
      */
     private function _bulk_th($fname, $field) {
         $config = $field['#bulk'];
         if(isset($config['params'])) {
             $params = $config['params'];
         }
         else {
             $params = array();
         }
         $label = $this->view->translate($field['title']);
         if(isset($config['icon'])) {
             $label = sprintf("<img src=\"%s\" width=\"32\" height=\"32\"/>", $config['icon']);
         }
         $link = sprintf('<a href="javascript:%s(\'%s\', \'%s\')">%s</a>', 
                     $field['#action'], 
                     $this->_form_id,
                     $this->_linkUrl($config['url'], $params),
                     $label
         );
         $checkall = sprintf('<input type="checkbox" name="%s" id="%s" onChange="javascript:%s(this, \'%s[]\');"/>',
                         $field['#uid'], $field['#uid'], $field['#checkall'], $fname);
         $this->_bulk_js($fname, $field);
         
         return $link.'<br/>'.$checkall;
     }
     
     private function _cvt($val) {
         if($val instanceof DateTime) {
             return $val->format("Y-m-j H:i:s");
         }
         else {
             // only allow simple types
             return $val;
         }
     }
     private function _bulk_td($fname, $field, $row) {
         $config = $field['#bulk'];
         $values = array();
         $keys = $this->_getKeys($config);
         foreach($keys as $id) {
             if(isset($row[$id])) {
                 $values[] = $this->_cvt($row[$id]);
             }
         }

         return sprintf('<input type="checkbox" name="%s[]"/>', $fname);
     }
     
     const bulkaction = '
       function %s(formid, action) {
            var conf = true;
            if(%s !== null) {
               conf=confirm(%s);
            }
            if(conf) {
                var the_form = document.getElementById(formid);
			    if(arguments.length>1) {
				    the_form.action = arguments[1];
			    }
			    if(arguments.length>2) {
				    the_form.method = arguments[2];
			    }
			    the_form.submit();
            }
		}';
     const bulkckall = '
       function %s(ckall, name) {
             var doms = ckall.form.getElementsByTagName("input");
             for(i=0; i<doms.length; i++) {
                if(doms[i].type == "checkbox" && doms[i].name == name) {
                   doms[i].checked = ckall.checked;
                } 
             }
		}';
     private function _bulk_js($fname, $field) {
     	if($this->_fields[$fname]['#allowed']) {
	        $scripts = $this->view->headScript();
	        $config = $field['#bulk'];
	        $js = array();
	        if(isset($config['message'])) {
	            $js[] = sprintf('var %s = "%s";', $field['#message'], htmlspecialchars($config['message']));
	        }
	        else {
	            $js[] = sprintf('var %s = null', $field['#message']);
	        }
	        $js[] = sprintf(self::bulkaction, $field['#action'], $field['#message'], $field['#message']);
	        $js[] = sprintf(self::bulkckall, $field["#checkall"]);
	        $scripts->appendScript(implode("\n", $js));
     	}
     }
     //--------------

     // acl support
     // resource format: module:controller
     private function _actionAllowed(&$config) {
     	if(isset($this->_acl)) {
	     	if(isset($config['#role'])) {
	     		$role = $config['#role'];
	     		unset($config['#role']);
	     	}
	     	if(isset($config['#resource'])) {
	     		$resource = $config['#resource'];
	     		unset($config['#resource']);
	     	}
	     	if(isset($config['#priviledge'])) {
	     		$priviledge = $config['#priviledge'];
	     		unset($config['#priviledge']);
	     	}
	     	if(!isset($role)) {
	     		// try to retrieve from session
	     		$role = $this->_role();
	     	}
	     	if(!isset($resource)) {
	     		//reflect the resource
	     		$resource = $this->_actionResource($config);
	     	}
	     	if(!isset($priviledge)) {
	     		$priviledge = $config['url']['action'];
	     		if($priviledge == 'index') {
	     			$priviledge == 'view';
	     		}
	     	}
     	    try {
     			$allowed = $this->_acl->isAllowed($role, $resource, $priviledge);
     			$config['#allowed'] = $allowed;
     			return $allowed;
     		}
     		catch(Zend_Acl_Exception $e) {
     			return false;
     		}
     	}
     	else {
     		//make compatibility back
     		$config['#allowed'] = true;
     		return true;
     	}	
     }
     private function _role() {
     	try {
     		$auth = Zend_Auth::getInstance();
     		return $auth->getIdentity()->role; //??
     	}
     	catch(Exception $e) {
     		return '';
     	}
     }
     private function _actionResource($config) {
     	if(isset($config['url']['module'])) {
     		$module = $config['module'];
     	}
     	if(isset($config['url']['controller'])) {
     		$controller = $config['controller'];
     	}
     	if(!isset($module)) {
     		$module = $this->_request->getModuleName();
     	}
     	if(!isset($controller)) {
     		$controller = $this->_request->getControllerName();
     	}
     	return $module.':'.$controller; // fixed format??
     }
     
     private function _scanActionPrivileges() {
     	foreach($this->_action_list as $name=>$configs) {
     		$allowed = false;
     		foreach($configs as $c) {
     			if($c['#allowed']) {
     				//at least one allowed
     				$allowed = true;
     				break;
     			}
     		}
     		$this->_fields[$name]['#allowed'] = $allowed;
     	}
     }
     
     //-------------------------------------
        
     
     private function _normalizeValue($value) {
         return empty($value)?'&nbsp;': ($value === '&nbsp;')?$value:htmlspecialchars($value);
     }
     
     private function _sortIcon($sort) {
         $icon = $sort === 'ASC'? $this->_icon_desc:$this->_icon_asc;
         return '<img src="'.$icon.'" border="0" />';
     }
     
     private function _getSortedField() {
         return $this->_request->getParam("b", null);  
     }
     
     private function _getKeys($config) {
         if(isset($config['keys'])) {
             return $config['keys'];
         }
         else {
             return $this->_keys;
         }
     }
     
     private function _isNotEmpty() {
       if(isset($this->_data)) {
           if(is_array($this->_data)) {
               return count($this->_data) > 0;
           }
           else if($this->_data instanceof Doctrine_Collection) {
               return $this->_data->count() > 0;
           }
       }
       return false;   
     }
     
     private function _init() {
        $this->_fields = array();
        $this->_name = null;
   
        $this->_data = null;
        $this->_keys = array('id'); // default primary key is 'id'
        $this->_sorting = false;
        $this->_empty_message = 'No data found';
        $this->_attribs = array('class' => 'grid');
        $this->_th_attribs = array();
        $this->_tr_attribs = array('valign'=>'middle');
        $this->_td_attribs = array('align'=>'left');
        $this->_tf_attribs = array('align'=>'center');
        $this->_alt_tr_attribs = null;
        $this->_alternativeClass = 'even';
        $this->_paginator = null;
        //$this->_pagination_tpl = 'include/pagination1.phtml';
        $this->_pagination_tpl = '__grid_pagination.phtml';
        $this->_item_count_per_page = 10;
        $this->_current_page_number = 1;
        $this->_paginator_enabled = true;
        $this->_action_separator = "&nbsp;|&nbsp;";
        $this->_action_field_list = null;   // field => action separator
        $this->_action_list = array();      // field => action list for each action field
        $this->_request = null;
        $this->_icon_asc = null;
        $this->_icon_desc = null;
        $this->_paginate_action = array('action' => 'index');
        $this->_helper = null;

        // retrieve a default acl from registry, later on we always 
        // can overwrite it by setAcl
        $this->_acl = Zend_Registry::get('acl'); //?? 
     }
}
