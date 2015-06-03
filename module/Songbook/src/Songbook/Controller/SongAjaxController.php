<?php

namespace Songbook\Controller;

use Ez\Api\Controller;
use Ez\Api\Request;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;

class SongAjaxController extends Controller
{

    /**
     * @var \User\Service\Song
     */
    protected $songService;

    public function searchByHeaderAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(array('term' => true));
        $request->validate();

        $term = $this->getRequest()->getParam('term');
        $songService = $this->getSongService();

        $array = array();

        foreach ($songService->getCollectionByHeader($term, null, 20) as $song) {
            $array []= array('id' => $song->id, 'title' => (( $song->favoriteHeader )? $song->favoriteHeader->content : $song->defaultHeader->content ));
        }

        return $this->getResponse()->prepareData($array);
    }

    public function getSongDataAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(array(
            'id' => true
        ));
        $request->validate();

        $id = $this->getRequest()->getParam('id');
        $songService = $this->getSongService();

        $response = $this->getResponse();

        try {
            $song = $songService->getById($id);
            $favoriteHeader = (( $song->favoriteHeader )? $song->favoriteHeader : $song->defaultHeader );
            $headersData = array();

            if ($favoriteHeader) {
                $headers = $songService->getSongContent($song,
                        \Songbook\Entity\Content::TYPE_HEADER);

                foreach ($headers as $header) {
                    if ($header->id != $favoriteHeader->id) {
                        $headersData[] = array(
                            'title' => $header->content
                        );
                    }
                }
            }

            //foreach($song->content
            $data = array(
                'id' => $song->id,
                'createTime' => $song->create_time,
                'favoriteHeader' => $favoriteHeader->content,
                'headers' => $headersData,
            );

            return $response->prepareData($data);

        } catch(\Exception $e){
            throw new \Ez\Api\Exception($e);
        }
    }

    public function getSuggestionLongNotUsedAction()
    {
        $songService = $this->getSongService();

        try {
            $songs = $songService->getCollectionLongNotUsed();
        } catch(\Exception $e){
            throw new \Ez\Api\Exception($e);
        }

        $array = array();

        foreach($songs as $song){
            $array []= array( 'id' => $song->id, 'title' => (( $song->favoriteHeader )? $song->favoriteHeader->content : $song->defaultHeader->content ));
        }

        $response = $this->getResponse();
        return $response->prepareData($array);
    }


    public function getSuggestionTopPopularAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(array(
            'limit' => true
        ));
        $request->validate();
        $limit = (int)$request->getParam('limit');
        $offset = (int)$request->getParam('offset');

        if(!$limit){
            $limit = 10;
        }

        if(!$offset){
            $offset = 0;
        }

        $songService = $this->getSongService();

        try {
            $items = $songService->getCollectionPopular(null, null, null, $limit, $offset);
        } catch(\Exception $e){
            throw new \Ez\Api\Exception($e);
        }

        $array = array();

        foreach ($items as $item) {
            $array[] = array(
                'id' => $item['entity']->id,
                'title' => (($item['entity']->favoriteHeader) ? $item['entity']->favoriteHeader->content : $item['entity']->defaultHeader->content),
                'lastPerformanceTime' => date('j.m.Y', $item['lastPerformanceTime']),
                'performancesAmount' => $item['performancesAmount'],
            );
        }

        $response = $this->getResponse();
        return $response->prepareData($array);
    }

    /**
     * @return \Sonbook\Service\Song
     */
    protected function getSongService ()
    {
        if (! $this->songService) {
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }

        return $this->songService;
    }

}