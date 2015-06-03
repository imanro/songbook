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

class SongImport
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $sl;

    /**
     *
     */
    protected $em;

    public function importDb ()
    {
        $importAdapter = new \Zend\Db\Adapter\Adapter(
                array(
                    'driver' => 'Pdo',
                    'dsn' => 'mysql:dbname=songbook_old;host=localhost',
                    'username' => 'songbook_old',
                    'password' => 'songbook_old',
                    'driver_options' => array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
                    )
                ));
        $sql = new Sql($importAdapter);
        $select = $sql->select()
            ->from('song')
            ->order(array(
            'song_title' => 'ASC'
        ));

        $sqlString = $sql->getSqlStringForSqlObject($select);

        $resultSet = new ResultSet(ResultSet::TYPE_ARRAY);
        $results = $importAdapter->query($sqlString,
                $importAdapter::QUERY_MODE_EXECUTE, $resultSet);

        $em = $this->getEntityManager();

        foreach ($results as $importRow) {
            $song = $em->getRepository('Songbook\Entity\Song')->findOneBy(
                    array(
                        'title' => $importRow['song_title']
                    ));
            if (is_null($song)) {
                $data = $this->prepareImportDbArray($importRow);
                $song = new Song();
                $song->exchangeArray($data);
                $em->persist($song);
                $em->flush();
            }
        }

        return true;
    }

    public function importTxt ($filename)
    {
        // explode by newline
        if (! file_exists($filename)) {
            throw new \Exception(
                    vsprintf('File "%s" is not exists', array(
                        $filename
                    )));
        }

        $lines = file($filename);
        foreach ($lines as $line) {
            // foreach
            $line = trim($line);
            if (! $line) {
                continue;
            }

            $song = $this->findSongByTitle($line);
            // find by title

            if (! $song) {
                // not found?

                // create + store
                $data = array(
                    'title' => $line,
                    'author' => '',
                    'copyright' => ''
                );
                $this->createSong($data);
            }
        }

        echo "Done.\n";
    }

    public function importTxtConcert ($filename)
    {
        $em = $this->getEntityManager();
        // explode by newline
        if (! file_exists($filename)) {
            throw new \Exception(
                    vsprintf('File "%s" is not exists', array(
                        $filename
                    )));
        }

        $lines = file($filename);

        $concert = null;

        foreach ($lines as $line) {
            // foreach
            $line = trim($line);

            if (! $line) {
                continue;
            }

            $parts = explode(';', $line);
            if (count($parts) == 2 && ! mb_strlen($parts[0])) {
                $em->flush();
                // if line seems to be like date

                // get timestamp for this date
                $timestamp = $this->getTimestamp($parts[1]);

                // get nearest sunday
                $dateSunday = $this->getNextSundayDate($timestamp);

                // try to find concert...
                $concert = $this->findConcertByDate($dateSunday);

                // not found? create concert
                if (! $concert) {
                    $concert = $this->createConcert(
                            array(
                                'time' => $dateSunday
                            ));
                }

            } else {
                $line = preg_replace('/^.+?\)?\./', '', $line);
                $line = preg_replace('/^.+?\)/', '', $line);
                $line = trim($line);

                // else
                $song = $this->findSongByTitle($line);
                // find by title

                if (! $song) {
                    // not found?

                    // create + store
                    $data = array(
                        'title' => $line,
                        'author' => '',
                        'copyright' => ''
                    );
                    $song = $this->createSong($data);
                }

                if (! is_null($concert)) {
                    // if there is concert - add to concert
                    $this->addSongToConcert($concert, $song);
                }
            }
        }
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

    protected function prepareImportDbArray (array $array)
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
     *
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
     *
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

    protected function getTimestamp ($string)
    {
        $array = explode('.', $string);

        if (count($array) != 3) {
            throw new \Exception('wrong format');
        }

        @list ($day, $month, $year) = $array;
        $day = (int) $day;
        $month = (int) $month;

        if (mb_strlen($year) == 2) {
            $year = 2000 + (int) $year;
        } else {
            $year = (int) $year;
        }

        return mktime(0, 0, 0, $month, $day, $year);
    }

    protected function getNextSundayDate ($timestamp)
    {
        return date('Y-m-d', strtotime('next Sunday', $timestamp));
    }

    protected function findConcertByDate ($date)
    {
        // FIXME
        $em = $this->getEntityManager();
        return $em->getRepository('Songbook\Entity\Concert')->findOneBy(
                array(
                    'time' => $date
                ));
    }

    /**
     *
     * @param array $data
     * @return \Songbook\Entity\Concert
     */
    protected function createConcert (array $data)
    {
        $em = $this->getEntityManager();
        $profile = $em->find('Songbook\Entity\Profile', 1);

        if (is_null($profile)) {
            $profile = $this->initProfile();
        }

        $data['profile'] = $profile;

        $concert = new Concert();
        $concert->exchangeArray($data);

        $em->persist($concert);
        $em->flush();

        return $concert;
    }

    protected function initProfile ()
    {
        $em = $this->getEntityManager();
        $profile = new Profile();
        $profile->id = 1;
        $profile->name = 'Revival';
        $profile->user = $this->initUser();
        $em->persist($profile);
        $em->flush();
        return $profile;
    }

    protected function initUser ()
    {
        $em = $this->getEntityManager();
        $user = new User();
        $user->id = 1;
        $em->persist($user);
        $em->flush();
        return $user;
    }

    protected function addSongToConcert (Concert $concert, Song $song)
    {
        $em = $this->getEntityManager();
        $concert->addSong($song);
        $em->persist($concert);
        return $concert;
    }
}