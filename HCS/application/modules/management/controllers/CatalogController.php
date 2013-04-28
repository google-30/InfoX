<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_CatalogController extends Zend_Controller_Action {

	private $em;
	private $lang;
	
	public function init() {
		
		$this->em = Zend_Registry::get ( 'em' );

		$auth=Zend_Auth::getInstance();
		$user=$auth->getIdentity();

		$this->lang = $user->getpreferredLanguage()->getlocale();
		if ($this->lang==NULL){
			$this->lang='en_US';
		}
		
	}
	
	public function indexAction() {
		
        if ( $this->hasParam('id')){
            $id = $this->getParam('id');
            $data = $this->em->getRepository('\Synrgic\Service\Catalog')->findBy(array('fid'=>$id));
        } else {
            $data = $this->em->getRepository ('\Synrgic\Service\Catalog')->getAllTopCatalogs();
        }

		$this->view->data=$data;
	}
	
	public function addAction() {
		$req = $this->getRequest ();
        $form = $this->getForm();
		
		if ($req->getPost () && $form->isValid($req->getPost())) {	

			if(!$this->isCatalogExist($form->getValue('name'))){
                $catalog = new \Synrgic\Service\Catalog();
                $catalog->fromArray($form->getValues());
                $mediaRepo = $this->em->getRepository('\Synrgic\Media');
                try {
                    $media = $mediaRepo->processUpload('icon');
                    $catalog->setIcon($media);
                    $catalog->setFid ( \Synrgic\Service\Catalog::TOP_CATALOG );
                    $this->em->persist ( $catalog );
                    $this->em->flush ();
                    $this->displayMsg('Add catalog successfully',"");
                    $this->_redirect ( "management/catalog/index/" );
                } catch( Exception $e ) {
                    $this->displayMsg('There was an error uploading the file: '.  $e->getMessages());
                }
			}
		}
        $this->view->form = $form;
	}
	
	public function editAction() {
		$req = $this->getRequest ();
		$id = $this->_getParam ( 'id' );
        $form = $this->getForm();
		$catalog = $this->em->getReference ( '\Synrgic\Service\Catalog', "$id" );
		if ($req->getPost () && $form->isValid($req->getPost())) {
            $values = $form->getValues();
            $catalog->fromArray($values);

            $mediaRepo = $this->em->getRepository('\Synrgic\Media');
            try {
                $media = $mediaRepo->processUpload('icon');
                $catalog->setIcon($media);
                $this->em->persist ( $catalog );
                $this->em->flush ();
				$this->displayMsg('Edit catalog successfully','');			
                $this->_redirect ( "management/catalog/index/" );
            } catch( Exception $e ) {
                $this->displayMsg('There was an error uploading the file: '.  $e->getMessages());
            }
		} else {
            $form->populate($catalog->toArray());
            $this->view->form = $form;
		}
	}
	
	public function translateAction() {
		$req = $this->getRequest ();
		
		$id = $this->_getParam ( 'catalogId' );
	
		$editData = $this->em->getReference ( '\Synrgic\Service\Catalog', "$id" );
		
		$changeLang = $this->_getParam ( 'lang' );
		
		if ($req->getPost ()) {
		    
		    $translateName=$this->_getParam ('translate');
		    		    		     
		        foreach($editData->getTranslate() as $r)
		        {
		            if($r->getLanguage()==$changeLang)
		            {
		                $isExists=true;
		                if($translateName<>NULL)
		                {
		                    if($translateName<>$r->getName())
		                    {    
		                        $r->setName($translateName);
		                        $this->em->persist ( $r );
		                        $this->em->flush ();
		                    } 
		                }
		                else{
		                    $r->setCatalog(NULL);		                    
		                    $this->em->remove ( $r );
		                    $this->em->flush ();
		                }       
		            }       
		        }
		        if((!$isExists)&&($translateName<>NULL))
		        {
		            $CatalogTranslate = new \Synrgic\Service\CatalogTranslate ();
		            		            
		            $CatalogTranslate->setName($translateName);
		            $CatalogTranslate->setLanguage($changeLang);
		            
		            $CatalogTranslate->setCatalog($editData);
		            
		            $editData->getTranslate()->add($CatalogTranslate);
		            		            
		            $this->em->persist ( $CatalogTranslate );
		            $this->em->persist ( $editData );
		            $this->em->flush ();
		              
		        }
		    $this->displayMsg('Translate catalog successfully',$editData->getName());
			$this->_redirect ( "management/catalog/index/" );
		} else {
		   
			$this->view->data=$editData;
			
			$languages = $this->em->getRepository('\Synrgic\Language')->findAll();
			
			$this->view->langData=$languages;
				
			if($changeLang<>NULL)
			{
			    $this->view->curLanguage=$changeLang;
			}
			else{
			    $this->view->curLanguage='en_US';
			}	
		}
	}

	public function deleteAction() {
		$req = $this->getRequest ();
		$catalogid = $req->getParam ( 'id' );

		$data = $this->em->getReference ( '\Synrgic\Service\Catalog', "$catalogid" );
		
		if(count($data->getservices()))
		{
			$this->displayMsg('This catalog has services,you could not delete it',$data->getName());
		}
		else{
			$this->displayMsg('Delete catalog successfully',$data->getName());
			
			$this->em->remove ( $data );
			$this->em->flush();
		}
		
                $this->_redirect ( "/management/catalog/index" );
	}

	public function isCatalogExist($name) {
		$existing = $this->em->getRepository('\Synrgic\Service\Catalog')->findOneByName($name);
		if( isset($existing)){
			$this->displayMsg('catalog is exist',$existing->getName());
			return '1';
		}
		else{
			return '0';
		}				
	}
	public function displayMsg($msg,$name) {
		$message=$this->view->translate($msg);
		$translateName=$this->view->translate('name');
		$this->_helper->flashMessenger->addMessage( $message.':'.$translateName.'='.$name);
	}		

    private function getForm()
    {
        $form = new Synrgic_Models_Form();
        $form->addTextField('name',true)
            ->addField('is_display','Checkbox',false)
            ->addFileField('icon',true)
            ->addSubmitButton()
            ->addCancelButton()
            ->setFormTemplate('/catalog/_form.phtml');

        return $form;
    }
}
