<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_CmsController extends Zend_Controller_Action
{
    private $_em;
    private $_pageRepo;
    private $_user;

    public function init()
    {
        $this->_em = Zend_Registry::get('em');
        $this->_pageRepo = $this->_em->getRepository('\Synrgic\CMS\Page');
        $this->_user = Zend_Auth::getInstance()->getIdentity();
    }

    public function indexAction()
    {
        $this->view->data=$this->_pageRepo->getAllPages();
    }

    public function addAction()
    {
        $request = $this->getRequest();
        $page = new \Synrgic\CMS\Page();
        $this->view->form = $this->getForm();
        $pageElement = $this->view->form->getElement('page');
        if( $request->getPost() && $this->view->form->isValid($request->getPost())){
            $values = $this->view->form->getValue('page');
            $this->_pageRepo->save($page,$values,$this->_user);
            $this->forward('index','cms','management');
        } else {
            $pageElement->setValue($page);
        }
    }

    public function editAction()
    {
        $id = $this->getParam('id');
        $request = $this->getRequest();
        $this->view->form = $this->getForm();
        $page = $this->_em->getReference('\Synrgic\CMS\Page',$id);
        if( $request->getPost() && $this->view->form->isValid($request->getPost())) {
            $values = $this->view->form->getValue('page');
            $this->_pageRepo->save($page, $values,$this->_user);
            $this->forward('index','cms','management');
        } else {
            $pageElement = $this->view->form->getElement('page');
            $pageElement->setPage($page);
        }
    }

    public function deleteAction()
    {
        $id = $this->getParam('id');
        $page = $this->_em->getReference('\Synrgic\CMS\Page',$id);
        $this->_pageRepo->remove($page);
        $this->forward('index','cms','management');
    }

    private function getForm()
    {
        $form = new Synrgic_Models_Form();
        $form->addPageField('page');
        $form->addSubmitButton();
        return $form;
    }

}

?>
