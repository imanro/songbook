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

        die('import from database');
    }

    public function importCsv()
    {
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

}