<?php

namespace Songbook\Controller;

use Ez\Api\Controller;
use Ez\Api\Request;
use Songbook\Entity\Content as ContentEntity;
use Zend\Http\Response as ZendHttpResponse;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;

class ContentAjaxController extends Controller
{
    /**
     * @var \Songbook\Service\Song
     */
    protected $songService;

    /**
     * @var \Songbook\Service\Content
     */
    protected $contentService;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    public function addContentAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(array(
            'song_id' => true,
            'content' => true,
            'type' => true,
        ));

        $request->validate();

        $songService = $this->getSongService();

        try {
            $song = $songService->getSongById($request->getParam('song_id'));
        } catch(\Exception $e){
            throw new \Ez\Api\Exception($e, null, null, ZendHttpResponse::STATUS_CODE_404);
        }

        $array = $request->getParams();
        $content = new ContentEntity();
        $content->exchangeArray($array);
        $content->song = $song;

        if($content->type == ContentEntity::TYPE_LINK && !$this->getContentService()->getContentLinkService($content)){
            throw new \Songbook\Model\Content\Exception('There is no service for provided link', \Songbook\Model\Content\Exception::CODE_LINK_DOES_NOT_MATCH_ANY_SERVICE);
        }

        $em = $this->getEntityManager();
        $em->persist($content);
        $em->flush();

        // the new way of responses
        $response->prepareData(array('content' => $content), ZendHttpResponse::STATUS_CODE_201);
        return $response;
    }

    public function removeContentAction()
    {
        $request = $this->getRequest();
        $response = $this->getResponse();

        $request->setRequiredMethod(Request::METHOD_DELETE);
        $contentId = (int) $this->params()->fromRoute('id', 0);

        $request->validate();

        $contentService = $this->getContentService();

        $content = $contentService->getSongContentById($contentId);

        if(is_null($content)){
            throw new \Ez\Api\Exception('Unable to find such content', null, null, ZendHttpResponse::STATUS_CODE_500);
        }

        $contentService->deleteContent($content);
        return $response->prepareStatus();
    }

    public function getContentLinkEmbedCodeAction()
    {
        $response = $this->getResponse();

        $contentId = (int) $this->params()->fromRoute('id', 0);

        $contentService = $this->getContentService();

        $item = $contentService->getSongContentById($contentId);
        $linkService = $contentService->getContentLinkService($item);
        $link = $linkService->getEmbedCode($item);

        $response->prepareData(array('link' => $link));
        return $response;
    }

    /**
     * @return \Songbook\Service\Song
     */
    protected function getSongService ()
    {
        if (is_null($this->songService)){
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }

        return $this->songService;
    }

    /**
     * @return \Songbook\Service\Content
     */
    protected function getContentService ()
    {
        if (is_null($this->contentService)){
            $sm = $this->getServiceLocator();
            $this->contentService = $sm->get('Songbook\Service\Content');
        }

        return $this->contentService;
    }

    /**
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
}

