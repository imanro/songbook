<?php
namespace Songbook\Service;
use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity\Concert as ConcertEntity;

class Concert
{

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface;
     */
    protected $sl;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \User\Service\User
     */
    protected $userService;

    /**
     * @var \Songbook\Service\Song
     */
    protected $songService;

    /**
     * @var \Songbook\Service\Profile
     */
    protected $profileService;

    /**
     * @param int $id
     * @deprecated
     */
    public function getById ($id)
    {
        return $this->getConcertById($id);
    }

    /**
     * @param int $id
     * @throws \Exception
     * @return \Songbook\Entity\Concert
     */
    public function getConcertById ($id)
    {
        $em = $this->getEntityManager();
        $concert = $em->find('Songbook\Entity\Concert', $id);

        if (is_null($concert)) {
            throw new \Exception('Concert is not found');
        }

        return $concert;
    }


    /**
     * @param int $id
     *
     * @return \Songbook\Entity\ConcertItem
     */
    public function getConcertItemById ($id)
    {
        $em = $this->getEntityManager();
        $item = $em->find('Songbook\Entity\ConcertItem', $id);

        if (is_null($item)) {
            throw new \Exception('Item is not found');
        }

        return $item;
    }

    /**
     * @param int $id
     *
     * @return \Songbook\Entity\ConcertGroup
     */
    public function getConcertGroupById ($id)
    {
        $em = $this->getEntityManager();
        $item = $em->find('Songbook\Entity\ConcertGroup', $id);

        if (is_null($item)) {
            throw new \Exception('ConcertGroup is not found');
        }

        return $item;
    }

    /**
     * @param int $id
     */
    public function getConcertItemsByIds (array $ids)
    {
        $em = $this->getEntityManager();
        $items = $em->getRepository('Songbook\Entity\ConcertItem')->findById($ids);

        return $items;
    }

    public function deleteConcertItem(\Songbook\Entity\ConcertItem $item)
    {
        $em = $this->getEntityManager();
        $retval = $em->remove($item);
        $em->flush();
        return $retval;
    }

    public function getLastConcert(\Songbook\Entity\Profile $profile)
    {
        // find all concert items ordered by order, then id
        $em = $this->getEntityManager();

        $item = $em
            ->getRepository('Songbook\Entity\Concert')
            ->findOneBy(array('profile' => $profile), array('time' => 'DESC', 'id' => 'DESC'));

        return $item;
    }

    public function createConcert(\Songbook\Entity\Profile $profile, array $data = null)
    {
        $em = $this->getEntityManager();

        if(is_null($data)){
            $data = array();
        }

        $data['profile'] = $profile;

        if(empty($data['time']))
        {
            $data['time'] = time();
        }

        $concert = new \Songbook\Entity\Concert();
        $concert->exchangeArray($data);

        $em->persist($concert);
        $em->flush();

        return $concert;
    }

    /**
     * @param \Songbook\Entity\Concert $concert
     * @param array $concertItems
     * @param string $concertGroupName
     *
     * @return \Songbook\Entity\ConcertGroup
     */
    public function createConcertGroup(\Songbook\Entity\Concert $concert, array $concertItems, $concertGroupName = '')
    {
        $concertGroup = new \Songbook\Entity\ConcertGroup;

        $concertGroup->exchangeArray(array(
                'name' => $concertGroupName,
                'concert' => $concert,
                'concertItems' => $concertItems
            )
        );

        $em = $this->getEntityManager();

        $em->persist($concertGroup);

        foreach($concertItems as $concertItem){
            $concertItem->concertGroup = $concertGroup;
            $em->persist($concertItem);
        }

        $em->flush();

        return $concertGroup;
    }

    public function deleteConcertGroup(\Songbook\Entity\ConcertGroup $item)
    {
        $em = $this->getEntityManager();
        $em->remove($item);
        $em->flush();
        return true;
    }

    public function addConcertItemIntoConcertGroup(\Songbook\Entity\ConcertItem $concertItem, \Songbook\Entity\ConcertGroup $concertGroup)
    {
        $concertItem->concertGroup = $concertGroup;
        $em = $this->getEntityManager();
        $em->persist($concertItem);
        $em->flush();
        return $concertItem;
    }


    public function deleteConcertItemFromConcertGroups(\Songbook\Entity\ConcertItem $concertItem)
    {
        $concertItem->concertGroup = null;
        $em = $this->getEntityManager();
        $em->persist($concertItem);
        $em->flush();
        return $concertItem;
    }

    public function createConcertItem(\Songbook\Entity\Concert $concert, \Songbook\Entity\Song $song, array $data = null )
    {
        // create new concert item
        $em = $this->getEntityManager();
        $concertItem = new \Songbook\Entity\ConcertItem();

        if(is_null($data)){
            $data = array();
        }

        if(empty($data['order'])){
            $count = $this->getCountConcertItems($concert);
            $data['order'] = (int)($count + 1);
        }

        $concertItem->exchangeArray($data);

        $concertItem->concert = $concert;
        $concertItem->song = $song;

        $em->persist($concertItem);
        $em->flush();

        return $concertItem;
    }

    public function getCountConcertItems(\Songbook\Entity\Concert $concert)
    {
        $em = $this->getEntityManager();
        $rep = $em->getRepository('Songbook\Entity\ConcertItem');

        $qb = $rep->createQueryBuilder('t');
        $qb->select('COUNT(t.id)');
        $qb->where('t.concert=:concertId');
        $qb->setParameter('concertId', $concert->id);

        $query = $qb->getQuery();
        $count = $query->getSingleScalarResult();
        return $count;
    }

    public function reorderConcertItems(\Songbook\Entity\Concert $concert)
    {
        // find all concert items ordered by order, then id
        $em = $this->getEntityManager();

        $data = $em
            ->getRepository('Songbook\Entity\ConcertItem')
            ->findBy(array('concert' => $concert), array('order' => 'ASC', 'id' => 'ASC'));

        // cycle, update order
        $order = 1;

        foreach($data as $concertItem) {
            /* @var $concertItem Songbook\Entity\ConcertItem */
            $concertItem->order = $order++;
            $em->persist($concertItem);
        }

         // save
         return $em->flush();
    }

    public function reorderConcertItemsByIds(\Songbook\Entity\Concert $concert, array $ids)
    {
        // find all concert items ordered by order, then id
        $em = $this->getEntityManager();

        $data = $em
            ->getRepository('Songbook\Entity\ConcertItem')
            ->findBy(array('concert' => $concert), array('order' => 'ASC', 'id' => 'ASC'));

        // cycle, update order

        $sortArray = array();

        foreach($data as $concertItem) {
            /* @var $concertItem Songbook\Entity\ConcertItem */
            $sortArray[$concertItem->id] = $concertItem;
        }


        $order = 1;
        foreach($ids as $id){
            if(isset($sortArray[$id])){
                $sortArray[$id]->order = $order++;
                $em->persist($sortArray[$id]);
            }
        }

         // save
         return $em->flush();
    }

    /**
     * @return array
     */
    public function getCollectionByProfile($profile = null, $criteria = null, $orderBy = null, $limit = null, $offset = null)
    {
        if(null === $profile){
            // get current user
            $userService = $this->getUserService();
            $user = $userService->getCurrentUser();

            // get active profile
            $profileService = $this->getProfileService();
            $profile = $profileService->getCurrentByUser($user);
        }

        if (null === $orderBy) {
            $orderBy = array(
                't.time' => 'DESC'
            );
        }

        // take all concerts
        $data = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Concert')
            ->findByProfile($profile, $criteria, $orderBy, $limit, $offset );

        return $data;
    }

    public function isSongInConcert (\Songbook\Entity\Song $song, \Songbook\Entity\Concert $concert)
    {
        $em = $this->getEntityManager();
        if ($em->getRepository('Songbook\Entity\ConcertItem')->findOneBy(
                array(
                    'song' => $song,
                    'concert' => $concert
                ))) {
            return true;
        } else {
            return false;
        }
    }

    public function isConcertEmpty (\Songbook\Entity\Concert $concert)
    {
        $em = $this->getEntityManager();
        if ($em->getRepository('Songbook\Entity\ConcertItem')->findOneBy(
                array(
                    'concert' => $concert
                ))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param string $date format 2014-12-31
     *
     * @return \Songbook\Entity\Concert|null
     */
    public function findConcertByDate ($date)
    {
        // FIXME
        $em = $this->getEntityManager();
        return $em->getRepository('Songbook\Entity\Concert')->findOneBy(
                array(
                    'time' => $date
                ));
    }



    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
     */
    public function setServiceLocator (ServiceLocatorInterface $sl)
    {
        $this->sl = $sl;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface;
     */
    public function getServiceLocator ()
    {
        return $this->sl;
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

    public function formatSongListString(ConcertEntity $concert, $isIncludeConcertGroups = false)
    {
        $songService = $this->getSongService();

        $songs = $songService->getCollectionByConcert($concert);

        $lines = array();
        $prevConcertGroup = null;
        /* @var $prevConcertGroup \Songbook\Entity\ConcertGroup */
        $counter = 0;

        foreach($songs as $song) {
            $concertGroup = $song->currentConcertItem->concertGroup;
            /* @var $concertGroup \Songbook\Entity\ConcertGroup */
            if($isIncludeConcertGroups) {
                if (!is_null($concertGroup) && (is_null($prevConcertGroup) || $prevConcertGroup->id != $concertGroup->id)) {

                    if($counter > 0){
                        $lines []= '';
                    }

                    if(strlen((string)$concertGroup->name) > 0) {
                        $lines [] = '=== ' . $concertGroup->name . ' ===';
                    } else {
                        $lines [] = '===========';
                    }
                    $lines []= '';
                }

                $lines [] = ($counter + 1) . '. ' . (( $song->favoriteHeader )? $song->favoriteHeader->content : $song->defaultHeader->content );
            }

            $prevConcertGroup = $concertGroup;
            $counter++;
        }

        return implode("\n", $lines);
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
     * @return \Songbook\Service\Song
     */
    protected function getSongService ()
    {
        if (! $this->profileService) {
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }

        return $this->songService;
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