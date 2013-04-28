<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\CMS;

use Doctrine\ORM\EntityRepository;

/**
 * CMS Repository management functions
 */
class CMSRepository extends EntityRepository 
{
    public function getAllPages($includeDeleted=false)
    {
        $pages = $this->findBy(array('parent'=>NULL,'type'=>\Synrgic\CMS\Page::PAGE));
        if( $includeDeleted )
            return $pages;

        $nonDeleted = array();
        foreach($pages as $page){
            if( $page->getState() != \Synrgic\CMS\Page::DELETED)
                $nonDeleted[]=$page;
        }
        
        return $nonDeleted;
    }

    public function save($page,$values,\Synrgic\User $user)
    {
        if( $page->getId() != NULL ){
            $revision = new \Synrgic\CMS\Page();
            $revision->setId(null);
            $revision->setParent($page);
            $revision->setType(\Synrgic\CMS\Page::REVISION);
            $revision->setState(\Synrgic\CMS\Page::INHERIT);
            $revision->setTitle($page->getTitle());
            $revision->setContent($page->getContent());
            $this->_em->persist($revision);
        }

        $page->fromArray($values);
        $page->setCreated(new \DateTime());

        //Reattach the user as they may not 
        //be from this call
        $user=$this->_em->merge($user);
        $page->setEditor($user);

        $this->_em->persist($page);
        $this->_em->flush();
    }

    public function remove($page)
    {
        $page->setState(\Synrgic\CMS\Page::DELETED);
        $this->_em->persist($page);
        $this->_em->flush();
    }

    public function getPastRevisions($page)
    {
        return $this->findBy(array( 'type'=>\Synrgic\CMS\Page::REVISION, 'parent'=>$page));
    }

    public function getLanguages($page)
    {
        $transRepo = $this->em->getRepository('\Synrgic\Translation');
        $entry = $transRepo->findOneBy(array('element_type'=>\Synrgic\Translation::PAGE,$page->getId()));

        // At this point we have a tuple with the translation group. Find all
        // the available entries in that group
        $tuples = $transRepo->findByGroup($entry->getGroup());

        $languages = array();
        $first=true;
        foreach($tuples as $tuple){
            $languages[] = $tuple->getLanguage();
        } 

        return $languages;
    }

    /*
       These methods are not complete yet but will
       be expaneded later on -benjsc 20130321
    public function getCategories($post)
    {
	$categories=array();
	foreach($this->terms as $term){
	    // Find the associated taxonomy
	    $taxonomy = XXX;
	    if( $taxonomy->getName() == 'category'){
		$category = new Category($taxonomy);
		$categories[]  = $category;
	    }
	}
	return $categories;
    }

    public function addCategory($category)
    {
	$term = $category->getTerm();
	$this->terms[]=$term;
    }
    */
}
