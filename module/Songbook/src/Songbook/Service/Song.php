<?php

namespace Songbook\Service;

use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Criteria;
use PDO;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class Song
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

    public function getById($id)
    {
        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // take all concerts
        $item = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findOneWithHeaders($user, $id);

        return $item;
    }

    public function getSongContent(\Songbook\Entity\Song $song, $type = Content::TYPE_HEADER)
    {
        $content = $song->content;
//        return $content->toArray();

        $criteria = Criteria::create()->where(Criteria::expr()->eq('type', $type));
        return $content->matching($criteria);
    }

    public function getCollectionByHeader ($string, array $orderBy = null, $limit = null, $offset = null)
    {
        if (null === $orderBy) {
            $orderBy = array(
                // 'h.content' => 'ASC'
            );
        }

        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // take all concerts
        $data = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findByHeaderWithHeaders($user, $string, $orderBy, $limit, $offset);

        return $data;
    }

    /**
     * Get one song by exactly header
     *
     * @param \User\Entity\User $user
     * @param string $string
     *
     * @return \Songbook\Entity\Song
     */
    public function getSongByHeader($string, \User\Entity\User $user = null)
    {
        if (is_null($user)) {
            $userService = $this->getUserService();
            $user = $userService->getCurrentUser();
        }

        // FIXME
        $em = $this->getEntityManager();
        $rep = $em->getRepository('Songbook\Entity\Song');
        $qb = $rep->createQueryBuilder('t');
        /* @var $qb \Doctrine\ORM\QueryBuilder */

        $qb->innerJoin('t.defaultHeader', 'h', 'WITH',
                'h.type=:typeHeader and h.user = :userId');
        $qb->where('h.content=:string');

        $qb->setParameters(array(
            'userId' => $user->id,
            'typeHeader' => \Songbook\Entity\Content::TYPE_HEADER,
            'string' => $string
        ));
        $qb->groupBy('t.id');
        $qb->setMaxResults(1);

        $query = $qb->getQuery();

        try {
            return $query->getSingleResult();
        } catch (\Doctrine\Orm\NoResultException $e) {
            return null;
        }
    }

    public function getCollectionByConcert(\Songbook\Entity\Concert $concert, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        if (null === $orderBy) {
            $orderBy = array(
                'i.order' => 'ASC'
            );
        }

        // take all concerts
        $data = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findByConcertWithHeaders($concert, $criteria, $orderBy, $limit, $offset);

        return $data;
    }

    public function getCollectionLongNotUsed(\Songbook\Entity\Profile $profile = null, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        if (is_null($profile)) {
            // get current user
            $userService = $this->getUserService();
            $user = $userService->getCurrentUser();

            // get active profile
            $profileService = $this->getProfileService();
            $profile = $profileService->getCurrentByUser($user);
        }

        if(is_null($limit)){
            $limit = 10;
        }

            // take all concerts
        $data = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findLongNotUsedWithHeaders($profile, $criteria, $orderBy, $limit,
                $offset);

        return $data;
    }

    public function getCollectionPopular(\Songbook\Entity\Profile $profile = null, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        if (is_null($profile)) {
            // get current user
            $userService = $this->getUserService();
            $user = $userService->getCurrentUser();

            // get active profile
            $profileService = $this->getProfileService();
            $profile = $profileService->getCurrentByUser($user);
        }

        $user = $profile->user;

        if (is_null($limit)) {
            $limit = 10;
        }

        if (is_null($offset)) {
            $offset = 0;
        }

        if(is_null($orderBy)){
            $orderBy = 'performances_amount';
        }

        $em = $this->getEntityManager();
        $rep = $em
            ->getRepository('Songbook\Entity\Song');
        /* @var $rep \Songbook\Entity\SongRepository */
        $headersSelectSql = $rep->getSqlSelectPartForHeaders();
        $headersJoinSql = $rep->getSqlJoinPartForHeaders();


        // CHECK $orderBy escaping

        $sql = 'SELECT b.time last_performance_time, a.*, COUNT(1) performances_amount FROM
                (SELECT a.id, ' . $headersSelectSql . ' FROM song a ' . $headersJoinSql .' GROUP BY a.id) a
                INNER JOIN
                (
                    SELECT a.song_id, b.time
                    FROM concert_item a
                    JOIN concert b ON a.concert_id=b.id
                    ORDER BY b.time DESC
                ) b ON a.id=b.song_id GROUP BY a.id
                ORDER BY ' . $orderBy . ' DESC LIMIT :limit OFFSET :offset';

        $rsm = new ResultSetMappingBuilder($em);
        $rsm->addRootEntityFromClassMetadata('\Songbook\Entity\Song', 'a');
        $rsm->addScalarResult('last_performance_time', 'lastPerformanceTime', 'timestamp');
        $rsm->addScalarResult('performances_amount', 'performancesAmount', 'string');

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameters(array('limit' => $limit, 'offset' => $offset));
        $rep->setNativeQueryParametersForHeaders($query, $rsm, $user);

        $result = $query->getResult();
        foreach($result as $key => $row){
            $row['entity'] = $row[0];
            unset($row[0]);
            $result[$key] = $row;
        }

        return $result;
    }

    public function getCollectionPopularOld(\Songbook\Entity\Profile $profile = null, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        if (is_null($profile)) {
            // get current user
            $userService = $this->getUserService();
            $user = $userService->getCurrentUser();

            // get active profile
            $profileService = $this->getProfileService();
            $profile = $profileService->getCurrentByUser($user);
        }

        $user = $profile->user;

        if (is_null($limit)) {
            $limit = 10;
        }

        $rep = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Song');

        $qb = $rep
            ->createQueryBuilderCommon($criteria, $orderBy, $limit, $offset);

        $rep->modifyQueryForHeaders($qb, $user);

        // select + count
        $qb->addSelect('count(DISTINCT i.id) as cnt');

        // join concert items
        $qb->innerJoin('t.concertItem', 'i');
        $qb->innerJoin('i.concert', 'c', 'WITH', 'c.profile=:profileId');

        // group by song
        $qb->groupBy('t.id');

        $qb->orderBy('cnt', 'DESC');

        $qb->setParameter('profileId', $profile->id);

        $query = $qb->getQuery();

        try {
            return $query->getResult();
        } catch (NoResultException $e) {
            return array();
        }
    }

    public function mergeSongs($masterId, array $ids )
    {
        // relink all content
        $em = $this->getEntityManager();

        $master = $em->find('Songbook\Entity\Song', $masterId );


        $repo = $em->getRepository('Songbook\Entity\Content');
        $content = $repo->findOneBy(array('song' => $masterId, 'type' => Entity\Content::TYPE_HEADER), array('id' => 'ASC'));

        if( $content ) {

            // clear favorites
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->update('Songbook\Entity\Content', 'c')
                ->set('c.is_favorite', '0')
                ->where('c.song = :songId')
                ->setParameters(array(
                'songId' => $masterId
            ));

            $query = $qb->getQuery();
            $numUpdated = $query->execute();

            // setFavorite
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->update('Songbook\Entity\Content', 'c')
                ->set('c.is_favorite', '1')
                ->where('c.id = :id')
                ->setParameters(array(
                'id' => $content->id
            ));

            $query = $qb->getQuery();
            $numUpdated = $query->execute();
        }

        foreach( $ids as $id ) {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb
            ->update('Songbook\Entity\Content', 'c')
            ->set('c.song', ':masterId')
            ->where('c.song = :id')
            ->setParameters(array('masterId' => $masterId, 'id' => $id ));

            $query = $qb->getQuery();
            $numUpdated = $query->execute();

            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb
            ->update('Songbook\Entity\ConcertItem', 'i')
            ->set('i.song', ':masterId')
            ->where('i.song = :id')
            ->setParameters(array('masterId' => $masterId, 'id' => $id ));

            $query = $qb->getQuery();
            $numUpdated = $query->execute();

            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb
            ->delete('Songbook\Entity\Song', 's')
            ->where('s.id = :id')
            ->setParameters(array('id' => $id ));

            $query = $qb->getQuery();
            $numDeleted = $query->execute();
        }

        // relink concert_item
    }

    public function createHeaders()
    {
        // for each song
        foreach ($this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findAll() as $song) {
            /* @var $song Entity\Song */
            $this->createDefaultHeader($song);
        }

        // create new entity "content" with type "header"
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

    /**
     * @param array $data
     * @return \Songbook\Entity\Song
     */
    protected function createDefaultHeader (Entity\Song $song)
    {
        $em = $this->getEntityManager();

        // fake user
        $user = $em->find('User\Entity\User', 1);

        $existing = $em->getRepository('Songbook\Entity\Content')->findOneBy(
                array(
                    'type' => Entity\Content::TYPE_HEADER,
                    'content' => $song->title
                ));

        if (is_null($existing)) {
            $content = new Entity\Content();
            $content->type = Entity\Content::TYPE_HEADER;
            $content->content = $song->title;
            $content->song = $song;
            $content->user = $user;
            $em->persist($content);
            $em->flush();
            return $content;

        } else {
            return $existing;
        }
    }

        /**
     *
     * @param array $data
     * @return \Songbook\Entity\Song
     */
    public function createSong (array $data)
    {
        $em = $this->getEntityManager();
        $song = new \Songbook\Entity\Song();
        $song->exchangeArray($data);
        $em->persist($song);
        $em->flush();

        $this->createDefaultHeader($song);
        return $song;
    }

    public function deleteSong (Entity\Song $song)
    {
        $this->getEntityManager()->remove($song);
        $this->getEntityManager()->flush();
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