<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */
require_once 'Zend/Translate.php';

class Management_ServicesController extends Zend_Controller_Action {	
	private $em;
	private $uploadPath;
	private $picturePath;
	private $lang;
	
	public function init() {
		/* Initialize action controller here */
		$this->em = Zend_Registry::get ( 'em' );
		$this->uploadPath = APPLICATION_PATH . '/data/uploads/services-pic';
		$this->picturePath = '/services-pic/';
		
		$auth=Zend_Auth::getInstance();
		$user=$auth->getIdentity();
		$this->lang = $user->getpreferredLanguage()->getlocale();
		if ($this->lang==NULL){
			$this->lang='en_US';
		}
		
	}
	public function indexAction() {	
		
		$req = $this->getRequest ();

                if($this->hasParam('search')){
                    $search=$this->getParam ( 'search' );
                    $data = $this->em->getRepository ( '\Synrgic\Service\Service' )->searchByServicesName("$search");
                } else {
                    $catalogid = $this->getParam('serviceid');
                    $data = $this->em->getRepository ( '\Synrgic\Service\Service')->searchByCatalogTree($catalogid);
                }

                //HACK This should not be here:
                switch( $catalogid){
                    case '1': //Room Service
                        $this->view->addAction='add-food';
                        $this->view->editAction='edit-food';
                        break;
                    case '2': //Taxis
                        $this->view->addAction='add-taxi';
                        $this->view->editAction='edit-taxi';
                        break;
                    default:
                        $this->view->addAction='add-other';
                        $this->view->editAction='add-other';
                        break;
                }
                

		$this->view->lang = $this->lang;
		$this->view->data = $data;
				
	}
	public function deleteAction() {
				
                $id=$this->getParam('id');
                $services = $this->em->getReference ( '\Synrgic\Service\Service', "$id" );
                $services->setCategory ( NULL );
                $services->setProvider ( NULL );

                $this->displayMsg('Delete service successfully',$this->view->translate($services->getName()));
                $this->em->remove ( $services );
		$this->em->flush();
                $this->forward('index','services','management',array('serviceid'=>$this->getParam('serviceid')));
			
	}
	public function addFoodAction() {
		$req = $this->getRequest ();
		
		if ($req->getPost ()) {	
			
			$pictureDir = $this->uploadPath;			
			$picturePath =$this->uploadfile ($pictureDir);			
			if ($picturePath <> NULL) {
				if ($picturePath [orgPicture] <> NULL) {
					$orgpicPath = $this->picturePath . $picturePath [orgPicture];
				}
				if ($picturePath [icon] <> NULL) {
					$iconPath = $this->picturePath . $picturePath [icon];
				}
			} else {
				unset ( $orgpicPath );
				unset ( $iconPath );
			}
			
			$type=$req->getParam ( 'type' );
			
			$foods = new \Synrgic\Service\Service ();

			$has_quantity=$req->getParam ('has_quantity');
			if($has_quantity==NULL)
			{
				$has_quantity=0;
			}
			$has_remark=$req->getParam ('has_remark');
			if($has_remark==NULL)
			{
				$has_remark=0;
			}			
				
			$has_starttime=$req->getParam ('has_starttime');
			if($has_starttime==NULL)
			{
				$has_starttime=0;
			}
			$has_stoptime=$req->getParam ('has_stoptime');
			if($has_stoptime==NULL)
			{
				$has_stoptime=0;
			}	

			$isDraft = $req->getParam ( 'submitButton' );

			if($isDraft=="Save as draft")
				$isSale=0;
			else 
				$isSale=1;	
		
			
			$foods->setName ( $req->getParam ( 'foodName' ) );			
			$foods->setType ( $type );			
			$foods->setClick_count ( '0' );
			$foods->setIs_deleted ( '0' );			
			$foods->setPrice ( $req->getParam ( 'price' ) );
			$foods->setKey_words ( $req->getParam ( 'otherName' ) );		
			$foods->setOrg_picture ( $orgpicPath );			
			$foods->setIntroduction ( $req->getParam ( 'introduction' ) );			
			$foods->setIs_sale ($isSale);

			
			$foods->setHas_quantity($has_quantity);
			$foods->setHas_remark($has_remark);
			$foods->setHas_starttime($has_starttime);	
			$foods->setHas_stoptime($has_stoptime);
					
			if(($has_starttime)&&($req->getParam('starttime')<>NULL))
			{
				$foods->setStarttime(new \DateTime($req->getParam('starttime')));
			}
			
			if(($has_stoptime)&&($req->getParam('stoptime')<>NULL))
			{
				$foods->setStoptime(new \DateTime($req->getParam('stoptime')));
			}			
				
			if($type=='1')
				$categoryid = $req->getParam ( 'categoryid' );
			else
				$categoryid='3';
			
			$catalog = $this->em->getRepository ( '\Synrgic\Service\Catalog' )->findOneBy ( array ('id' => $categoryid ) );
			$foods->setCategory ( $catalog );
			$catalog->getServices ()->add ( $foods );
						
			$providerid=$req->getParam ('providerid');	
			$provider = $this->em->getRepository ( '\Synrgic\Service\Provider' )->findOneBy ( array ('id' => $providerid ) );
			$foods->setProvider ( $provider );
			$provider->getServices ()->add ( $foods );
						
			$this->em->persist ( $foods );
			$this->em->persist ( $catalog );
			$this->em->persist ( $provider );
			$this->em->flush ();
			
			//$this->displayMsg('Add Food/Drink successfully',$foods->getName());	
			$this->_redirect ( "management/services/index/p/$curPage" );
		} else {
			$TopCat = $this->em->getRepository ( '\Synrgic\Service\Catalog' )->getTopCatalogs();
			$TopCatalogs [0] = $TopCat [0];
			$TopCatalogs [1]= $TopCat [2];
		
			$data = $this->em->getRepository ( '\Synrgic\Service\Catalog' )->getSubTree ( '1' );
			for($i = 1; $i < count ( $data ); $i ++)
				$catalogData [$i - 1] = $data [$i];

			$provider=$this->em->getRepository ( '\Synrgic\Service\Provider')->findAll();
			
			$this->view->lang = $this->lang;
			$this->view->TopCatalogs=$TopCatalogs;
			$this->view->data = $catalogData;			
			$this->view->providerData=$provider;
		}
	}
	public function addOtherAction() {
		$req = $this->getRequest ();
		
		if ($req->getPost ()) {
			
			$pictureDir = $this->uploadPath;			
			$picturePath =NULL; //$this->uploadfile ( $pictureDir);			
			if ($picturePath <> NULL) {
				if ($picturePath [orgPicture] <> NULL) {
					$orgpicPath = $this->picturePath . $picturePath [orgPicture];
				}
				if ($picturePath [icon] <> NULL) {
					$iconPath = $this->picturePath . $picturePath [icon];
				}
			} else {
				unset ( $orgpicPath );
				unset ( $iconPath );
			}
			
			$services = new \Synrgic\Service\Service ();
			
			$isSale = $req->getParam ( 'isSale' );
			$services->setName ( $req->getParam ( 'servicesName' ) );
			$services->setType ( '3' );	
			$services->setClick_count ( '0' );
			$services->setIs_deleted ( '0' );
			$services->setIs_top ( 0 );
			$services->setIs_new ( 0 );
			$services->setTop_index ( '-1' );
			$services->setNew_index ( '-1' );			
			$services->setPrice ( $req->getParam ( 'price' ) );
			$services->setKey_words ( $req->getParam ( 'otherName' ) );			
			$services->setOrg_picture ( $orgpicPath );
			$services->setIcon ( $iconPath );			
			$services->setIntroduction ( $req->getParam ( 'introduction' ) );
			$services->setRemark ( $req->getParam ( 'remark' ) );			
			$services->setAdd_time ( new \DateTime ( 'now' ) );			
			$services->setIs_sale ( $isSale ? 1 : 0 );
			
			$catalog = $this->em->getRepository (
                            '\Synrgic\Service\Catalog' )->findOneBy ( array ('id' => $this->getParam('serviceid') ) );
			$services->setcategory ( $catalog );
			$catalog->getServices ()->add ( $services );
						
			$providerid=$req->getParam ('providerid');
			$provider = $this->em->getRepository ( '\Synrgic\Service\Provider' )->findOneBy ( array ('id' => $providerid ) );
			$services->setProvider ( $provider );
			$provider->getServices ()->add ( $services );
			
			$this->em->persist ( $services );
			$this->em->persist ( $catalog );
			$this->em->persist ( $provider );
			$this->em->flush ();
			
			//$this->displayMsg('Add Other Services successfully',$services->getName());
			$this->_redirect ( "management/services/index/serviceid/" .  $catalog->getId());
		}
		else {
                        $this->view->serviceId = $this->getParam('serviceid');
			$provider=$this->em->getRepository ( '\Synrgic\Service\Provider')->findAll();			
			$this->view->providerData=$provider;
		}
	}
	public function editFoodAction() {
		$req = $this->getRequest ();
		$curPage=$this->_getParam ( 'p' );
		if($curPage=="")
			$curPage=1;
		
		if ($req->getPost ()) {		
			$id = $this->_getParam ( 'id' );
			$foods = $this->em->getReference ( '\Synrgic\Service\Service', $id );
			
			$pictureDir = $this->uploadPath;			
			$picturePath = $this->uploadfile ( $pictureDir);			
			$orgpicPath = $foods->getorg_picture ();
			$iconPath = $foods->geticon ();			
			if ($picturePath <>NULL) {
				if ($picturePath [orgPicture] <> NULL) {
					$orgpicPath = $this->picturePath . $picturePath [orgPicture];
				}
				if ($picturePath [icon] <> NULL) {
					$iconPath = $this->picturePath . $picturePath [icon];
				}
			}
			
			$isDraft = $req->getParam ( 'submitButton' );
			
			if($isDraft=="Save as draft")
				$isSale=0;
			else
				$isSale=1;
			
			$type=$req->getParam ( 'type' );
			
			$has_quantity=$req->getParam ('has_quantity');
			if($has_quantity==NULL)
			{
				$has_quantity=0;
			}
			$has_remark=$req->getParam ('has_remark');
			if($has_remark==NULL)
			{
				$has_remark=0;
			}
			
			$has_starttime=$req->getParam ('has_starttime');
			if($has_starttime==NULL)
			{
				$has_starttime=0;
			}
			$has_stoptime=$req->getParam ('has_stoptime');
			if($has_stoptime==NULL)
			{
				$has_stoptime=0;
			}
			

			$isTop = $req->getParam ( 'isTop' );
			$isNew = $req->getParam ( 'isNew' );
			$isDeleted = $req->getParam ( 'isDeleted' );
			
			$foods->setName ( $req->getParam ( 'foodName' ) );			
			$foods->setKey_words ( $req->getParam ( 'otherName' ) );			
			$foods->setPrice ( $req->getParam ( 'price' ) );			
			$foods->setTop_index ( $req->getParam ( 'topIndex' ) );
			$foods->setNew_index ( $req->getParam ( 'newIndex' ) );			
			$foods->setOrg_picture ( "$orgpicPath" );
			$foods->setIcon ( "$iconPath" );			
			$foods->setIntroduction ( $req->getParam ( 'introduction' ) );
			$foods->setRemark ( $req->getParam ( 'remark' ) );			
			$foods->setIs_sale ( $isSale);			
			//$foods->setIs_top ( $isTop ? 1 : 0 );			
			$foods->setIs_new ( $isNew ? 1 : 0 );			
			$foods->setIs_deleted ( $isDeleted ? 1 : 0 );
			
			
			
			
			$foods->setHas_quantity($has_quantity);
			$foods->setHas_remark($has_remark);
			$foods->setHas_starttime($has_starttime);
			$foods->setHas_stoptime($has_stoptime);
				
			if(($has_starttime)&&($req->getParam('starttime')<>NULL))
			{
				$foods->setStarttime(new \DateTime($req->getParam('starttime')));
			}
				
			if(($has_stoptime)&&($req->getParam('stoptime')<>NULL))
			{
				$foods->setStoptime(new \DateTime($req->getParam('stoptime')));
			}
			
			$foods->setType ( $type );
			if($type=='1')
				$categoryid = $req->getParam ( 'categoryid' );
			else
				$categoryid='3';
			$catalog = $this->em->getRepository ( '\Synrgic\Service\Catalog' )->findOneBy ( array ('id' => $categoryid) );
			$foods->setCategory ( $catalog );
					
			$providerid=$req->getParam ('providerid');
			$provider = $this->em->getRepository ( '\Synrgic\Service\Provider' )->findOneBy ( array ('id' => $providerid ) );
			$foods->setProvider ( $provider );
			
			$this->em->persist ( $foods );
			$this->em->flush ();
			
			$this->displayMsg('Edit Food/Drink successfully',$foods->getName());
			$this->_redirect ( "management/services/index/serviceid/" .  $this->getParam('serviceid') );
		} else {
			$this->view->curPage = $curPage;
			$id = $this->_getParam ( 'id' );
			$foods = $this->em->getReference ( '\Synrgic\Service\Service', "$id" );
			$this->view->data = $foods;
			
			$data = $this->em->getRepository ( '\Synrgic\Service\Catalog' )->getSubTree ( '1' );
			for($i = 1; $i < count ( $data ); $i ++)
				$catalogData [$i - 1] = $data [$i];			
			$this->view->lang = $this->lang;			
			$this->view->catalogData = $catalogData;
			
			$TopCat = $this->em->getRepository ( '\Synrgic\Service\Catalog' )->getTopCatalogs();
			if($foods->getType()=='1')
				$TopCatalogs [0] = $TopCat [0];
			else if($foods->getType()=='3')
				$TopCatalogs [0]= $TopCat [2];
			$this->view->TopCatalogs=$TopCatalogs;
			
			$provider=$this->em->getRepository ( '\Synrgic\Service\Provider')->findAll();
			$this->view->providerData=$provider;
			
		}
	}	
	public function editTaxiAction() {
		$req = $this->getRequest ();
		
		if ($req->getPost ()) {
			$id = $this->_getParam ( 'id' );
			$taxi = $this->em->getReference ( '\Synrgic\Service\Service', $id );
			
			$pictureDir = $this->uploadPath;			
			$picturePath = $this->uploadfile ( $pictureDir);		
			$orgpicPath = $taxi->getOrg_picture ();
			$iconPath = $taxi->getIcon ();			
			if ($picturePath <>NULL) {
				if ($picturePath [orgPicture] <> NULL) {
					$orgpicPath = $this->picturePath . $picturePath [orgPicture];
				}
				if ($picturePath [icon] <> NULL) {
					$iconPath = $this->picturePath . $picturePath [icon];
				}
			}
			
			$isSale = $req->getParam ( 'isSale' );
			$isDeleted = $req->getParam ( 'isDeleted' );
						
			$taxi->setName ( $req->getParam ( 'taxiName' ) );			
			$taxi->setKey_words ( $req->getParam ( 'otherName' ) );			
			$taxi->setOrg_picture ( "$orgpicPath" );
			$taxi->setIcon ( "$iconPath" );			
			$taxi->setIntroduction ( $req->getParam ( 'introduction' ) );
			$taxi->setRemark ( $req->getParam ( 'remark' ) );			
			$taxi->setIs_sale ( $isSale ? 1 : 0 );			
			$taxi->setIs_deleted ( $isDeleted ? 1 : 0 );
			
			$providerid=$req->getParam ('providerid');
			$provider = $this->em->getRepository ( '\Synrgic\Service\Provider' )->findOneBy ( array ('id' => $providerid ) );
			$taxi->setProvider ( $provider );
			
			$this->em->persist ( $taxi );
			$this->em->flush ();
			
			$this->displayMsg('Edit Taxi successfully',$taxi->getName());
			$this->_redirect ( "management/services/index/serviceid/" .  $this->getParam("serviceid") );
		} else {
			$id = $this->_getParam ( 'id' );
			$taxi = $this->em->getReference ( '\Synrgic\Service\Service', "$id" );
			
			$this->view->data = $taxi;
			$provider=$this->em->getRepository ( '\Synrgic\Service\Provider')->findAll();
			$this->view->providerData=$provider;
		}
	}
	public function editServicesAction() {
		if ($req->getPost ()) {
			$id = $this->_getParam ( 'id' );
			$services = $this->em->getReference ( '\Synrgic\Service\Service', $id );
						
			$pictureDir = $this->uploadPath;			
			$picturePath = $this->uploadfile ( $pictureDir);		
			$orgpicPath = $services->getOrg_picture ();
			$iconPath = $services->getIcon ();			
			if ($picturePath <>NULL) {
				if ($picturePath [orgPicture] <>NULL) {
					$orgpicPath = $this->picturePath . $picturePath [orgPicture];
				}
				if ($picturePath [icon] <>NULL) {
					$iconPath = $this->picturePath . $picturePath [icon];
				}
			}
			
			$isSale = $req->getParam ( 'isSale' );
			$isDeleted = $req->getParam ( 'isDeleted' );
			
			$services->setName ( $req->getParam ( 'servicesName' ) );
			$services->setPrice ( $req->getParam ( 'price' ) );			
			$services->setKey_words ( $req->getParam ( 'otherName' ) );			
			$services->setOrg_picture ( "$orgpicPath" );
			$services->setIcon ( "$iconPath" );			
			$services->setIntroduction ( $req->getParam ( 'introduction' ) );
			$services->setRemark ( $req->getParam ( 'remark' ) );			
			$services->setIs_sale ( $isSale ? 1 : 0 );			
			$services->setIs_deleted ( $isDeleted ? 1 : 0 );
			
			$providerid=$req->getParam ('providerid');
			$provider = $this->em->getRepository ( '\Synrgic\Service\Provider' )->findOneBy ( array ('id' => $providerid ) );
			$services->setProvider ( $provider );
			
			$this->em->persist ( $services );
			$this->em->flush ();
			
			$this->displayMsg('Edit Other services successfully',$services->getName());			
			$this->_redirect ( "management/services/index/page/$curPage" );
		} else {
			$id = $this->_getParam ( 'servicesid' );
			$services = $this->em->getReference ( '\Synrgic\Service\Service', "$id" );
			
			$this->view->data = $services;
			$provider=$this->em->getRepository ( '\Synrgic\Service\Provider')->findAll();
			$this->view->providerData=$provider;
		}
	}	
	public function translateAction() {
	    $req = $this->getRequest ();
	    $curPage=$this->getCurPage($this->_getParam ( 'page' ));
	    $this->view->curPage = $curPage;
	    
	    $changeLang = $this->_getParam ( 'lang' );
	    
	    $id = $this->_getParam ( 'servicesid' );
	    $services = $this->em->getReference ( '\Synrgic\Service\Service', "$id" );
	    
	    if ($req->getPost ()) {        
	        $Name=$this->_getParam ('translateName');
	        $introduction=$req->getParam ( 'introduction' );
	        
	        $pictureDir = $this->uploadPath;    
	        $picturePath = $this->uploadfile ( $pictureDir);	         
	        if ($picturePath <>NULL) {
	        	if ($picturePath [orgPicture] <> NULL) {
	        		$orgpicPath = $this->picturePath . $picturePath [orgPicture];
	        	}
	        	if ($picturePath [icon] <> NULL) {
	        		$iconPath = $this->picturePath . $picturePath [icon];
	        	}
	        }else {
				unset ( $orgpicPath );
				unset ( $iconPath );
			} 
	        
	        if(($Name<>NULL)||($introduction<>NULL)||($picturePath <>NULL))
	        {
	            $inputNotEmpty=true;
	        }
	        else
	        {
	            $inputNotEmpty=false;
	        }
	       	        	
	        foreach($services->getTranslate() as $r)
	        {
	        	if($r->getLanguage()==$changeLang)
	        	{
	        		$isExists=true;
	        		if($inputNotEmpty)
	        		{
	        			if($Name<>$r->getName())
	        			{
	        				$r->setName($Name);	        				
	        			}
	        			if($introduction<>$r->getIntroduction())
	        			{
	        				$r->setIntroduction($introduction);
	        			}	
	        			if($orgpicPath<>NULL)
	        			{
	        			    $r->setOrg_picture($orgpicPath);
	        			}
	        			if($iconPath<>NULL)
	        			{
	        				$r->setIcon($iconPath);
	        			}
	        				        			
	        			$this->em->persist ( $r );
	        			$this->em->flush ();
	        		}
	        		else
	        		{  
	        		    if(($r->getOrg_picture()==NULL)&&($r->getIcon()==NULL))
	        		    {
	        			    $r->setService(NULL);
	        			    $this->em->remove ( $r );
	        			    $this->em->flush ();
	        		    }
	        		    else
	        		    {
	        		        $r->setName($Name);
	        		        $r->setIntroduction($introduction);
	        		        $this->em->persist ( $r );
	        		        $this->em->flush ();
	        		    }
	        		}
	        	}
	        }
	        if((!$isExists)&&$inputNotEmpty)
	        {
	        	$serviceTranslate = new \Synrgic\Service\ServiceTranslate ();	                	
	        	if($Name<>NULL)
	        	{
	        		$serviceTranslate->setName($Name);
	        	}
	        	if($introduction<>NULL)
	        	{
	        		$serviceTranslate->setIntroduction($introduction);
	        	}
	        	if($orgpicPath<>NULL)
	        	{
	        		$serviceTranslate->setOrg_picture($orgpicPath);
	        	}
	        	if($iconPath<>NULL)
	        	{
	        		$serviceTranslate->setIcon($iconPath);
	        	}	        	
	        	$serviceTranslate->setLanguage($changeLang);	        
	        	$serviceTranslate->setService($services);	        
	        	$services->getTranslate()->add($serviceTranslate);
	        
	        	$this->em->persist ( $serviceTranslate );
	        	$this->em->persist ( $services );
	        	$this->em->flush ();        
	        }
	        $this->displayMsg('Translate services successfully',$services->getName());
	    	$this->_redirect ( "management/services/index/page/$curPage" );
	    } else {		
	    	$this->view->data = $services;
	    	
	    	$languages = $this->em->getRepository('\Synrgic\Language')->findAll();
	    	$this->view->langData=$languages;

	    	if($changeLang<>NULL)
	    	{
	    		$this->view->curLanguage=$changeLang;
	    	}
	    	else{
	    		$this->view->curLanguage='en_US';
	    	}
	    	
	    	$this->view->curTranslateData=NULL;
	    	foreach($services->getTranslate() as $r){
	    		if ($r->getLanguage()==$this->view->curLanguage)
	    			$this->view->curTranslateData=$r;
	    	}	    	
	    }   
	}
		
	public function uploadfile($dir) {
		$adapter = new Zend_File_Transfer_Adapter_Http ();
		
		$adapter->setDestination ( $dir );
		
		$adapter->addValidator ( 'Extension', false, array (
				'gif',
				'jpeg',
				'jpg',
				'png',
		) );
		$adapter->addValidator ( 'Size', false, array (
				'min' => '1kB',
				'max' => '4MB' 
		) );
		$adapter->addValidator ( 'Count', false, 2 );
		$adapter->addValidator ( 'FilesSize', false, array (
				'min' => '1kB',
				'max' => '2MB' 
		) );
		
		$fileName = array ();
		
		$org_fileInfo = $adapter->getFileInfo ();
		
		foreach ( $org_fileInfo as $file => $info ) {
			if ($adapter->isUploaded ( $file )) {
				
				$ext = $this->getExtension ( $info ['name'] );
				$fileName [$file] = $file . '-' . date ( 'Ymd-His', time () ) . '.' . $ext;
				
				$adapter->addFilter ( 'Rename', $dir . '/' . $fileName [$file], $file );
				$adapter->receive ( $file );
			}
		}
		
		return $fileName;
	}
	public function getExtension($fileName) {
		$fileName = strtolower ( $fileName );
		$exts = split ( "[/\\.]", $fileName );
		$n = count ( $exts ) - 1;
		$exts = $exts [$n];
		return $exts;
	}	

	public function displayMsg($msg,$name) {
		$message=$this->view->translate($msg);
		$translateName=$this->view->translate('name');
		$this->_helper->flashMessenger->addMessage( $message.':'.$translateName.'='.$name);
	}	
}

