Setup grid view helper

1. make a symbol link in APPLICATION_PATH/library/Grid to external/Grid

2. create plugin to set the grid view helper path

   $view->addHelperPath(APPLICATION_PATH.'/../library/Grid/View/Helper', "Grid_View_Helper_");
   $view->addScriptPath(APPLICATION_PATH.'/../library/Grid/View/Helper');

3. set grid helper path in Bootstrap.php

   require_once(APPLICATION_PATH.'/../library/Grid/Helper/Autoloader.php');
   $modules = array('adverts', 'bill', 'gambling', 'information', 'local', 'management', 'noticeboard', 'room', 'service');
   $prefixes = array();
   foreach($modules as $m) {
       $prefixes[APPLICATION_PATH.'/modules/'.$m.'/views/helpers'] = 'GridHelper_';
   }
   Grid_Helper_Autoloader::registerPath($prefixes);
        
   Zend_Loader_Autoloader::getInstance()->registerNamespace("Grid_");
   $fc = $this->getResource('frontController');
  $fc->registerPlugin(new XXX_Plugin_ViewSetup());

