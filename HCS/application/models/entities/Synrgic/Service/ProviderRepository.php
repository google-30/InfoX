<?php
/*-
 * Copyright (c) 2013 Synrgic Research Pte Ltd
 * All rights reserved
 *
 * Redistribution of this file in source code, or binary form is
 * expressly not permitted without prior written approval of
 * Synrgic Research Pte Ltd
 */

namespace Synrgic\Service;

use Doctrine\ORM\EntityRepository;

class ProviderRepository extends EntityRepository 
{
    public function getProviderNameList()
    {
	$Provider = $this->_em->getRepository ( '\Synrgic\Service\Provider' )->findAll();
	$list = array();
	foreach($Provider as $p )
	    $list[$p->getId()] = $p->getName();

	if(empty($list))
	    $list[SYNRGIC_Provider_DEFAULT] = 'Default';

	return $list;
    }
}
