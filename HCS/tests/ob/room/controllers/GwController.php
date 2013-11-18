<?php
/*
 * - Copyright (c) 2012 Synrgic Research Pte Ltd All rights reserved
 * Redistribution of this file in source code, or binary form is expressly not
 * permitted without prior written approval of Synrgic Research Pte Ltd
 */

/*
 * this function is used to transfer the date to ip2rf gateway.
 * add by sean.cheng 2012.10.10
 */


class Room_GwController extends Zend_Controller_Action
{

 public function init ()
 {
 /* Initialize action controller here */
 $this->_helper->layout->disableLayout();
 }

 public function indexAction ()
 {
 $ip = $this->getRequest()->getParam('ip', null);
 if ($ip === null)
 exit();
 $act = $this->getRequest()->getParam('act', null);
 	$target = $this->getRequest()->getParam('target', null);
 $url = $url = "http://" . $ip ."&target=".$target."&act=". $act;
 
 //$url="http://192.168.0.202/images/room/gw.txt";
/*
 $opts = array(
 'http' => array(
 'method' => "GET",
 'timeout' => 6000
 ) // set the timeout
 );
 
 $context = stream_context_create($opts);
 $contain = file_get_contents($url, false, $context);
 */
 $curl = curl_init();
 curl_setopt($curl,CURLOPT_URL,$url);
 $contain=curl_exec($curl);
 curl_close($curl);
 //echo $contain;
 //exit();
 if (! $contain) 
 {
 $data = '{"GW":[{"ok":"0"}]}';
 echo $data;
 exit();
 } else 
 {
 echo $contain;
 exit();
 }
 }
	
}


