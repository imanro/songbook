<?php
/**
 * Created by PhpStorm.
 * User: manro
 * Date: 29.06.17
 * Time: 12:47
 */

namespace Songbook\Model\Cloud\Driver;

use Songbook\Entity\Song;
use Songbook\Model\Cloud;


interface DriverInterface {

    /**
     * @param Cloud $cloud
     */
    public function setCloud(Cloud $cloud);

    public function getFiles($parentFolder);

    public function uploadFile($parentFolder, $content, $name);

    public function downloadFile($fileId);

    public function setRootFolderName($name);

    public function getFolder($name);

    public function getFile($name);

    public function oauth2Authenticate();
}