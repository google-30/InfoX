<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Information_ManageController extends Zend_Controller_Action
{
    private $em;
    private $infodata;
    private $pagedata;

    const GROUP_ID = 1; // TODO: group id should store in config/bootstrap
    private $pagegroup;        
    
    public function init()
    {
        /* Initialize action controller here */
        $this->em = Zend_Registry::get('em');
        $this->infodata = $this->em->getRepository('\Synrgic\Information\Infodata');
        $this->pagedata = $this->em->getRepository('\Synrgic\Page');

        // make sure information group exists
        // TODO: information group id 
        $id = self::GROUP_ID;
        $result = $this->em->getRepository('\Synrgic\PageGroup')->findOneBy(array('id' => $id));
        if($result)
        {
            $this->pagegroup = $result;                                
        }
        else
        {// XXX: error handle
            echo "Information_ManageController, PageGroup error.\n";
        }
    }

    public function indexAction()
    {
        $getLang = $this->_getParam("language"); 
        $langSels = "";

	    $languages = $this->em->getRepository('\Synrgic\Language')->findAll();
	    $langArray = array();
	    foreach($languages as $lang )
	        $langArray[$lang->getLocale()] = $lang->getName();

        foreach($langArray as $langTmp)
        {
            if($langTmp === $getLang)
            {
                $langSels .= '<option selected="selected" value="' . $langTmp. '">' . $langTmp . '</option>';

            }
            else
            {
                $langSels .= '<option value="' . $langTmp. '">' . $langTmp . '</option>';
            }
        }
        $this->view->langSelections = $langArray;
        $this->view->langSels = $langSels;

        // query
        // TODO: language choose        
        $whereLang = ($getLang != "") ? $getLang : $langArray["en_US"];

        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from('\Synrgic\Information\Infodata', 'a')
            ->where('a.language = ?1 ')
            ->setParameter(1, $whereLang);
        $result = $qb->getQuery()->getResult();
        //var_dump($result);

        $this->view->infos = empty($result) ? "": $result;
    }    

    /*
    public function getLangSels()
    {
	    $languages = $this->em->getRepository('\Synrgic\Language')->findAll();
	    $langArray = array();
	    foreach($languages as $lang )
	        $langArray[$lang->getLocale()] = $lang->getName();

        $langSels="";
        foreach($langArray as $langTmp)
        {

            $langSels .= '<option value="' . $langTmp. '">' . $langTmp . '</option>';
        }
        $this->view->langSelections = $langArray;
        $this->view->langSels = $langSels;
    }
    */

    public function getLangSels($selectlang)
    {
        $languages = $this->em->getRepository('\Synrgic\Language')->findAll();
        $langArray = array();
        foreach($languages as $lang )
            $langArray[$lang->getLocale()] = $lang->getName();

        $langSels="";
        foreach($langArray as $tmp)
        {
            if($tmp === $selectlang)
            {
                $langSels .= '<option selected="selected" value="' . $tmp. '">' . $tmp . '</option>';

            }
            else
            {
                $langSels .= '<option value="' . $tmp. '">' . $tmp . '</option>';
            }
        }

        return $langSels;
    }

    public function addnewAction()
    {
        $id = $this->_getParam("id");
        $this->view->pageid = $id;
        $pageObj = $this->em->getRepository('\Synrgic\Page')->findOneBy(array('id'=>$id)); 
        $language = $pageObj->getLanguageName();        
        $this->view->language = $language;
        $this->view->pageid = $id; 
    }

    public function editAction()
    { 
        $id = $this->_getParam("pageid");
        $this->view->pageid = $id;
        $pageObj = $this->em->getRepository('\Synrgic\Page')->findOneBy(array('id'=>$id)); 
        $language = $pageObj->getLanguageName();        
        $this->view->language = $language;
        $this->view->pageid = $id;   
      
        $infoid = $this->_getParam("id");
        $this->view->id = $infoid;

        $data = $this->em->getRepository('\Synrgic\Information\Infodata')
                        ->findOneBy(array('id' => $infoid));
        $this->view->category = $data->getTitle();
        $this->view->content = $data->getContent();
    }
    
    public function deleteAction()
    {   
	$pageid = $this->getParam('pageid');
	$id = $this->getParam('id');
        $requests = $this->getRequest()->getPost();

	$repo = $this->em->getRepository('\Synrgic\Information\Infodata');
	$data = $repo->findOneBy(array('id' => $id));
	$this->em->remove($data);
        $this->em->flush();

	$this->forward('pageedit','manage','information',array('id'=>$pageid));
    }

    public function tinymcesubmitAction()
    {        
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $category = $this->getParam("catehidden"); 
        $language = $this->getParam("langhidden"); 
        $content  = $this->getParam("data");
        $redirect = $this->getParam("redirect");
        $mode = $this->getParam("modehidden");           

        if($mode == "Edit")
        {   // update
            $infoid = $this->_getParam("infoid");
            $infodata = $this->infodata->findOneBy(array('id'=>$infoid));    
            $infodata->setTitle($category);
            //$infodata->setLanguage($language);    
            $infodata->setContent($content);
            $this->em->persist($infodata);
            $this->em->flush();
        } 
        else
        {
            $pageid = $this->_getParam("pageid");
            $pageObj = $this->pagedata->findOneBy(array('id'=>$pageid)); 

            $infodata = new \Synrgic\Information\Infodata();
            $infodata->setTitle($category);
            //$infodata->setLanguage($language);    
            $infodata->setContent($content);
            $infodata->setPage($pageObj);
            $this->em->persist($infodata);
            $this->em->flush(); 
        }       

	$this->redirect($redirect);
    }

    public function previewAction()
    {
        $this->_helper->layout->disableLayout();                

        $pageid = $this->_getParam("id");    
        $pageObj = $this->pagedata->findOneBy(array('id'=>$pageid)); 
        $data = $this->infodata->findby(array('page'=>$pageObj));
        
        $icount = 0;
        $information = "";
        foreach($data as $row)
        {            
            $sCate = $row->getTitle();
            $content = $row->getContent();
            $sLang = $row->getLanguage();

            $icount++;            

            if($icount == 1)
            {
                $information .= '<div data-role="collapsible" data-collapsed="false"><h3>';
            }
            else
            {
                $information .= '<div data-role="collapsible"><h3>';
            }

            $information .= $this->view->translate($sCate) . '</h3><p>' . $content  . '</p></div>';
        }

        $this->view->data = $data; 
        $this->view->information = $information; 
        
    }

    function getStates($state)
    {
        $states = array("Normal", "Trash");
        $sels="";
        foreach($states as $tmp)
        {
            if($tmp === $state)
            {
                $sels .= '<option selected="selected" value="' . $tmp. '">' . $tmp . '</option>';

            }
            else
            {
                $sels .= '<option value="' . $tmp. '">' . $tmp . '</option>';
            }
        }

        return $sels;           
    }
    

    function pagemanageAction()
    {

        $language = $category = $this->_getParam("language");   
        $language = ($language == "") ? "English": $language; 
        $this->view->langSels = $this->getLangSels($language);

        $statesel = $this->_getParam("state");
        $statesel = ($statesel == "") ? "Normal" : $statesel;        
        $this->view->stateSels = $this->getStates($statesel);    
        
        $langObj = $this->em->getRepository('\Synrgic\Language')->findOneBy(array('name'=>$language));        
        $pages = $this->em->getRepository('\Synrgic\Page')->findBy(array('language'=>$langObj, 'group'=>$this->pagegroup));

        $displaypages = array();
        
        foreach($pages as $page)
        {
            // convert state to string
            $state = $page->getState();  
            switch ($state) {
                case 0:
                    $page->state = "Draft";
                    break;
                case 1:
                    $page->state = "Published";
                    break;
                case 2:
                    $page->state = "Deleted";
                    break;
                default:
                    $page->state = "Obsoleted";
                    break;
            }  

            if($statesel=="Trash" && $state==2)
            {
                $displaypages[] = $page;
            }
            else if($statesel=="Normal" && $state!=2)
            {
                $displaypages[] = $page;
            }            
                        
        }
            

        $this->view->pages = $displaypages;

    }

    function addpageAction()
    {
        $language = $this->_getParam("language");   
        $language = ($language == "") ? "English": $language;        
        $this->view->langSels = $this->getLangSels($language);
    } 

    function deletepageAction()
    {
	$id = $this->getParam('id');
  
	$pageObj = $this->pagedata->findOneBy(array('id'=>$id));                 
        $pageObj->setState(2);
	$this->em->persist($pageObj);
        $this->em->flush();              

	$this->forward('pagemanage','manage','information');
    }         

    function publishpageAction()
    {
        $requests = $this->getRequest()->getPost();

        $pageid = $this->_getParam("id");
    
        $targetObj = $this->pagedata->findOneBy(array('id'=>$pageid)); 

        // set last published page to draft        
        $records = $this->pagedata->findBy(array('group'=>$this->pagegroup,'language'=>$targetObj->getLanguage()));

        foreach($records as $obj)
        {
            if($obj->getState() == $obj::PUBLISHED)            
            {
                $obj->setState($obj::DRAFT);
            }    
            $this->em->persist($obj);
        }

        // set current page to publish                
        $targetObj->setState($targetObj::PUBLISHED);
        $this->em->persist($targetObj);
        $this->em->flush();
	$this->forward('pagemanage','manage','information');
    }

    public function pageeditAction()
    {        
        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            return;
        }
        
        $mode = $this->_getParam("mode"); //echo "mode=$mode\n";
        $language = $this->_getParam("language");

        // TODO: date and time
        $date = $this->_getParam("date");
        //$pageid = $this->_getParam("pageid");

        if($mode == "create")
        {// create page
            //TODO: lots to do, check existence; error handle, object check, etc
            
            $langObj = $this->em->getRepository('\Synrgic\Language')->findOneBy(array('name'=>$language));
            $grpObj = $this->em->getRepository('\Synrgic\PageGroup')->findOneBy(array('id'=>self::GROUP_ID));                            
    
            $record = new \Synrgic\Page();
            //$record->setLanguage($langid); 
            $record->setLanguage($langObj);   
            $record->setGroup($grpObj);            
            $record->setState($record::DRAFT);
            $record->setContent("");
            //$record->setCreated(new \Datetime('now'));
            $record->setCreated(new \Datetime($date));
            //$record->setPreviousRevision(null);
            $this->em->persist($record);
            $this->em->flush();                     
            
        }
        else
        {// edit page
            $id = $this->_getParam("id");
            $this->view->pageid = $id;
            $pageObj = $this->em->getRepository('\Synrgic\Page')->findOneBy(array('id'=>$id)); 
            $language = $pageObj->getLanguageName();
            
            $infodata = $this->em->getRepository('\Synrgic\Information\Infodata')->findBy(array('page'=>$pageObj));
            $this->view->infos = $infodata;
   
        }
   
        $this->view->language = $language;                          
    }  
}

