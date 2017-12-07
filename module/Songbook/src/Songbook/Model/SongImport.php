<?php
namespace Songbook\Model;
use Zend\Db\Sql\Sql;
use Zend\Db\ResultSet\ResultSet;
use PDO;
use DateTime;
use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity\Song;

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

        $songService = $this->getSongService();

        $lines = file($filename);
        foreach ($lines as $line) {
            // foreach
            $line = trim($line);
            if (! $line) {
                continue;
            }

            $song = $songService->getSongByHeader($line);
            // find by title

            if (! $song) {
                // not found?

                // create + store
                $data = array(
                    'title' => $line,
                    'author' => '',
                    'copyright' => ''
                );


                $songService->createSong($data);
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

        // get current user
        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // get active profile
        $profileService = $this->getProfileService();
        $profile = $profileService->getCurrentByUser($user);

        $songService = $this->getSongService();
        $concertService = $this->getConcertService();

        $lines = file($filename);

        $concert = null;
        $order = 0;

        foreach ($lines as $line) {
            // foreach
            $line = trim($line);

            if (! $line) {
                continue;
            }

            $parts = explode(';', $line);
            if (count($parts) == 2 && ! mb_strlen($parts[0])) {
                // if line seems to be like date

                if (! is_null($concert)) {
                    // fix ordering of previous concert
                    $concertService->reorderConcertItems($concert);
                }

                $em->flush();

                // get timestamp for this date
                $timestamp = $this->getTimestampDmy($parts[1]);

                // get nearest sunday
                $dateSunday = $this->getNextSundayDate($timestamp);

                // try to find concert...
                $concert = $concertService->findConcertByDate($dateSunday);

                // not found? create concert
                if (! $concert) {
                    var_dump('Concert not found');
                    continue;
                    /*
                    $concert = $concertService->createConcert(
                            $profile,
                            array(
                                'time' => $dateSunday
                            ));
                            */
                }

                $order = 0;

            } else {
                $line = preg_replace('/^.+?\)?\./', '', $line);
                $line = preg_replace('/^.+?\)/', '', $line);
                $line = trim($line);

                if(!mb_strlen($line)){
                    // skip empty lines
                    continue;
                }

                // else
                $song = $songService->getSongByHeader($line);
                // find by title

                if (! $song) {
                    // not found?

                    // create + store
                    $data = array(
                        'title' => $line,
                        'author' => '',
                        'copyright' => ''
                    );

                    //var_dump(vsprintf('Song "%s" not found', array($line)));
                    //continue;
                    $song = $songService->createSong($data);
                }

                if (! is_null($concert)) {
                    $order++;

                    if(!$concertService->isSongInConcert($song, $concert)){
                        var_dump(vsprintf('!!!! Song "%s" is not in concert !!!!!', array($line)));
                        // if there is concert - add to concert
                        $concertService->createConcertItem($concert, $song, array( 'order' => $order));
                    }
                }
            }
        }
    }

    //delete ci.* from concert_item ci inner join concert c on ci.concert_id = c.id where c.create_time >= '2014-12-03' ;
    //delete from concert where create_time >= '2014-12-03' ;
    public function importFolder($rootFolder, array $subfolderNames, $isSortFiles = true)
    {
        $em = $this->getEntityManager();
        $songService = $this->getSongService();
        $concertService = $this->getConcertService();

        // get current user
        $userService = $this->getUserService();
        $user = $userService->getCurrentUser();

        // get active profile
        $profileService = $this->getProfileService();
        $profile = $profileService->getCurrentByUser($user);


        // readdir
        $dh = opendir($rootFolder);

        $folders = array();

        while($name = readdir($dh)){
             // skip
            if (in_array($name,
                    array(
                        '.',
                        '..'
                    ))) {
                continue;
            }

            $folders []= $name;
        }

        // sort
        sort($folders);

        // foreach
        foreach ($folders as $folderName) {

            // parse date (folder name)
            $timestamp = $this->getTimestampYmd($folderName);

            if(!$timestamp){
                $timestamp = $this->getTimestampDmy($folderName);

                if(!$timestamp){
                    var_dump('Not date', $folderName);
                    continue;
                }
            }

            $year = (int) date('Y', $timestamp);

            if ($year < 2011 || $year > 2014) {

                $timestamp = $this->getTimestampDmy($folderName);
                $year = (int) date('Y', $timestamp);

                if ($year < 2011 || $year > 2014) {
                    var_dump('Not in range', date('j.m.Y', $timestamp), $folderName);
                    continue;
                }
            }

            $date = $this->getDateString($timestamp);

            // search concert or continue
            $concert = $this->findConcertByDate($date);

            if (! $concert) {
                $concert = $concertService->createConcert(
                        $profile,
                        array(
                            'time' => $date
                        ));
            } else {
                var_dump( '! Concert found' );
            }

            // search "slides" subfolder
            $name = $rootFolder . '/' . $folderName;
            $subfolderName = false;

            foreach ($subfolderNames as $n) {
                if (file_exists($s = $name . '/' . $n)) {
                    $subfolderName = $s;
                }
            }

            if(!$subfolderName){
                var_dump('Subfolder not found ' . $folderName);
                continue;
            }

            // readdir
            $dhs = opendir($subfolderName);

            // clear file name
            $re = '/^(?:.+?\.\s?)?(.+?)(?:\..+)/';

            // sort
            $files = array();
            while ($name = readdir($dhs)) {
                if (in_array($name,
                        array(
                            '.',
                            '..'
                        )) || substr($name, 0, 1) === '.') {
                    continue;
                    // skip
                }

                if (preg_match($re, $name, $matches)) {
                    $name = $matches[1];
                }

                $files[$name] = true;
            }

            $files = array_keys($files);

            // sort
            if($isSortFiles){
                sort($files);
            }

            $order = 0;

            if (! $concertService->isConcertEmpty($concert)) {
                var_dump('//////////////////////// Concert is not empty');
                continue;
            } else {
                var_dump('\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\ Concert empty');
            }

            foreach($files as $f ) {

                // search/create song
                $song = $songService->getSongByHeader($f);

                // find by title
                var_dump($f);

                if (! $song) {
                    // not found?

                    // create + store
                    $data = array(
                        'title' => $f,
                        'author' => '',
                        'copyright' => ''
                    );

                    $song = $songService->createSong($data);

                } else {
                    var_dump('SONG FOUND!!!!!!!!', $f);
                }

                // add to db
                if (! is_null($concert)) {
                    $order++;

                    if(!$concertService->isSongInConcert($song, $concert)){
                        var_dump('0000000000 song is not in concert 0000000000000');
                        $concertService->createConcertItem($concert, $song, array( 'order' => $order));
                    } else {
                        var_dump('777 Song In Concert 999');
                    }
                }

            }

            closedir($dhs);
        }

        $em->flush();
        closedir($dh);
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

    protected function getSongService ()
    {
        if (! $this->songService) {
            $sm = $this->getServiceLocator();
            $this->songService = $sm->get('Songbook\Service\Song');
        }

        return $this->songService;
    }

    protected function getConcertService ()
    {
        if (! $this->concertService) {
            $sm = $this->getServiceLocator();
            $this->concertService = $sm->get('Songbook\Service\Concert');
        }

        return $this->concertService;
    }

    protected function getProfileService ()
    {
        if (! $this->profileService) {
            $sm = $this->getServiceLocator();
            $this->profileService = $sm->get('Songbook\Service\Profile');
        }

        return $this->profileService;
    }

    protected function getUserService ()
    {
        if (! $this->userService) {
            $sm = $this->getServiceLocator();
            $this->userService = $sm->get('User\Service\User');
        }

        return $this->userService;
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


    protected function getTimestampDmy ($string)
    {
        $dateTime = DateTime::createFromFormat('d.m.y', $string);

        if ($dateTime) {
            return $dateTime->getTimestamp();
        } else {
            return false;
        }
    }

    protected function getTimestampYmd ($string)
    {
        $dateTime = DateTime::createFromFormat('y.m.d', $string);

        if ($dateTime) {
            return $dateTime->getTimestamp();
        } else {
            return false;
        }
    }

    protected function getNextSundayDate ($timestamp)
    {
        return date('Y-m-d', strtotime('next Sunday', $timestamp));
    }

    protected function getDateString ($timestamp)
    {
        return date('Y-m-d', $timestamp);
    }
}