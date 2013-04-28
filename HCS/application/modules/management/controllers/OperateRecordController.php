<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

class Management_OperateRecordController extends Zend_Controller_Action
{

    private $em;

    public function init ()
    {
        /* Initialize action controller here */
        $this->em = Zend_Registry::get('em');
    }

    public function indexAction ()
    {
        $data = $this->em->getRepository('\Synrgic\Service\Operate_record')->findAll();
        $this->view->message = $this->_getParam('_msg');

		// Read page begin
		$req = $this->getRequest();
        $totalpage = count($data);
		$mypage = $req->getParam('page');

		if ((! $mypage) || ($mypage <= 0)) {
			$mypage = 0;
		}
		$limit = 15;
		$start = $mypage * $limit;

		if (($start + $limit) >= $totalpage) {
			$this->view->endpage = 1;
		} else {
			$this->view->endpage = 0;
		}

		$this->view->page = $mypage;
		$data = $this->em->getRepository('\Synrgic\Service\Operate_record')->pageView($start, $limit);
        // Page read end

        $this->view->data = $data;
        $userName=NULL;
        foreach($data as $r)
        {
        	$User= $this->em->getRepository('\Synrgic\User')->findOneBy(array('id'=>$r->getUser_id()));
        	$userName[$r->getId()]=	$User->getUsername();
        }
        $this->view->userName = $userName;
    }

    public function cleanAction ()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->delete('Synrgic\Service\Operate_record', 'b');
        $qb->getQuery()->getResult();
        $this->_forward('index', null, null,array('_msg' => "Operate record clean successfully!"));
    }
}

