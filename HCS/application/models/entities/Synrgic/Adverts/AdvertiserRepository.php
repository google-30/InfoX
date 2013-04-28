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
 * Advertiser Repository 
 *
 * author: Liu detang, liu.detang@synrgicresearch.com
 * date: 15/10/2012
 */

namespace Synrgic\Adverts;

use Doctrine\ORM\EntityRepository;
use \Synrgic_Models_Adverts_Util;

class AdvertiserRepository extends EntityRepository 
{
   /**
    * $advertiser should be a reference to Advertiser 
    */
   public function deleteAdvertiser($id) {
       $advertiser = $this->find($id);
       if(isset($advertiser)) {
           $this->_removeAdvertsOwnedBy($advertiser);
           $this->_em->remove($advertiser);
           $this->_em->flush();
           return true;
       }
       return false;
   }
   
   public function deleteAdvertsOwnedBy($id) {
       $advertiser = $this->find($id);
       if(issert($advertiser)) {
           $this->_removeAdvertsOwnerdBy($advertser);
           $this->_em->flush();
           return true;
       }    
       return false;
   }
   
   private function _removeAdvertsOwnedBy($advertiser) {
       $medias = $advertiser->getMedias();
       $adverts = $advertiser->getAdverts();
       foreach($adverts as $a) {
           foreach($a->getScheduleEntries() as $e) {
               $this->_em->remove($e);
           }
           $this->_em->remove($a);
       }
       foreach($medias as $m) {
           unlink(Synrgic_Models_Adverts_Util::getMediaFilePath($m));
           $this->_em->remove($m);
       }
   }
}

