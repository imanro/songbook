<?php

namespace Songbook\Service;

use Doctrine\Common\Collections\Criteria;
use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Songbook\Entity\Song as SongEntity;
use Songbook\Entity\Content as ContentEntity;

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
     * @var \Songbook\Model\Cloud
     */
    protected $cloudModel;

    /**
     * @var \Songbook\Service\Profile
     */
    protected $profileService;

    public function getById($id)
    {
        return $this->getSongById($id);
    }

    public function getSongById($id)
    {
        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // take all concerts
        $item = $this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findOneWithHeaders($user, $id);

        return $item;
    }

    public function getSongContent(SongEntity $song, $type = ContentEntity::TYPE_HEADER, $mimeTypes = null)
    {
        if(!is_array($type)){
            $type = array($type);
        }

        if(in_array(ContentEntity::TYPE_GDRIVE_CLOUD_FILE, $type)){
            // sync gdrive folder and content table
            // + maybe add synced time in song table to sync every nth minutes, not every page reloading
            $this->syncCloudContent($song);
        }

        $content = $song->content;
//        return $content->toArray();

        $criteria = Criteria::create()->where(Criteria::expr()->in('type', $type));

        if(!is_null($mimeTypes)){
            if(!is_array($mimeTypes)){
                $mimeTypes = array($mimeTypes);
            }
            $criteria->andWhere(Criteria::expr()->in('mime_type', $mimeTypes));
        }

        return $content->matching($criteria);
    }

    /**
     * @param SongEntity $song
     * @param string $content if $type == ContentEntity::TYPE_CLOUD_FILE, then it would be a fs link
     * @param string $type
     */
    public function addSongContent(SongEntity $song, $content, $type = ContentEntity::TYPE_HEADER)
    {
        $user = $this->getUserService()->getCurrentUser();

        $em = $this->getEntityManager();

        if(!$content instanceof ContentEntity) {
            $entityContent = new ContentEntity();
            $entityContent->type = $type;
            $entityContent->content = $content;
        } else {
            $entityContent = $content;
        }

        $entityContent->song = $song;
        $entityContent->user = $user;

        $em->persist($entityContent);
        $em->flush();

        return $entityContent;
        // if its header/link/inline - just create record in content table
    }

    public function syncCloudContent(SongEntity $song)
    {
        if(!$this->isCloudContentShouldBeSynced($song)){
            return true;
        } else {
            // 1st - list content of table
            $content = $song->content;

            $criteria = Criteria::create()->where(Criteria::expr()->eq('type', ContentEntity::TYPE_GDRIVE_CLOUD_FILE));
            $dbItems = $content->matching($criteria);

            // 2nd - list files in cloud
            $cloudModel = $this->getCloudModel();
            $cloudFiles = $cloudModel->getSongFiles($song);


            $dbIds = array();
            $cloudIds = array();

            foreach ($cloudFiles as $file) {
                $cloudIds [$file->getId()] = $file;
            }

            foreach ($dbItems as $item) {
                $dbIds [$item->content] = $item;
            }

            $dbMissing = array();
            $cloudMissing = array();

            foreach ($cloudFiles as $file) {
                $found = false;
                foreach ($dbItems as $item) {
                    if ($item->content == $file->getId() && $item->file_name == $file->getName() && $item->mime_type == $file->getMimeType()) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $dbMissing[] = $file;
                }
            }

            foreach ($dbItems as $item) {
                $found = false;
                foreach ($cloudFiles as $file) {
                    if ($item->content == $file->getId() && $item->file_name == $file->getName() && $item->mime_type == $file->getMimeType()) {
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $cloudMissing[] = $item;
                }
            }


            // 3rd, sync by name - add to table all that is missing in folder
            foreach ($dbMissing as $file) {

                $entityContent = new ContentEntity();
                $entityContent->type = ContentEntity::TYPE_GDRIVE_CLOUD_FILE;
                $entityContent->content = $file->getId();
                $entityContent->file_name = $file->getName();
                $entityContent->mime_type = $file->getMimeType();

                $this->addSongContent($song, $entityContent, ContentEntity::TYPE_GDRIVE_CLOUD_FILE);
            }

            // 4th, sync by name - remove from table all that missing in cloud
            foreach ($cloudMissing as $item) {
                $this->getEntityManager()->remove($item);
            }

            $song->cloud_content_sync_time = time();
            $this->getEntityManager()->persist($song);

            // store all changes
            $this->getEntityManager()->flush();
            return true;
        }
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
            'typeHeader' => \Songbook\Entity\ContentEntity::TYPE_HEADER,
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

    public function getCollectionUsedLastMonths(\Songbook\Entity\Profile $profile = null, array $criteria = null, array $orderBy = null, $monthsAmount = 2, $limit = null)
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
            ->findUsedLastMonthsWithHeaders($profile, $criteria, $orderBy, $monthsAmount, $limit);

        return $data;
    }

    public function getCollectionTakenLastMonths(\Songbook\Entity\Profile $profile = null, array $criteria = null, array $orderBy = null, $monthsAmount = 2, $limit = null)
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
            ->findTakenLastMonthsWithHeaders($profile, $criteria, $orderBy, $monthsAmount, $limit);

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


        $repo = $em->getRepository('Songbook\Entity\Content');
        $content = $repo->findOneBy(array('song' => $masterId, 'type' => Entity\ContentEntity::TYPE_HEADER), array('id' => 'ASC'));

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
    protected function createDefaultHeader (SongEntity $song)
    {
        $em = $this->getEntityManager();

        // fake user
        $user = $em->find('User\Entity\User', 1);

        $existing = $em->getRepository('Songbook\Entity\Content')->findOneBy(
                array(
                    'type' => Entity\ContentEntity::TYPE_HEADER,
                    'content' => $song->title
                ));

        if (is_null($existing)) {
            $content = new Entity\Content();
            $content->type = Entity\ContentEntity::TYPE_HEADER;
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
        $song = new SongEntity();
        $song->exchangeArray($data);
        $em->persist($song);
        $em->flush();

        $this->createDefaultHeader($song);
        return $song;
    }

    public function deleteSong (SongEntity $song)
    {
        $this->getEntityManager()->remove($song);
        $this->getEntityManager()->flush();
    }

    protected function isCloudContentShouldBeSynced(SongEntity $song)
    {
        return time() - (int)$song->cloud_content_sync_time > (int)$this->getServiceLocator()->get('Config')['cloud']['sync_time_threshold_seconds'];
    }

    protected function uploadCloudFile(SongEntity $song, $fsPath)
    {
        $cloudModel = $this->getCloudModel();
        return $cloudModel->uploadSongFile($song, $fsPath);
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

    protected function getCloudModel()
    {
        if(is_null($this->cloudModel)) {
            $sl = $this->getServiceLocator();
            $this->cloudModel = $sl->get('Songbook\Model\Cloud');
        }

        return $this->cloudModel;
    }

}