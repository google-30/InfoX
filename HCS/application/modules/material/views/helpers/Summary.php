<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Summary
 *
 * @author philip
 */
class GridHelper_Summary extends Grid_Helper_Abstract 
{
    protected function td_orderdate($field, $row) 
    {
    	return ($row->$field) ? $row->$field->format('Y-m-d') : "&nbsp;";
    }    
}
