<?php
/**
 * Created by PhpStorm.
 * User: manro
 * Date: 28.06.17
 * Time: 13:29
 */

namespace Songbook\Model;

use Songbook\Entity\Song;
use Songbook\Model\Cloud\CloudFile;
use Songbook\Model\Cloud\Driver\DriverInterface;
use Songbook\Service\Setting;
use Zend\ServiceManager\ServiceLocatorInterface;

class Cloud
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $sl;

    /**
     * @var DriverInterface
     */
    protected $driver;

    public function setDriver(DriverInterface $driver)
    {
        $driver->setCloud($this);
        $this->driver = $driver;
        return $this;
    }

    public function setServiceLocator (ServiceLocatorInterface $sl)
    {
        $this->sl = $sl;
        return $this;
    }

    public function getServiceLocator()
    {
        return $this->sl;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function getFile($name)
    {
        return $this->getDriver()->getFile($name);
    }

    public function getSongFolder(Song $song)
    {
        return $this->getDriver()->getFolder($song->id);
    }

    /**
     * @return CloudFile[]
     */
    public function getSongFiles(Song $song)
    {
        $folder = $this->getSongFolder($song);
        return $this->getDriver()->getFiles($folder);
    }

    public function uploadSongFile(Song $song, $fsPath, $name = null)
    {
        if(is_null($name)){
            $name = pathinfo($fsPath, \PATHINFO_FILENAME);
        }

        $folder = $this->getSongFolder($song);
        return $this->getDriver()->uploadFile($folder, $fsPath, $name);
    }

    public function downloadFile($fileId, $name, $folderFsPath)
    {
        // receiving content
        $content = $this->getDriver()->downloadFile($fileId);
        return file_put_contents($folderFsPath . '/' . $name, $content);
    }

    public function getFileMimeType($fsPath)
    {
        $finfo = finfo_open(\FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $fsPath);
        finfo_close($finfo);
        return $type;
    }

    public function oauth2Authenticate()
    {
        $params = func_get_args();
        $driver = $this->getDriver();

        return call_user_func_array(array($driver, 'oauth2Authenticate'), $params);
    }
}