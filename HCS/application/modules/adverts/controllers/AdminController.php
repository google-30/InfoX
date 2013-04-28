<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

/*-
 * An interface to the backend of advertisement management 
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 15/10/2012
 */

class Adverts_AdminController extends Zend_Controller_Action
{
    private $em;
    private $advertiserRepo;
    private $advertsRepo;
   
    public function init()
    {
        $this->em = Zend_Registry::get("em");
        $this->advertiserRepo = $this->em->getRepository('Synrgic\Adverts\Advertiser');
        $this->advertsRepo = $this->em->getRepository('Synrgic\Adverts\Adverts');
        $this->view->title = "Advertisement Management"; 
    }

    public function indexAction()
    {
        $cname = $this->getRequest()->getControllerName();
        $this->_helper->flashMessenger->clearMessages();
        $this->view->data = $this->advertiserRepo->findAll();
    }

    public function addAction() {
        $req = $this->getRequest();
        $form = $this->getAdvertiserForm()->setMethod('post');
        if($req->getPost() && $form->isValid($req->getPost())) {
            $obj = new \Synrgic\Adverts\Advertiser();
            $obj->fromArray($form->getValues(true));
            $this->em->persist($obj);
            $this->em->flush();
            $this->_gotoAction('index', 'Advertiser added successfully: id = '.$obj->getId());
        }
        else {
            $this->view->form = $form;
        }
    }

    public function editAction() {
        $req = $this->getRequest();
        $form = $this->getAdvertiserForm()->setMethod('post');
        $id = $this->_getParam('id');
        $obj = $this->advertiserRepo->find($id);
        if($req->getPost() && $form->isValid($req->getPost())) {
            $obj->fromArray($form->getValues(true));
            $this->em->persist($obj);
            $this->em->flush();
            $this->_gotoAction('index', 'Advertiser updated successfully: id = '.$obj->getId());
        }
        else {
            $form->populate($obj->toArray());
            $this->view->form = $form;
        }
    }

    public function deleteAction() {
       $id = $this->_getParam('id');
       if($this->advertiserRepo->deleteAdvertiser($id)) {
           $this->_gotoAction('index', 'Advertiser deleted successfully: id = '.$id);
       } 
       else {
           $this->_gotoAction('index');
       }
    }

    public function cancelAction() {
        $this->_gotoAction('index');
    }

    public function searchAdvertsAction() {
        $aid = $this->_getParam('aid'); 
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
        $this->_helper->flashMessenger->clearMessages();
        $this->view->data = $this->makeImageUrl($this->advertsRepo->findAdvertsByOwner($aid));
    }
    private function makeImageUrl($data) {
        foreach($data as $r) {
            $media = $r->getMedia();
            $r->_imageUrl = Synrgic_Models_Adverts_Util::getMediaUrl($media);
        }
        return $data;
    }

    public function addAdvertsAction() {
        $req = $this->getRequest();
        $aid = $this->_getParam('aid');
        $form = $this->getAdvertsForm($aid)->setMethod('post');
        if($req->getPost() && $form->isValid($req->getPost())) {
            $newfile = false;
            $obj = $this->advertsRepo->addAdverts($this->_getValuesFromForm($form, array('aid'=>$aid)), $newfile);
            if($newfile) {
                $this->_upload($obj->getMedia());
            }
            $this->em->getRepository('Synrgic\Adverts\Schedule')->updateSchedule($obj, 'add');
            $this->_gotoAction('search-adverts', 'Adverts added successfully: id = '.$obj->getId(), array('aid'=>$aid));
        }
        else {
            $this->view->form = $form;
        }
    	
    }

    public function editAdvertsAction() {
        $req = $this->getRequest();
        $aid = $this->_getParam('aid');
        $id = $this->_getParam('id');
        $form = $this->getAdvertsForm($aid)->setMethod('post');
        $obj = $this->advertsRepo->find($id);
        if($req->getPost() && $form->isValid($req->getPost())) {
            $newfile = false;
            $obj = $this->advertsRepo->updateAdverts($this->_getValuesFromForm($form, array('id'=>$id)), $newfile);
            if($newfile) {
                $this->_upload($obj->getMedia());
            } 
            $this->em->getRepository('Synrgic\Adverts\Schedule')->updateSchedule($obj, 'edit');
            $this->_gotoAction('search-adverts', 'Adverts updated successfully: id = '.$obj->getId(), array('aid'=>$aid));
        }
        else {
            $this->_populateAdvertsForm($form, $obj);
            $this->view->form = $form;
        }
    }
 
    public function deleteAdvertsAction() {
        $aid = $this->_getParam('aid');
        $id = $this->_getParam('id');
        $deleted = $this->_getParam('delete');
        if(!empty($deleted)) {
            //bulk delete here
            foreach($deleted as $id) {
                $obj = $this->advertsRepo->deleteAdverts($id);
                $this->em->getRepository('Synrgic\Adverts\Schedule')->updateSchedule($obj, 'delete');
            }
            $this->_gotoAction('search-adverts', 'Adverts deleted successfully', array('aid'=>$aid));
        }
        else if(isset($id)) {
            $obj = $this->advertsRepo->deleteAdverts($id);
            $this->em->getRepository('Synrgic\Adverts\Schedule')->updateSchedule($obj, 'delete');
            $this->_gotoAction('search-adverts', 'Adverts deleted successfully: id = '.$id, array('aid'=>$aid));
        }
        else {
            $this->_gotoAction('search-adverts', '', array('aid'=>$aid));
        }
    }
    
    public function cancelAdvertsAction() {
        $aid = $this->_getParam('aid');
        $this->_gotoAction('search-adverts', '', array('aid'=>$aid));
    }

    private function getAdvertiserForm() 
    {
        $form = new Synrgic_Models_Form();
        $form->addTextField("name", true)
             ->addTextField("contact")
             ->addSubmitButton()
             ->addCancelButton()
             ->setFormTemplate('admin/_form.phtml');
        return $form;
    }

    private function getAdvertsForm($aid)
    {
        $advertiser = $this->advertiserRepo->find($aid);
        if(!$advertiser) {
            //XXX, dtliu
            //should never be here. however at the moment,
            //there are some strage behavior on matrix.internal.synrgicresearch.com: 
            //$advertiser not found by id. 
            //just work around it. D2?
            $medias = array();
        }
        else {
            $medias = $advertiser->getMedias();
        }
        $mediaoptions = Synrgic_Models_Adverts_Util::toMultiOptions($medias, 'id', 'path', '0', $this->view->translate('Choose one media'));
        $form = new Synrgic_Models_Form(array('moduleName'=>$this->getRequest()->getModuleName()));
        $form->addTextField('startDate', true)
             //->addTextField("startTime", true)
             ->addTextField("endDate", true)
             //->addTextField("endTime", true)
             ->addTextField("duration", true)
             ->addSelectField("permanent", false, array(
                     'class'=>'custom',
                     'data-role'=>'slider',
                     '_multioptions'=>array(0=>'Off', 1=>'On')
                     ))
             ->addTextField('size', true)
             ->addFileField('path', false, array('size'=>55))
             ->addTextField("clickUrl", false, array('placeholder'=>'Your favorite link'))
             ->addSelectField("media_id", false, array('_multioptions'=>$mediaoptions))
             ->addField("keywords", "Textarea", false, array('rows'=>2, 'placeholder'=>"Keywords separated by ','"))
             ->addField('mediaList', 'Hidden')
             ->addSubmitButton()
             ->addCancelButton('cancelAdverts')
             ->setFormTemplate('admin/_form-adverts.phtml');
       
        // XXX: fix a charge model currently.
        // ideally we should can assign a charge model for each adverts, 
        // however, this needs a very flexible charge model management
        // with charge rule management.
        $form->addHiddenField('charge_model_id', 1);
        $mode = $form->createField("playMode", "Radio", true, array(
                     'separator'=>'',
                     '_multioptions'=>array(
                         'cycle'=>$this->view->translate('Cycle'), 
                         'popup'=>$this->view->translate('Popup'),
                     )));
        $mode->setValue('cycle');
        $form->getElement('path')->setAttrib('size', 30);
        
        $today = new DateTime();
        $form->setAttrib('enctype', 'multipart/form-data');
        $form->getElement('duration')->setValue(1);
        $form->getElement('size')->setValue(1);
        $form->getElement('startDate')->setValue($today->format('Y-m-d H:i:s'));
        $form->getElement('endDate')->setValue($today->format('Y-m-d H:i:s'));
        //$form->getElement('startDate')->setValue($today->format('Y-m-d'));
        //$form->getElement('endDate')->setValue($today->format('Y-m-d'));
        //$form->getElement('startTime')->setValue($today->format('H:i:s'));
        //$form->getElement('endTime')->setValue($today->format('H:i:s'));
        $form->getElement('mediaList')->setValue($this->_createMediaUrlList($medias));
        return $form;
    }
    private function _createMediaUrlList($medias) {
        $options = array();
        foreach($medias as $m) {
            $options[$m->getId()] = Synrgic_Models_Adverts_Util::getMediaUrl($m);
        }
        return Zend_Json::encode($options);
    }

    // a short hand to wrap a notification message
    private function _gotoAction($action, $msg='', $params=array()) {
       $this->_helper->flashMessenger->addMessage($msg);
       //$this->_forward($action, null, null, array('id'=>null));
       $this->_helper->getHelper('Redirector')->gotoSimple($action, null, null, $params);
    }

    private function _upload($media) {
        Synrgic_Models_Adverts_Util::uploadMediaFile($media);
    }

    private function _clearMediaFiles($medias) {
        foreach($medias as $m) {
            unlink(Synrgic_Models_Adverts_Util::getMediaFilePath($m));
        }
    }
    
    private function _populateAdvertsForm($form, $ad) {
        $adinfos = $ad->toArray();
        $adinfos['startDate'] = $ad->getStartDate()->format('Y-m-d H:i:s');
        $adinfos['endDate'] = $ad->getEndDate()->format('Y-m-d H:i:s');        
        //$adinfos['startDate'] = $ad->getStartDate()->format('Y-m-d');
        //$adinfos['endDate'] = $ad->getEndDate()->format('Y-m-d');
        //$adinfos['startTime'] = $ad->getStartTime()->format('H:i:s');
        //$adinfos['endTime'] = $ad->getEndTime()->format('H:i:s');
        $adinfos['media_id'] = $ad->getMedia()->getId();
        $form->populate($adinfos);
    }

    private function _getValuesFromForm($form, $extra = array()) {
        $data = $form->getValues(true);
        $data = array_merge($data, $extra);
        return $data;
    }
}

