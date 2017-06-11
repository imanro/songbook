<?php

namespace Songbook\Controller;

use Songbook\Entity\Song;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Doctrine\ORM\EntityManager;

/**
 * @author manro
 */
class ConcertController extends AbstractActionController {

     /**
     * @var DoctrineORMEntityManager
     */
    protected $em;

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
        // js requirements
        $this->getViewHelper('HeadScript')->appendFile('/assets/bower_components/jquery-ui/jquery-ui.min.js');
        $this->getViewHelper('HeadScript')->appendFile('/assets/bower_components/jquery-impromptu/dist/jquery-impromptu.min.js');
        $this->getViewHelper('HeadScript')->appendFile('/assets/bower_components/jquery-sortable/source/js/jquery-sortable-min.js');
        $this->getViewHelper('HeadLink')->appendStylesheet('/assets/bower_components/jquery-impromptu/dist/jquery-impromptu.min.css');

        $this->getViewHelper('HeadLink')->appendStylesheet('/assets/bower_components/jquery-ui/themes/base/jquery-ui.min.css');

        $this->getViewHelper('HeadScript')->appendFile('/assets/js/songbook/composite.js');

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

    /**
     * @param string $helperName
     */
    protected function getViewHelper ($helperName)
    {
        return $this->getServiceLocator()
            ->get('viewhelpermanager')
            ->get($helperName);
    }

}