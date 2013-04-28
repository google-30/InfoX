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

use Doctrine\ORM\EntityRepository;

/**
 * Methods related to managing a device
 */
class DeviceRepository extends EntityRepository 
{
    /**
     * Generates a unique identifier that can be used to identify a device.
     * The identifier is a textual string
     *
     * @return A String that can be used to uniquely identify a device
     */
    public function generateUniqueID()
    {
	return session_id() . md5(time());
    }

    public function resetDeviceById($id)
    {
	$device =$this->_em->getReference('\Synrgic\Device',$id);
	if($device != null ){
	    $this->resetDevice ($device);
	}
    }

    /**
     * Reset the device so it reaquires a new session. This maintains the 
     * device pairing but causes the device to act as if it was newly started
     * after the pairing process
     */
    public function resetDevice(\Synrgic\Device $device)
    {
	// When deleting a device we must expire tnynhe device's session
	$currentsession = session_id();
	session_write_close();
	session_id($device->getSessionID());
	session_start();
	session_destroy();
	session_id($currentsession);
	session_start();
    }

    /**
     * Deletes the device and destroys any existing sessions that 
     * the device is using
     *
     * @param id The ID of the device to delete
     * @return true on a successful deletion, else false on error
     */
    public function deleteDevice($id)
    {
	$device = $this->_em->getReference('\Synrgic\Device', $id);

	if( isset($device)) {
	    $this->resetDevice($device);
	    $this->_em->remove($device);
	    $this->_em->flush();
	    return true;
	}
	return false;
    }

    /**
     * Updates the session ID associated with the device. The 
     * Session information is obtained from the environment
     *
     * @param Device The device to update
     */
    public function updateSession($device)
    {
	assert( isset( $device ));

	$device->setSessionID( session_id());
	$this->_em->persist($device);
	$this->_em->flush();
    }

}

?>
