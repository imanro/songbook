<?php

namespace Songbook\Model;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Doctrine\ORM\EntityManager;
use PDO;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity\Song;
use Songbook\Entity\Content;
use Songbook\Entity\User;

class SongService
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $sl;

    /**
     *
     */
    protected $em;

    public function createHeaders()
    {
        // for each song
        foreach ($this->getEntityManager()
            ->getRepository('Songbook\Entity\Song')
            ->findAll() as $song) {
            /* @var $song Song */
                $this->createHeader($song);
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
    protected function createHeader (Song $song)
    {
        $em = $this->getEntityManager();

        // fake user
        $user = $em->find('Songbook\Entity\User', 1);

        $existing = $em->getRepository('Songbook\Entity\Content')->findOneBy(
                array(
                    'type' => Content::TYPE_HEADER,
                    'content' => $song->title
                ));

        if (is_null($existing)) {
            $content = new Content();
            $content->type = Content::TYPE_HEADER;
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
}