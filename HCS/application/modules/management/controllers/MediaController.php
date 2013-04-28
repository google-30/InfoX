<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_MediaController extends Zend_Controller_Action
{
    private $em = null;

    public function init()
    {
        $this->em = Zend_Registry::get('em');
    }

    public function indexAction()
    {
        $media = $this->em->getRepository('\Synrgic\Media')->findAll();
        $this->view->data = $media;
    }
}
