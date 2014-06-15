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
 * This plugin sets the layout path to that of the module if
 * the module has a layout directory. This can't be done via a resource
 * definition in application.ini because routing has not taken place in
 * BootStrap.php. Hence we have to wait until dispatch time.
 */
class Synrgic_Plugin_LayoutInit extends Zend_Controller_Plugin_Abstract
{

	public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
	{
		$layout = Zend_Layout::getMvcInstance();

		// Determine the device in use
		$device = strtolower(\Synrgic\Device::probeDeviceTypeAsString());
		Zend_Registry::set('device-type', $device); //let controller quickly determine what settings should be used for

		//
		// Rules for layout selection:
		//  1. If the user is a staff user user the management layout
		// 	2. If the module has it's own layout use it
		//  3. Else use the guest layout
		//
		$path = APPLICATION_PATH .  '/modules/' .  $request->getModuleName() .  '/layouts/scripts/' . $device;
		$acl = Zend_Registry::get('acl');
		$auth = Zend_Auth::getInstance();

		if( $auth->hasIdentity() && $acl->isAllowed($auth->getIdentity()->role,'management:dashboard','view') ){
			$path = APPLICATION_PATH .  '/modules/management/layouts/scripts/' . $device;
			$layout->getView()->navigation(Zend_Registry::get('navigation'));
		} else if(is_dir($path)){
			// Set the layout directory for the loaded module if exists,
			// else fallback to default module
			$layout ->setLayoutPath($path);
		} else {
			$layout->getView()->navigation(Zend_Registry::get('navigation-guest'));
			$path = APPLICATION_PATH .  '/modules/default/layouts/scripts/' . $device;
		}

		$layout->style = $this->_layoutStyle();
		$layout->setLayoutPath($path);
	}

	
	// put complex logic here to make the layout template clean
	private function _layoutStyle() 
	{
    $settings = Zend_Registry::get('settings');
    $style = '';
    
    try {
        foreach($settings->Styling as $k=>$v) {
            $k = str_replace("_", "-", $k);
            $val = trim($v->Value);
            $val = trim($val, "\xc2\xa0");
            if(isset($val) && $val !== '') {
                if($k === 'background-image') {
                    $style .= sprintf("%s:url('%s');", $k, $val);
                } 
                else {
                    $style .= sprintf("%s:%s;", $k, $val);
                }
            }
        }
        return rtrim($style, ';');
    }
    catch(Exception $e) {
        return '';
    }
    
	}
	
}

?>
