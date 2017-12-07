<?php

namespace Songbook\Service;

use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity\Content as ContentEntity;
use Songbook\Model\Content\Service\Pool as ContentServicePool;
use Doctrine\Common\Collections\Criteria;
use ZendPdf;

class Content {
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface;
     */
    protected $sl;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Songbook\Model\Cloud
     */
    protected $cloudModel;

    /**
     * @return string
     */
    public static function getContentLinkServiceName(ContentEntity $content)
    {
        if($content->type !== ContentEntity::TYPE_LINK){
            throw new ContentException('Wrong content type given', ContentException::CODE_WRONG_CONTENT_TYPE);
        }

        if(strpos($content->content, 'youtube') !== false){
            return  ContentServicePool::SERVICE_YOUTUBE;
        } else if(strpos($content->content, 'godtube') !== false){
            return ContentServicePool::SERVICE_GODTUBE;
        } else {
            return null;
        }
    }

    /**
     * @return \Songbook\Model\Content\Service\AbstractService|null
     */
    public function getContentLinkService(ContentEntity $content)
    {
        $serviceName = self::getContentLinkServiceName($content);

        if(!is_null($serviceName)){
            return ContentServicePool::get($serviceName);
        } else {
            return null;
        }
    }

    /**
     * @return ContentEntity
     */
    public function getSongContentById($id)
    {
        // take content item
        $item = $this->getEntityManager()
            ->find('Songbook\Entity\Content', $id);

        /* @var $item ContentEntity */
        return $item;
    }

    /**
     * @return string|bool to downloaded content|false if it cannot be downloaded
     */
    public function downloadCloudContent(ContentEntity $content, $folderFsPath)
    {
        if($content->type !== ContentEntity::TYPE_GDRIVE_CLOUD_FILE){
            return false;
        }

        $cloudModel = $this->getCloudModel();

        $fileName = $content->file_name;
        if(empty($fileName)){
            $fileName = md5(microtime());
        }

        $bytes = $cloudModel->downloadFile($content->content, $fileName, $folderFsPath);

        if($bytes === false) {
            return false;
        } else {
            return $folderFsPath . '/' . $fileName;
        }
    }

    public function getSongContentsByIds(array $ids)
    {
        $repository = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Content');

        if(count($ids) > 0) {
            return $repository->findById($ids);
        } else {
            return array();
        }
    }

    /**
     * Removes Content
     *
     * @param ContentEntity
     */
    public function deleteContent (ContentEntity $content)
    {
        $this->getEntityManager()->remove($content);
        $this->getEntityManager()->flush();
        return true;
    }

    /**
     * @param ContentEntity[] $contents
     */
    public function pdfCompile(array $contents)
    {
        // creating temporary folder
        $rootFolderFsPath = $this->getServiceLocator()->get('Config')['paths']['tmp'];
        $folderFsPath = $rootFolderFsPath . '/' . 'cloudFiles_' . date('Y-m-j-H-i-s') . '_' . uniqid();

        // downloading pdf there
        if(!file_exists($folderFsPath)){
            if(mkdir($folderFsPath)){
                ;
            } else {
                throw new ContentException(vsprintf('Unable to create temporary folder "%s" for downloading cloud files', array($folderFsPath)), ContentException::CODE_UNABLE_TO_CREATE_TMP_FOLDER);
            }
        }

        $pdfCompiled = new ZendPdf\PdfDocument();

        foreach($contents as $content){
            if($content->type !== ContentEntity::TYPE_GDRIVE_CLOUD_FILE || $content->mime_type != 'application/pdf'){
                continue;
            }

            if(($fsPath = $this->downloadCloudContent($content, $folderFsPath)) !== false) {
                $pdf = ZendPdf\PdfDocument::load($fsPath);
                foreach ($pdf->pages as $page) {
                    $pdfExtract = clone $page;
                    $pdfCompiled->pages[] = $pdfExtract;
                }

                unset($pdf);
            }

        }

        array_map('unlink', glob("${folderFsPath}/*"));
        rmdir($folderFsPath);

        return $pdfCompiled->render();
    }

    public static function orderContentsByIds(array $contents, array $ids)
    {
        $contentsAssoc = array();
        foreach($contents as $content){
            $contentsAssoc[$content->id] = $content;
        }

        $contentsOrdered = array();
        foreach($ids as $id){
            if(isset($contentsAssoc[$id])){
                $contentsOrdered[] = $contentsAssoc[$id];
            }
        }

        return $contentsOrdered;
    }

    public function setServiceLocator (ServiceLocatorInterface $sl)
    {
        $this->sl = $sl;
    }

    public function getServiceLocator ()
    {
        return $this->sl;
    }

    /**
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager ()
    {
        if (null === $this->em) {
            $this->em = $this->getServiceLocator()->get(
                'doctrine.entitymanager.orm_default');
        }

        return $this->em;
    }

    protected function getCloudModel()
    {
        if(is_null($this->cloudModel)) {
            $sl = $this->getServiceLocator();
            $this->cloudModel = $sl->get('Songbook\Model\Cloud');
        }

        return $this->cloudModel;
    }
}