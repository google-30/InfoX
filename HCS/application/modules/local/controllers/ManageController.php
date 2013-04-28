<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Local_ManageController extends Zend_Controller_Action
{
    private $em;
    private $session;
    private $attrdata;
    private $advertiserdata;

    public function init()
    {
        // cross domain request
        header("Access-Control-Allow-Origin: *");

        $this->em = Zend_Registry::get('em');
        $this->session = Zend_Registry::get('em');
        $this->attrdata = $this->em->getRepository('\Synrgic\LocalAttractions\Attractiondata');    
        $this->advertiserdata = $this->em->getRepository('\Synrgic\Adverts\Advertiser');
    }

    public function getLangArray()
    {
        $languages = $this->em->getRepository('\Synrgic\Language')->findAll();
	    $langArray = array();
	    foreach($languages as $lang )
	        $langArray[$lang->getLocale()] = $lang->getName();

        return $langArray;         
    }    

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

    private function getCateSels($category)
    {
        $cateArray = array("Shopping", "Culture","Relax", "Amusement");
        $cateSels = "";
        foreach($cateArray as $tmp)
        {
            if($tmp === $category)
            {
                $cateSels .= '<option selected="selected" value="' . $tmp. '">' . $tmp . '</option>';

            }
            else
            {
                $cateSels .= '<option value="' . $tmp. '">' . $tmp . '</option>';
            }
        }    

        return $cateSels;
    }    

    private function getAdvertisers()
    {
        $data = $this->advertiserdata->findall();
        $Advertisers = array();
        
        foreach($data as $entry)
        {
            $name = $entry->getName();
            $Advertisers[] = $name;    
        }
            
        return $Advertisers;
    }

    private function getAdvertiserOptions($Advertisers, $selected)
    {
        $options = "";
        foreach($Advertisers as $tmp)
        {
            if($tmp === $selected)
            {
                $options .= '<option selected="selected" value="' . $tmp. '">' . $tmp . '</option>';

            }
            else
            {
                $options .= '<option value="' . $tmp. '">' . $tmp . '</option>';
            }
        }
        return $options;
    }

    public function indexAction()
    {
        // set language
        $langArray = array();
        $langArray = $this->getLangArray();
        $getLang = $this->_getParam("language");
        $langSels = $this->getLangSels($getLang);
        $this->view->langSels = $langSels;
        $whereLang = ($getLang == "") ? $langArray["en_US"] : $getLang;
        $langid = $this->em->getRepository('\Synrgic\Language')->findOneBy(array('name' => $whereLang))->getId();

        // TODO: category
        $cateArray = array("Shopping", "Culture","Relax", "Amusement");
        $getCate = $this->_getParam("category");
        $cateSels = $this->getCateSels($getCate);
        $this->view->cateSels = $cateSels;

        $whereCate = ($getCate == "") ? $cateArray[0] : $getCate;

        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from('\Synrgic\LocalAttractions\Attractiondata', 'a')
            ->where('a.language = ?1 AND a.type = ?2')
            ->setParameters(array(1 => $langid, 2 => $whereCate))
            ->orderBy('a.level');
        $result = $qb->getQuery()->getResult(); 
        
        $attractions = array();
        
        foreach ($result as $tmp)
        {
            $entry = array();
            $date = $tmp->getStart(); 
            $entry["start"] = $date ? $date->format('m/d/Y H:i:s') : "";
            $date = $tmp->getStop(); 
            $entry["stop"] = $date ? $date->format('m/d/Y H:i:s') : "";

            $entry["id"] = $tmp->getId();
            $entry["title"] = $tmp->getTitle(); 

            $advertiser = $this->advertiserdata->findOneBy(array('id' => $tmp->getAdvertiser()));
            $entry["advertiser"] =  $advertiser ? $advertiser->getName() : "";

            $entry["sponsor"] = $tmp->getSponsor();

            $attractions[] = $entry;
        }    
        $this->view->attractions = $attractions;

        $this->view->entries  = $result;   

        //var_dump($result);     
    }    

    public function addnewAction()
    {
        //$requests = $this->getRequest()->getPost();
        //var_dump($requests);
        $language = $this->_getParam("language");
        $this->view->langSels = $this->getLangSels($language);
       
        $getCate = $this->_getParam("category");
        $cateSels = $this->getCateSels($getCate);
        $this->view->cateSels = $cateSels;
        
        $advertisers = $this->getAdvertisers();
        $noadvertiser = "none";
        $advertisers[] = $noadvertiser;
        //$this->view->advertisers = $advertisers;
        
        $this->view->advertisers = $this->getAdvertiserOptions($advertisers, $noadvertiser);
    }
    
    public function deleteAction()
    {   // delete records
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(TRUE);        
        
        $requests = $this->getRequest()->getPost();
        if(0)
        {        
            var_dump($requests);
            return;
        }
        
        $language = $this->_getParam("language", "English"); 
        foreach($requests as $key => $value)
        {
            if($value == 'true' && $key)
            {
                $langobj = $this->em->getRepository('\Synrgic\Language')->findOneBy(array('name'=>$language));
                $data = $this->attrdata->findOneBy(array('language' => $langobj, 'id' => $key));
                if($data)
                {    
                    //var_dump($data);
                    $this->em->remove($data);
                }                
            } 
        }
        $this->em->flush();
    }

    public function editAction()
    {
        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            if(1) return;
        }

        $id = $this->_getParam("id");
        $data = $this->attrdata->findOneBy(array('id' => $id));
        if(!$data)
        {
            echo "Got data issue, please check DB.";
            return;
        }    

        $language = $data->getLanguage();
        $langSels = $this->getLangSels($language);
        $this->view->langSels = $langSels;

        //$getCate = $this->_getParam("category");
        $getCate = $data->getType();    
        $cateSels = $this->getCateSels($getCate);
        $this->view->cateSels = $cateSels;

        //$this->view->category = $category;
        $this->view->content = $data->getDescription();

        //$this->view->latitude = $data->getLatitude();
        //$this->view->longitude = $data->getLongitude();
        $this->view->title = $data->getTitle();
        $this->view->id = $data->getId();
        $this->view->address = $data->getAddress();
        $this->view->sponsor = $data->getSponsor();

        $advid = $data->getAdvertiser();
        $advertisers = $this->getAdvertisers();
        $noadvertiser = "none";
        $advertisers[] = $noadvertiser;
        
        $advertiserobj = $this->advertiserdata->findOneBy(array('id' => $advid));
        if($advertiserobj)
        {
            $seladvertiser = $advertiserobj->getName();    
        }
        else
        {
            $seladvertiser = $noadvertiser;
        }
       
        $this->view->advertisers = $this->getAdvertiserOptions($advertisers, $seladvertiser);

        // date/time
        $this->view->start = $data->getStart() ? $data->getStart()->format('m/d/Y H:i:s') : "";
        $this->view->stop = $data->getStop() ? $data->getStop()->format('m/d/Y H:i:s') : "";
        //echo "start=" . $this->view->start;

    }

    public function tinymcesubmitAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        if(0)
        {    
            $requests = $this->getRequest()->getPost();
            var_dump($requests);
            if(1) return;
        }

        $address = $this->_getParam("address");
        /*
        $location = $this->geocode($address);
        if(count($location) == 0)
        {
            echo "Address/Google Maps error, please check.";
            return;
        }
        else
        {
            //var_dump($location);
            $latitude = $location['lat'];
            $longitude = $location['lng'];
        }    
        */
        $latitude = $this->_getParam("latitude");
        $longitude = $this->_getParam("longitude");

        // TODO: data error handle
        $id = $this->_getParam("id");    
        $mode = $this->_getParam("modehidden");
        $title = $this->_getParam("title");    
        $language = $this->_getParam("language");
        $category = $this->_getParam("category");                  
        $startdate = $this->_getParam("startdate"); 
        $stopdate = $this->_getParam("enddate");  
        $sponsor = $this->_getParam("sponsor"); 
        $advertiser = $this->_getParam("advertiser"); 
        $description = $this->_getParam("data"); 
        $distance = intval($this->_getParam("distance"));  

        $langobj = $this->em->getRepository('\Synrgic\Language')->findOneBy(array('name'=>$language));
        $adobj = $this->em->getRepository('\Synrgic\Adverts\Advertiser')->findOneBy(array('name' => $advertiser));

        $data = null;
        if($mode == "Edit")
        {
            $data = $this->attrdata->findOneBy(array('id' => $id));
            echo "Data updated!\n";                         
        }
        else
        {
            $data = new \Synrgic\LocalAttractions\Attractiondata();
            echo "Data stored!"; 
        }

        $data->setTitle($title);    
        $data->setType($category);            
        $data->setLanguage($langobj);    
        $data->setDescription($description);
        $data->setAddress($address);
        $data->setLatitude($latitude);
        $data->setLongitude($longitude);
        $data->setSponsor($sponsor);
        $data->setAdvertiser($adobj);         
    
        $datetime = new DateTime($startdate);                
        $data->setStart($datetime);    
        $datetime = new DateTime($stopdate);            
        $data->setStop($datetime);
                
        $level = $this->calcLevel($langobj, $category);
        $data->setLevel($level);
        $data->setDistance($distance);
    
        $this->em->persist($data);
        try {
            $this->em->flush(); 
        } catch (Exception $e){
            var_dump($e);
            //return;
        }     
    }

    private function calcLevel($langobj, $category)
    {
        $qb = $this->em->createQueryBuilder();
        
        $qb->select('a.level')
            ->from('\Synrgic\LocalAttractions\Attractiondata', 'a')
            ->where('a.language = ?1 AND a.type = ?2')
            ->setParameters(array(1 => $langobj->getId(), 2 => $category))
            ->orderBy('a.level', 'DESC');
        
        $result = $qb->getQuery()->getResult(); 
        //var_dump($result);

        $level = (count($result) > 0) ? ($result[0]["level"]+1) : 1;           
        //echo "level=$level"; 

        return $level;           
    }    

    function uponepositionAction()
    {
        $this->_helper->layout->disableLayout();  
        $this->_helper->viewRenderer->setNoRender(TRUE); 
        $id = $this->_getParam("id");    
        //echo "id=" . $id;

        // find itself
        $data = $this->attrdata->findOneBy(array('id' => $id));
        $dlevel = $data->getLevel();
        if($dlevel == 1)
        {// already top
            echo "already top";
            return;
        }
    
        $cate = $data->getType();
        $lang = $data->getLanguage();
        //echo "cate=" . $cate . ",lang=" . $lang;
        
        $exchangedata = $this->attrdata->findOneBy(array('language' => $lang, 'type' => $cate, 'level'=>$dlevel-1));

        $data->setLevel($dlevel-1);
        $this->em->persist($data);
               
        $exchangedata->setLevel($dlevel);
        $this->em->persist($exchangedata);

        $this->em->flush();  
    }

    function downonepositionAction()
    {
        $this->_helper->layout->disableLayout();  
        $this->_helper->viewRenderer->setNoRender(TRUE); 
        $id = $this->_getParam("id");    
        //echo "id=" . $id;

        // find itself
        $data = $this->attrdata->findOneBy(array('id' => $id));
        $dlevel = $data->getLevel();
        $cate = $data->getType();
        $lang = $data->getLanguage();

        $dataarray = $this->attrdata->findBy(array('language' => $lang, 'type' => $cate));
        $flag = false;
        foreach($dataarray as $tmpdata)
        {
            $tmplevel = $tmpdata->getLevel();
            if($dlevel < $tmplevel)
            {
                $flag = true;
                break;
            }
        }
        if(!$flag)
        {// already bottom
            echo "already bottom";
            return;
        }
            
        $exchangedata = $this->attrdata->findOneBy(array('language' => $lang, 'type' => $cate, 'level'=>$dlevel+1));

        $data->setLevel($dlevel+1);
        $this->em->persist($data);
               
        $exchangedata->setLevel($dlevel);
        $this->em->persist($exchangedata);

        $this->em->flush();  
    }

    function uptotopAction()
    {
        $this->_helper->layout->disableLayout();  
        $this->_helper->viewRenderer->setNoRender(TRUE); 
        $id = $this->_getParam("id");    
        //echo "id=" . $id;

        // find itself
        $data = $this->attrdata->findOneBy(array('id' => $id));
        $dlevel = $data->getLevel();
        $cate = $data->getType();
        $lang = $data->getLanguage();

        if($dlevel == 1)
        {// already top
            echo "already top";
            return;
        }

        $dataarray = $this->attrdata->findBy(array('language' => $lang, 'type' => $cate));
        foreach($dataarray as $tmpdata)
        {
            $tmplevel = $tmpdata->getLevel();
            if($dlevel > $tmplevel)
            {
                $tmpdata->setLevel($tmplevel+1);  
                $this->em->persist($tmpdata);                  
            }
        }                        
        $data->setLevel(1);
        $this->em->persist($data);
        $this->em->flush();          
    }

    function downtobottomAction()
    {
        $this->_helper->layout->disableLayout();  
        $this->_helper->viewRenderer->setNoRender(TRUE); 
        $id = $this->_getParam("id");

        // find itself
        $data = $this->attrdata->findOneBy(array('id' => $id));
        $dlevel = $data->getLevel();
        $cate = $data->getType();
        $lang = $data->getLanguage();       
        $dataarray = $this->attrdata->findBy(array('language' => $lang, 'type' => $cate));
        
        foreach($dataarray as $tmpdata)
        {
            $tmplevel = $tmpdata->getLevel();
            if($dlevel < $tmplevel)
            {
                $tmpdata->setLevel($tmplevel-1);
                $this->em->persist($tmpdata);
            }
        }
        $count = count($dataarray);
        $data->setLevel($count);                            
        $this->em->persist($data);
        $this->em->flush(); 
    }

    private function geocode($address)
    {// http://ygamretuta.me/2011/03/07/google-maps-v3-geocoding-with-pure-php/
        
        $address = urlencode($address);
        $url = "http://maps.google.com/maps/geo?output=xml&q=$address";

        $coords = array('lat' => 0, 'lng' => 0);
     
        // load file from url 
        try
        {
          $xml = simplexml_load_file($url);
        }
        catch(Exception $e)
        {
          // return an empty array for a file request exception
          return array();
        }

        //get response status
        $status = $xml->Response->Status->code;

        if (strcmp($status, '200') == 0)
        {         
          // get coordinates node from xml response
          $coordsNode = explode(',', $xml->Response->Placemark->Point->coordinates);
          $coords['lat'] = $coordsNode[1];
          $coords['lng'] = $coordsNode[0];
        }
        else 
        //if (strcmp($status, 620) == 0)
        {
          return array();
        }

      return $coords;  
    }

    
}

