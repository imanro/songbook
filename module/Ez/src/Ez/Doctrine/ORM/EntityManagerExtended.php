<?php

namespace Ez\Doctrine\ORM;

use Ez\Doctrine\ORM\QueryExtended;
use Ez\Doctrine\ORM\QueryBuilderExtended;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\ORMException;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;

class EntityManagerExtended extends EntityManager {


    public static function create($conn, Configuration $config, EventManager $eventManager = null)
    {
        if ( ! $config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        switch (true) {
            case (is_array($conn)):
                $conn = \Doctrine\DBAL\DriverManager::getConnection(
                    $conn, $config, ($eventManager ?: new EventManager())
                );
                break;

            case ($conn instanceof Connection):
                if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
                     throw ORMException::mismatchedEventManager();
                }
                break;

            default:
                throw new \InvalidArgumentException("Invalid argument: " . $conn);
                break;
        }

        return new EntityManagerExtended($conn, $config, $conn->getEventManager());
    }

    public function createQuery ($dql = '')
    {
        $query = new QueryExtended($this);

        if (! empty($dql)) {
            $query->setDql($dql);
        }

        return $query;
    }

    public function createQueryBuilder()
    {
        return new QueryBuilderExtended($this);
    }


}