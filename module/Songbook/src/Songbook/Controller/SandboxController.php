<?php
namespace Songbook\Controller;
use Songbook\Entity\Song;
use Zend\Mvc\Controller\AbstractActionController;
use Doctrine\ORM\EntityManager;

/**
 *
 * @author manro
 */
class SandboxController extends AbstractActionController
{

    /**
     * @var DoctrineORMEntityManager
     */
    protected $em;

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


    public function testSimilarAction ()
    {
        $string = 'пусть не манит лучами своими звезда';

        $em = $this->getEntityManager();
        $user = $em->find('User\Entity\User', 1);
        // take all songs
        $songs = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findSimilar($string, array());

        var_dump($songs);
        exit;
    }

    public function testSearchAction ()
    {
        $string = $this->params('id', null);
        $songService = $this->getSongService();
        foreach ($songService->getCollectionByHeader($string, null, 10) as $song) {
            var_dump( $song->id, (( $song->favoriteHeader )? $song->favoriteHeader->content : $song->defaultHeader->content ), $song->title);
        }

        exit();
    }

    public function testCreateConcertItemAction()
    {
        $concertService = $this->getConcertService();

        // get current user
        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // get active profile
        $profileService = $this->getProfileService();
        $profile = $profileService->getCurrentByUser($user);

        $concert = $concertService->createConcert($profile);

        $em = $this->getEntityManager();
        $song = $em->find('Songbook\Entity\Song', 607);

        $concertService->createConcertItem($concert, $song);

        exit();
    }

    public function testLongAction()
    {
        $songService = $this->getSongService();
        $songs = $songService->getCollectionLongNotUsed();

        foreach($songs as $song){
            var_dump($song->defaultHeader->content);
            //var_dump($song->id);
        }

        return array();
    }

    public function testPopularAction()
    {
        $songService = $this->getSongService();
        var_dump($songService->getCollectionPopular());
        //exit;
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