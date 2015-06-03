<?php

namespace Songbook\Controller;

use Ez\Api\Controller;
use Ez\Api\Request;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Doctrine\ORM\EntityManager;
use Ez\Api\Exception;

class ConcertAjaxController extends Controller
{

    /**
     * @var \User\Service\Song
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

        $date = $this->getRequest()->getParam('date');
        $dateTime = \DateTime::createFromFormat('d-m-Y', $request->getParam('date'));
        $timestamp = $dateTime->getTimestamp();

        // get current user
        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // get active profile
        $profileService = $this->getProfileService();
        $profile = $profileService->getCurrentByUser($user);

        $data = array(
            'time' => $timestamp
        );
        $response = $this->getResponse();
        try {
            $concertService = $this->getConcertService();
            $concert = $concertService->createConcert($profile, array('time' => $timestamp));

            return $response->prepareData(array(
                'id' => $concert->id
            ));

        } catch (Exception $e) {
            throw new Exception($e);
        }
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
        $concertService = $this->getConcertService();

        if (! is_array($concertItemIds) || ! count($concertItemIds)) {
            throw new \Ez\Api\Exception(
                    'wrong request, concertItemIds must be non-empty array');
        }

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

    /**
     * @return \Sonbook\Model\SongService
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
     * @return \Sonbook\Model\ConcertService
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
     * @return \Sonbook\Model\UserService
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
     * @return \Sonbook\Model\ProfileService
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