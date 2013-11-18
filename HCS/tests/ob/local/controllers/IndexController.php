<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Local_IndexController extends Zend_Controller_Action
{
    private $em;
    private $attractions;
    private $language;
    private $settings;
    private $nearbydistance;
    private $nearbyzoom;
    private $centeraddress;
    private $cityzoom;

    private $_session;

    public function init()
    {
        $this->em = Zend_Registry::get('em');
        $this->attractions = $this->em->getRepository('Synrgic\LocalAttractions\Attractiondata');
        $ajaxContext = $this->_helper->getHelper('AjaxContext');
        $ajaxContext->addActionContext('getattractions', 'json')->setAutoJsonSerialization(false);
        $ajaxContext->initContext();

        $this->_session = Zend_Registry::get(SYNRGIC_SESSION);
        $this->language = $this->em->getRepository('\Synrgic\Language')->findOneBy(array('locale'=>$this->_session->language));

        $this->settings = Zend_Registry::get('settings');
        $la = $this->settings->LocalAttractions;
        $this->nearbydistance = $this->settings->LocalAttractions->nearbydistance->Value;
        $this->nearbyzoom = $this->settings->LocalAttractions->nearbyzoom->Value;
        $this->centeraddress = $la->centeraddress->Value;
        $this->cityzoom = $la->cityzoom->Value;
    }

    public function getLangArray()
    {
        $languages = $this->em->getRepository('\Synrgic\Language')->findAll();
	    $langArray = array();
	    foreach($languages as $lang )
	        $langArray[$lang->getLocale()] = $lang->getName();

        return $langArray;         
    }   

    public function indexAction()
    {
        $featured = "";
        $attractions = $this->attractions->findBy(array('level'=>1, 'language'=>$this->language));

        $filtered = $this->filterByTime($attractions);           
        $filtered = $this->sortByLevel($filtered);
        
        $featured .= '<div data-role="collapsible-set" class="attractions-collapse">';
        $icount = 0;
        foreach($filtered as $tmp)
        {
            $title = $tmp->getTitle();
            $description = $tmp->getDescription();                        
            if(0 && ++$icount == 1)
            {
                $featured .= '<div data-role="collapsible" data-collapsed="false"><h3>';
            }
            else
            {
                $featured .= '<div data-role="collapsible"><h3>';
            }
            $featured .= $this->view->translate($title) . '</h3><p>' . $description  . '</p></div>';
        } 
        $featured .= "</div>";
        $this->view->featured = $featured; 
    }    

    public function getattractionsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);
        
        $category = $this->_getParam('category','all');
        if($category == "all")
        {
            $attractions = $this->attractions->findBy(array('language'=>$this->language)); 
        }
        else
        {
            $attractions = $this->attractions->findBy(array('language'=>$this->language, 'type'=>$category)); 
        }

        $filtered = $this->filterByTime($attractions);

        if($category != "all")
        {            
            $filtered = $this->sortByLevel($filtered);
        }

        $attrArray = $this->generateRawItems($filtered);     
        echo Zend_Json::encode($attrArray);
    }

    // provide setting data for google maps
    public function getsettingsAction()
    {
        $this->_helper->layout->disableLayout();   
        $this->_helper->viewRenderer->setNoRender(TRUE);

        $settings = array();
        $settings["nearbyzoom"] = $this->nearbyzoom;
        $settings["nearbydistance"] = $this->nearbydistance;
        $settings["centeraddress"] = $this->centeraddress;
        $settings["cityzoom"] = $this->cityzoom;

        echo Zend_Json::encode($settings);        
    }

    private function filterByTime($attractions)
    {
        $filtered = array();
        $nowdate = gettimeofday();
        $now = $nowdate["sec"];
        //echo "now=$now<br>";
   
        foreach($attractions as $tmp)
        {
            $startdate = $tmp->getStart();
            $stopdate = $tmp->getStop();
            $start = $startdate ? $startdate->getTimestamp() : 0;
            $stop = $stopdate ? $stopdate->getTimestamp() : 0;

            if($now < $start || ($now > $stop && $stop != 0))
            {
                continue;
            }
            
            if($now >= $start && ($now < $stop || $stop == 0))
            {
                $filtered[] = $tmp;
                //echo "level=" . $tmp->getLevel() . "\n";
            }                                       
        }

        //var_dump($filtered);    
        return $filtered;
    }    

    private function sortByLevel($attractions)
    {
        usort($attractions, array($this, 'twistedcmp'));
        //echo "sortByLevel\n";
        return $attractions;
    }   

    private function cmp($a, $b)
    {
        $alevel = $a->getLevel();
        $blevel = $b->getLevel();

        if ($alevel == $blevel) 
        {
            return 0;
        }

        return ($alevel < $blevel) ? -1 : 1;
    } 

    private function twistedcmp($a, $b)
    {
        $alevel = $a->getLevel();
        $blevel = $b->getLevel();

        // just in case: no level information 
        $alevel = (!$alevel) ? 1000 : $alevel;
        $blevel = (!$blevel) ? 1000 : $blevel;

        if ($alevel == $blevel) 
        {
            return 0;
        }

        return ($alevel < $blevel) ? -1 : 1;
    } 

    private function generateRawItems($attractions)
    {
        $items = array();
        foreach ($attractions as $attr)
        {
            $newItem = (object) NULL;
            $newItem->title = $attr->getTitle();
            $newItem->description = $attr->getDescription();
            $newItem->latitude = $attr->getLatitude();
            $newItem->longitude = $attr->getLongitude();
            $newItem->postcode = $attr->getPostcode();
            $newItem->address = $attr->getAddress();
            $newItem->sponsor = $attr->getSponsor();
            $items[] = $newItem;
        }
        return $items;
    }

    public function mapviewAction()
    {
        $categories = array(
            "all" => "All Categories",
            "Shopping" => "Shopping",
            "Culture" => "Culture",
            "Relax" => "Relax",
            "Amusement" => "Amusement",
        );
        $this->view->categories = $categories;
        $selCate = $this->_getParam('category', key($categories));
   
        $this->view->selCate = $selCate;             
        
        $distances = array( 
            "nearby" => "Show Nearby",
            "wholecity" => "Show The Whole City",
        );
        $this->view->distances = $distances;
        $selDist = $this->_getParam('distance', key($distances));
        $this->view->selDist = $selDist;  

        $center = $this->_getParam('center', "");
        $this->view->selcenter = $center;        
    }    

    public function listviewAction()
    {
        //http://stackoverflow.com/questions/6601122/google-map-api-v3-computedistancebetween-method-and-distance-in-metric-form   
        $categories = array(
            "Shopping" => "Shopping",
            "Culture" => "Culture",
            "Relax" => "Relax",
            "Amusement" => "Amusement",
        );
        $this->view->categories = $categories;
        $selCate = $this->_getParam('category', key($categories));   
        $this->view->selCate = $selCate;             
        
        $distances = array( 
            "nearby" => "Show Nearby",
            "wholecity" => "Show The Whole City",
        );
        $this->view->distances = $distances;
        $selDist = $this->_getParam('distance', key($distances));
        $this->view->selDist = $selDist;  
        
        $attractions = $this->attractions->findBy(array('language'=>$this->language, 'type'=>$selCate)); 
        $filtered = $this->filterByTime($attractions);      
        if($selDist == "nearby")
        {
            $filtered = $this->filterByDistance($attractions);                              
        }
        $filtered = $this->sortByLevel($filtered);

        $icount = 0;
        $html = ""; 
        foreach($filtered as $tmp)
        {
            $icount++;
            $title = $tmp->getTitle();            
            $description = $tmp->getDescription();

            if($icount == 1)
            {
                $html .= '<div data-role="collapsible" data-collapsed="false"><h3>';
            }
            else
            {
                $html .= '<div data-role="collapsible"><h3>';
            }
            $html .= $this->view->translate($title) . '</h3><p>' . $description  . '</p>';
            $mapurl = "mapview?distance=$selDist&category=$selCate&center=$title";
            $html .= '<a href="' . $mapurl . '" data-role="button" data-mini="true">Check this out on Map</a>';                        
            $html .= "</div>";
        }
        $this->view->attractions = $html;  
    } 

    private function filterByDistance($attractions)
    {
        $filtered = array();   
        foreach($attractions as $tmp)
        {
            $distance = $tmp->getDistance();
            if($distance=="")
            {
                continue;
            }
    
            if($distance <= $this->nearbydistance)
            {
                $filtered[] = $tmp;
            }                               
        }

        //var_dump($filtered);    
        return $filtered;                
    }    
}

