<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/**
 * The Page class provides common functionality that can be used
 * to render or parse a page entity object
 */
class Synrgic_Models_Page
{
    private $_page;
    private $_displayTitle;
    private $_tokenHelper=null;
    private $_language=null;

    public function __construct()
    {
        $this->_displayTitle = true;
    }

    public function setPage($page)
    {
        $this->_page = $page;

        return $this;
    }

    public function getPage()
    {
        return $this->_page;
    }

    public function setLanguage($language)
    {
        $this->_language = $language;

        return $this;
    }

    public function setDisplayTitle($state)
    {
        $this->_displayTitle = $state;

        return $this;
    }

    public function setTokenHelper($tokenHelper)
    {
        $this->_tokenHelper = $tokenHelper;

        return $this;
    }

    public function __toString()
    {
        return $this->render();
    }

    public function render()
    {
        $output = $this->_page->getContent();
        $output = $this->replaceTokens($output);
        return $output;
    }

    public function fromArray($data)
    {
        $this->_page = new \Synrgic\CMS\Page();
        $this->_page->setTitle($data['title']);
        $this->_page->setContent($data['content']);
    }

    public function toArray()
    {
        return array(
            'state'=>$this->_page->getState(),
            'title'=>$this->_page->getTitle(),
            'content'=>$this->_page->getContent());
    }

    private function replaceTokens($string)
    {
        $string=preg_replace('/%NAME%/','test',$string);
        return $string;
    }

}

?>

