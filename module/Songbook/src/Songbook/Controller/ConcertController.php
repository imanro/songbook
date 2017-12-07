<?php

namespace Songbook\Controller;

/**
 * @author manro
 */
class ConcertController extends FrontendController {

     /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Songbook\Service\Song
     */
    protected $songService;

    /**
     * @var \Songbook\Service\Content
     */
    protected $contentService;

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
     * Displaying of a set
     */
    public function listAction()
    {
        $concertService = $this->getConcertService();
        $data = $concertService->getCollectionByProfile();

        return array(
            'data' => $data
        );
    }

    /**
     * Displaying of a set
     */
    public function browseAction()
    {
        $id = $this->params('id', null);

        if(!$id){
            throw new \Exception('id param is mandatory');
        }

        $concertService = $this->getConcertService();
        $concert = $concertService->getById($id);

        $songService = $this->getSongService();
        $data = $songService->getCollectionByConcert($concert);

        return array(
            'data' => $data
        );
    }

    /**
     *
     */
    public function compositeAction()
    {
        $id = $this->params('id', null);

        $concertService = $this->getConcertService();
        $songService = $this->getSongService();

        if ($id) {
            $concert = $concertService->getById($id);

        } else {
            // get current user
            $userService = $this->getUserService();
            $user = $userService->getCurrentUser();

            // get active profile
            $profileService = $this->getProfileService();
            $profile = $profileService->getCurrentByUser($user);

            $concert = $concertService->getLastConcert($profile);
        }

        return array(
            'concert' => $concert,
            'songService' => $songService,
        );
    }

    public function contentProcessAction()
    {
        $id = $this->params('id', null);

        $concertService = $this->getConcertService();
        $songService = $this->getSongService();
        $contentService = $this->getContentService();

        if ($id) {
            $concert = $concertService->getById($id);

        } else {
            // get current user
            $userService = $this->getUserService();
            $user = $userService->getCurrentUser();

            // get active profile
            $profileService = $this->getProfileService();
            $profile = $profileService->getCurrentByUser($user);

            $concert = $concertService->getLastConcert($profile);
        }

        // we need: all songs from this concert with content here
        return array(
            'concert' => $concert,
            'songService' => $songService,
            'contentService' => $contentService
        );
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