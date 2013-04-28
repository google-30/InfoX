<?php
/*-
 * Copyright (c) 2012 Synrgic Research Pte Ltd
* All rights reserved
*
* Redistribution of this file in source code, or binary form is
* expressly not permitted without prior written approval of
* Synrgic Research Pte Ltd
*/

namespace Synrgic;

/**
 * This entity setup is for Zend_Session_SaveHandler_DbTable
 * 
 * @Entity
 * @Table(name="session")
 */
class Session extends \Synrgic_Models_Entity
{
    /**
     * @Id @Column(type="string", name="session_id", length=32)
     */
    protected $sessionId;
    
    /**
     * @Id @Column(type="string", name="save_path", length=32)
     */
    protected $savePath;
    
    /**
     * @Id @Column(type="string", length=32)
     */
    protected $name;
    
    /**
     * @Column(type="integer")
     */
    protected $modified;
    
    /**
     * @Column(type="integer")
     */
    protected $lifetime;
    
    /**
     * @Column(type="text", name="session_data", nullable=true)
     */
    protected $sessionData;
}
