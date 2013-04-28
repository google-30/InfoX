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

define('UPLOAD_BASE', APPLICATION_PATH. '/data/uploads');
define('URI_BASE', '/uploads');

class MediaRepository extends EntityRepository 
{
    // Medianame to path realpath map
    private $pathMap=array(
        'media'   => '/media',
        'adverts' => '/adverts',
        'service' => '/service',
        );

    /**
     * Handle a file upload. This method takes a field name
     * and internal media prefix and handles the uploading of
     * a file into the media store. Upon a successful upload, 
     * a Media object is returned representing the file uploaded
     *
     * @param fieldname The name of the form field which represented the element
     * @param prefix The prefix to use internally (ie: adverts would return: adverts://filename)
     * @param extensions A valid list of file extensions (or null to accept all)
     */
    public function processUpload($fieldName, $prefix='media', $extensions=null)
    {
        // Determine a location for the internal representation
        $iPath = $prefix . '://';

        // Find the corresponding realpath on disk
        $relPath = "";
        foreach($this->pathMap as $key => $val){
            if( $key==$prefix){
                $relPath = $val;
                break;
            }
        }
        if($relPath === ""){
            throw new Exception("Invalid Upload Prefix given: $prefix");
        }

        $dirPath = $this->getRealPath($relPath);
        $realPath = $this->uploadFile($fieldName, $dirPath);
        $relPath = $this->getRelPath($realPath);
        $iPath.= pathinfo($relPath,PATHINFO_FILENAME);

        // Create the media object which will encapsulate the uploaded media
        $media = $this->createMedia($iPath,$relPath);

        return $media;
    }

    public function getFilePath($media)
    {
        return $this->getRealPath($media->getPath());
    }

    public static function getURI($media)
    {
        return URI_BASE . '/' . $media->getPath();
    }

    private function createMedia($iPath,$relPath) {
        $media = new \Synrgic\Media();
        $media->ipath = $iPath;
        $media->path = $relPath;

        $ext = pathinfo($relPath, PATHINFO_EXTENSION);

        $mediaType = $this->_em->getRepository('Synrgic\MediaType')->findOneBy(array('type'=>strtolower($ext)));
        $media->setMediaType($mediaType);
        $mediaType->getMedias()->add($media);
        $this->_em->persist($media);
        return $media;
    }
    private function getRelPath($realPath)
    {
        // +1 to remove '/';
        return substr($realPath,strlen(UPLOAD_BASE)+1);
    }

    private function getRealPath($relPath)
    {
        return UPLOAD_BASE . '/' . $relPath;
    }

    private function uploadFile($fieldName, $dirPath) 
    {
        $adapter = new \Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination($dirPath);

        // Set a new destination path and overwrites existing files
        $adapter->addFilter('Rename', array('target'=>$dirPath, 'overwrite'=>true));
        $fileName = $dirPath . '/';
        if($adapter->isValid()) {
            foreach($adapter->getFileInfo() as $file=>$info) {
                if( $file == $fieldName ){
                    $fileName.= $info["name"];
                    if($adapter->isUploaded($file)) {
                        try {
                            $adapter->addFilter('Rename', array('target'=>$fileName, 'overwrite'=>true),$file);
                            $adapter->receive($file);

                        }
                        catch(Exception $e) {
                            //file exists
                        }
                    }
                }
            }
        }

        return $fileName;
    }
}
