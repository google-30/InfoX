<?php

/* -
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_DashboardController extends Zend_Controller_Action {

    private $em = null;
    private $user;
    private $acl;

    public function init() {
        $this->em = Zend_Registry::get('em');
        $this->_helper->layout()->setLayout('dashboard');
        $this->acl = Zend_Registry::get('acl');
        $auth = Zend_Auth::getInstance();
        $this->user = $auth->getIdentity();
    }

    public function indexAction() {
        $this->view->dashboard = $this->inflateMenuTree();
    }

    private function inflateMenuTree() {
        $navigation = Zend_Registry::get('navigation');
        $dashboard = array();
        $dashboard[] = array('label' => $this->view->translate(''), 'pages' => null);
        $appgroup = array();
        foreach ($navigation->getChildren() as $page) {
            if ($page->count() > 0) {
                //printf("%s<br>",$page->Label);
                $pages = array();
                $this->groupPages($page, $pages);
                if (empty($pages)) {
                    //no children, put it to its parent
                    $this->addPageToGroup($page, $appgroup);
                } else {
                    $dashboard[] = array('label' =>
                        $this->view->translate($page->Label), 'pages' => $pages);
                }
            } else {
                //printf("xxx %s\<br>", $page->Label);
                if ($page->Label !== 'Dashboard' && $page->isVisible()) {
                    $this->addPageToGroup($page, $appgroup);
                }
            }
        }
        $dashboard[0]['pages'] = $appgroup;
        return $dashboard;
    }

    private function addPageToGroup($page, &$group) {
        $icon = $page->icon;
        if (!isset($icon)) {
            $page->icon = '/images/lightsoff.png';
            $page->iconhover = '/images/lightson.png';
        }

        /*
         * ACL Debugging. Enable the below to view all notes with postfix
         * comment -benjsc 20130212
          if($page->getResource())
          $show = ($this->acl->isAllowed($this->user->role, $page->getResource(),$page->getPrivilege())==1)?"Y":"N";
          else
          $show="U";
         */

        if (($page->getResource() &&
                $this->acl->isAllowed($this->user->role, $page->getResource(), $page->getPrivilege())) ||
                isset($show)) {

            $page->Label = $this->view->translate($page->Label);
            if (isset($show))
                $page->Label.=$show;

            $group[] = $page;
        }
    }

    private function groupPages($container, &$group) {
        foreach ($container as $page) {
            if ($page->hasChildren()) {
                $this->groupPages($page, $group);
            } else {
                if ($page->isVisible()) {
                    $this->addPageToGroup($page, $group);
                }
            }
        }
    }

}
