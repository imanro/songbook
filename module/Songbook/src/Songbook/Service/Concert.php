<?php
namespace Songbook\Service;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Doctrine\ORM\EntityManager;
use PDO;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity;

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
     * @param \Zend\ServiceManagerServiceLocatorInterface $sl
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
     * @return \Sonbook\Service\Profile
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