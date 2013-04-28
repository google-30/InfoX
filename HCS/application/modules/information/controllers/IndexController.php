<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Information_IndexController extends Zend_Controller_Action
{
    private $em;
    private $infodata;
    private $language;
    private $pagedata;
    private $pubpage;
    private $pubpageid;    
    private $_session;

    const GROUP_ID = 1; // TODO: group id should store in config/bootstrap
    private $pagegroup;   

    public function init()
    {
        $this->em = Zend_Registry::get('em');
        $this->infodata = $this->em->getRepository('Synrgic\Information\Infodata');

        $id = self::GROUP_ID;
        $result = $this->em->getRepository('\Synrgic\PageGroup')->findOneBy(array('id' => $id));
        if($result)
        {
            $this->pagegroup = $result;                                
        }
        else
        {// XXX: error handle
            echo "Information_IndexController, PageGroup error.\n";
        }

        
        $this->_session = Zend_Registry::get(SYNRGIC_SESSION);
        $this->language = $this->em->getRepository('\Synrgic\Language')->findOneBy(array('locale'=>$this->_session->language));
        $this->pagedata = $this->em->getRepository('\Synrgic\Page');
        $this->pubpage = $this->pagedata->findOneBy(array('language'=>$this->language, 'group'=>$this->pagegroup, 'state'=>1));
        $this->pubpageid = $this->pubpage->getId();
    }

    public function indexAction()
    {
        // published page    
        $pageObj = $this->pubpage;
        if(!$pageObj)
        {
            echo "No Contents Published Now.\n";
            return;
        }
        $pageid = $pageObj->getId();
        
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from('Synrgic\Information\Infodata', 'a')
            ->where('a.page = ?1')
            ->orderBy('a.level', 'ASC')
            ->setParameter(1, $pageid);
        $data = $qb->getQuery()->getResult();
        $this->view->data = $data; 
    }

    public function subcategoryAction()
    {
        $category = $this->_getParam("category");
        //echo "category=$category";

        $pageid = $this->pubpage->getId();
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from('Synrgic\Information\Infodata', 'a')
            ->where('a.page = ?1 AND a.title= ?2')
            ->orderBy('a.level', 'ASC')
            ->setParameters(array(1=>$pageid, 2=>$category));
        $data = $qb->getQuery()->getResult();
        $this->view->data = $data; 
  
    }

    public function searchresultAction()
    {
        $category = $this->getParam("category");
        $search = $this->getParam("searchmain");

        $this->view->search = $search;

        $pageid = $this->pubpage->getId();       
        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from('Synrgic\Information\Infodata', 'a')
            ->where('a.page = ?1')
            ->orderBy('a.level', 'ASC')
            ->setParameter(1, $pageid);
        $data = $qb->getQuery()->getResult();        

        $searchresult = array();        
        foreach($data as $tmp)
        {
            //var_dump($tmp);
            $content = $tmp->getContent();
            $text = $this->htmltotxt($content);
            //echo "$content<hr>"; echo "$text<hr>"; 

            $result = $this->searchresult($text, $search);
            if($result)
            {
                $newtext = $this->generatenewtext($text, $search);
                $newtext = $this->addcolor($newtext, $search);
            
                //$searchresult[$tmp["title"]] = $newtext; 
                $tmparray = array();
                $tmparray[0] = $newtext;
                $tmparray[1] = $tmp;
                $searchresult[] = $tmparray;
                //array_push($searchresult, $tmparray);
            }
        }
     
        //var_dump($searchresult);
        $this->view->search = $search;            
        $this->view->searchresult = $searchresult;         
    }

    private function htmltotxt($content)
    {//http://psoug.org/snippet/Convert-HTML-to-plain-text_36.htm
        // $document should contain an HTML document.
        // This will remove HTML tags, javascript sections
        // and white space. It will also convert some
        // common HTML entities to their text equivalent.
         
        $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
                         "'<[/!]*?[^<>]*?>'si",          // Strip out HTML tags
                         "'([rn])[s]+'",                // Strip out white space
                         "'&(quot|#34);'i",                // Replace HTML entities
                         "'&(amp|#38);'i",
                         "'&(lt|#60);'i",
                         "'&(gt|#62);'i",
                         "'&(nbsp|#160);'i",
                         "'&(iexcl|#161);'i",
                         "'&(cent|#162);'i",
                         "'&(pound|#163);'i",
                         "'&(copy|#169);'i",
                         "'&#(d+);'e");                    // evaluate as php
         
        $replace = array ("",
                         "",
                         "\1",
                         "\"",
                         "&",
                         "<",
                         ">",
                         " ",
                         CHR(161),
                         CHR(162),
                         CHR(163),
                         CHR(169),
                         "chr(\1)");
         
        $text = preg_replace($search, $replace, $content); 

        return $text;       
    }

    private function searchresult($content, $search)
    {
        return stristr($content , $search);
    }    

    private function generatenewtext($content, $search)
    {
        $gap = 50;
        $first = stripos($content, $search);
        $last = strripos($content, $search);

        if(!$last)
        {
            $last = $first + $gap;
        }    
        else
        {
            $last += $gap; //length($search);
        }        
        
        if($first > $gap)
    
        $first = ($first > $gap) ? ($first - $gap) : 0;
        return "... " . substr($content, $first, $last) . " ...";
    }    

    private function addcolor($content, $search)
    {
        $first = stripos($content, $search);
        $last = strripos($content, $search);

        $begin = '<font color="yellow">';
        $end  = '</font>';

        $newtext = "";
        $newtext .= substr($content, 0, $first);
        $newtext .= $begin . $search . $end;
        if(!$last)
        {
            $newtext .= substr($content, $first+strlen($search), strlen($content));
        }
        else
        {
            $newtext .= substr($content, $first+strlen($search), $last-$first-strlen($search));
            $newtext .= $begin . $search . $end;
            $newtext .= substr($content, $last+strlen($search), strlen($content)-$last-strlen($search));    
        }
        
        //echo "newtext=$newtext\n";
        return $newtext;        
    }

    public function infodetailAction()
    {
        $search = $this->getParam("search");
        $ids = $this->getParam("ids");
        $id = $this->getParam("id");
        //echo "search=$search,ids=$ids,id=$id";    

        $this->view->search = $search;
        $allids = explode(",", substr($ids, 0, strlen($ids)-1)); 

        // find the key
        $key = array_search($id, $allids); 
        $this->view->displayinfo = $key+1;     
        //echo "displayinfo=" . $this->view->displayinfo;   
        $this->view->totalinfo = count($allids);

        // convert to integer       
        array_walk($allids, 'intval');
        //print_r($allids);        

        $qb = $this->em->createQueryBuilder();
        $qb->select('a')
            ->from('Synrgic\Information\Infodata', 'a')
            ->where($qb->expr()->andx(
                    $qb->expr()->in('a.id', $allids),$qb->expr()->in('a.page', $this->pubpageid) 
                ));
        $data = $qb->getQuery()->getResult();
        
        $this->view->data = array_reverse($data);                
    }
}

