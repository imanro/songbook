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
use Songbook\Entity\Concert;
use Songbook\Entity\Profile;
use Songbook\Entity\User;

class SongImport {

    /**
     * @var ServiceLocatorInterface
     */
    protected $sl;

    protected $em;

    public function importDb()
    {
        $importAdapter = new \Zend\Db\Adapter\Adapter(
                array(
                    'driver' => 'Pdo',
                    'dsn' => 'mysql:dbname=songbook_old;host=localhost',
                    'username' => 'songbook_old',
                    'password' => 'songbook_old',
                    'driver_options' => array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                    ),
));
        $sql = new Sql($importAdapter);
        $select = $sql->select()
        ->from('song')
        ->order(array('song_title' => 'ASC'));

        $sqlString = $sql->getSqlStringForSqlObject($select);

        $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
        $results = $importAdapter->query($sqlString, $importAdapter::QUERY_MODE_EXECUTE, $resultSet );

        $em = $this->getEntityManager();

        foreach($results as $importRow ) {
            $song = $em->getRepository('Songbook\Entity\Song')->findOneBy(array('title' => $importRow['song_title']));
            if(is_null($song)){
                $data = $this->prepareImportDbArray($importRow);
                $song = new Song();
                $song->exchangeArray($data);
                $em->persist($song);
                $em->flush();
            }
        }

        return true;
    }

    public function importCsv()
    {
        $string = '5.09.13';
        $timestamp = $this->getTimestamp($string);
        $timestampSunday = $this->getNextSundayTimestamp($timestamp);
        var_dump(date('D, j.m.Y', $timestampSunday));
        die('import from csv file');
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

    protected function prepareImportDbArray(array $array)
    {
        $newArray = array();

        array_walk($array,
                function  ($value, $key) use( &$newArray)
                {
                    $newKey = str_replace('song_', '', $key);

                    if ($newKey == 'id') {
                        return;
                    }
                    $newArray[$newKey] = $value;
                });

        return $newArray;
    }

    /**
     * @param string $title
     * @return Ambigous \Songbook\Entity\Song|null
     */
    protected function findSongByTitle ($title)
    {
        $em = $this->getEntityManager();
        return $em->getRepository('Songbook\Entity\Song')->findOneBy(
                array(
                    'title' => $title
                ));
    }

    /**
     * @param array $data
     * @return \Songbook\Entity\Song
     */
    protected function createSong (array $data)
    {
        $em = $this->getEntityManager();
        $data = $this->prepareImportDbArray($data);
        $song = new Song();
        $song->exchangeArray($data);
        $em->persist($song);
        $em->flush();
        return $song;
    }

    protected function getTimestamp($string)
    {
        $array = explode('.', $string);

        if(count($array) != 3) {
            throw new \Exception('wrong format');
        }

        @list($day, $month, $year) = $array;
        $day = (int)$day;
        $month = (int)$month;

        if (mb_strlen($year) == 2) {
            $year = 2000 + (int) $year;
        } else {
            $year = (int) $year;
        }

        return mktime(0, 0, 0, $month, $day, $year);
    }

    protected function getNextSundayTimestamp($timestamp)
    {
        return strtotime('next Sunday', $timestamp);
    }

    protected function findConcertByTime($timestamp)
    {
        //FIXME
        $em = $this->getEntityManager();
        return $em->getRepository('Songbook\Entity\Concert')->findOneBy(
                array(
                    'time' => $timestamp
                ));
    }

    /**
     * @param array $data
     * @return \Songbook\Entity\Concert
     */
    protected function createConcert(array $data)
    {
        $profile = new Profile();
        $profile->id = 1;

        $em = $this->getEntityManager();
        $concert = new Concert();
        $consert->profile = $profile;
        $concert->exchangeArray($data);
        $em->persist($concert);
        $em->flush();
        return $concert;
    }
}