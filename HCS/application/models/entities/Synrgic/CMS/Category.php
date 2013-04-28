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

/**
 * The Category class is a wrapper arounds the 'category' taxonomy
 */
class Category {

    private $taxonomy;

    public function getName()
    {
	return $this->taxonomy->getTerm()->getName();
    }

    public function getParent()
    {
	$parent = $this->taxonomy->getParent();
	if( $parent == null ){
	    return null;
	}

	$category = new Category();
	$category->taxonomy = $parent;
	return $category;
    }

    public function getTerm()
    {
	return $this->taxonomy->getTerm();
    }
}

?>
