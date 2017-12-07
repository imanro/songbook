<?php

namespace Songbook\Controller;

use Ez\Api\Controller as ApiController;
use Ez\Api\Request;

class ConcertAjaxController extends ApiController
{

    /**
     * @var \Songbook\Service\Song
     */
    protected $songService;

    /**
     * @var \Songbook\Service\Concert
     */
    protected $concertService;

    /**
     * @var \User\Service\User
     */
    protected $userService;

    /**
     * @var \Songbook\Service\Profile
     */
    protected $profileService;

    /**
     * Create concert
     */
    public function createConcertAction ()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(array(
            'date' => true
        ));

        $request->validate();


        $dateTime = \DateTime::createFromFormat('d-m-Y', $request->getParam('date'));
        $timestamp = $dateTime->getTimestamp();

        // get current user
        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // get active profile
        $profileService = $this->getProfileService();
        $profile = $profileService->getCurrentByUser($user);

        $response = $this->getResponse();

        $concertService = $this->getConcertService();
        $concert = $concertService->createConcert($profile, array('time' => $timestamp));

        return $response->prepareData(array(
            'id' => $concert->id
        ));
    }

    public function createConcertItemAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(
                array(
                    'songId' => true,
                    'concertId' => true
                ));

        $request->validate();

        $songId = $this->getRequest()->getParam('songId');
        $concertId = $this->getRequest()->getParam('concertId');

        $concertService = $this->getConcertService();
        $songService = $this->getSongService();

        try {
            $song = $songService->getById($songId);
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        try {
            $concert = $concertService->getById($concertId);
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        try {
            $item = $concertService->createConcertItem($concert, $song);
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        $response = $this->getResponse();
        return $response->prepareData(array(
            'id' => $item->id
        ));
    }

    public function deleteConcertItemAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(
                array(
                    'id' => true
                ));

        $request->validate();

        $id = $request->getParam('id');
        $concertService = $this->getConcertService();

        try {
            $item = $concertService->getConcertItemById($id);
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        $concertService->deleteConcertItem($item);
        $response = $this->getResponse();

        return $response->prepareData(array('id' => $id));
    }

    public function reorderAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(
                array(
                    'concertId' => true,
                    'concertItemIds' => true
                ));

        $request->validate();

        $concertId = $request->getParam('concertId');
        $concertItemIds = $request->getParam('concertItemIds');

        if (! is_array($concertItemIds) || ! count($concertItemIds)) {
            throw new \Ez\Api\Exception(
                    'wrong request, concertItemIds must be non-empty array');
        }

        $concertService = $this->getConcertService();

        try {
            $concert = $concertService->getConcertById($concertId);
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        $concertService->reorderConcertItemsByIds($concert, $concertItemIds);
        $response = $this->getResponse();
        return $response->prepareData(array());
    }

    public function editConcertItemAction()
    {
    }

    public function createConcertGroupAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(
            array(
                'concertId' => true,
                'concertItemsIds' => true
            ));

        $request->validate();

        $concertId = $request->getParam('concertId');
        $concertItemsIds = $request->getParam('concertItemsIds');
        $concertGroupName = $request->getParam('concertGroupName');

        $concertService = $this->getConcertService();

        try {
            $concert = $concertService->getConcertById($concertId);
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        if (! is_array($concertItemsIds) || ! count($concertItemsIds)) {
            throw new \Ez\Api\Exception(
                'wrong request, concertItemsIds must be non-empty array');
        }

        $concertItems = $concertService->getConcertItemsByIds($concertItemsIds);

        if ( ! count($concertItems)) {
            throw new \Ez\Api\Exception(
                'such concert items was not found');
        }

        try {
            $concertGroup = $concertService->createConcertGroup($concert, $concertItems, $concertGroupName);
            $response = $this->getResponse();
            return $response->prepareData(array('concertGroup' => $concertGroup));
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }
    }

    public function deleteConcertGroupAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(
            array(
                'id' => true
            ));

        $request->validate();

        $id = $request->getParam('id');
        $concertService = $this->getConcertService();

        try {
            $item = $concertService->getConcertGroupById($id);
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        $concertService->deleteConcertGroup($item);
        $response = $this->getResponse();

        return $response->prepareData(array('id' => $id));
    }

    public function addConcertItemIntoConcertGroupAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(
            array(
                'concertItemId' => true,
                'concertGroupId' => true
            ));

        $request->validate();

        $concertService = $this->getConcertService();

        try {
            $concertItem = $concertService->getConcertItemById($request->getParam('concertItemId'));
        } catch(\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        try {
            $concertGroup = $concertService->getConcertGroupById($request->getParam('concertGroupId'));
        } catch (\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }

        $concertService->addConcertItemIntoConcertGroup($concertItem, $concertGroup);
        $response = $this->getResponse();
        return $response->prepareStatus();
    }

    public function deleteConcertItemFromConcertGroupsAction()
    {
        $request = $this->getRequest();
        $request->setRequiredMethod(Request::METHOD_POST);
        $request->setRequiredParams(
            array(
                'concertItemId' => true,
            ));

        $request->validate();

        $concertService = $this->getConcertService();

        try {
            $concertItem = $concertService->getConcertItemById($request->getParam('concertItemId'));
        } catch(\Exception $e) {
            throw new \Ez\Api\Exception($e);
        }


        $concertService->deleteConcertItemFromConcertGroups($concertItem);
        $response = $this->getResponse();
        return $response->prepareStatus();
    }

    /**
     * @return \Songbook\Service\Song
     */
    protected function getSongService ()
    {
        if (! $this->songService) {
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }

        return $this->songService;
    }

    /**
     * @return \Songbook\Service\Concert
     */
    protected function getConcertService ()
    {
        if (! $this->concertService) {
            $sm = $this->getServiceLocator();
            $this->concertService = $sm->get('Songbook\Service\Concert');
        }

        return $this->concertService;
    }

    /**
     * @return \User\Service\User
     */
    protected function getUserService ()
    {
        if (! $this->userService) {
            $sm = $this->getServiceLocator();
            $this->userService = $sm->get('User\Service\User');
        }

        return $this->userService;
    }


    /**
     * @return \Songbook\Service\Profile
     */
    protected function getProfileService ()
    {
        if (! $this->profileService) {
            $sm = $this->getServiceLocator();
            $this->profileService = $sm->get('Songbook\Service\Profile');
        }

        return $this->profileService;
    }


}