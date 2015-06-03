<?php

namespace Ez\Doctrine\Service;

use DoctrineORMModule\Service\EntityManagerFactory;

use Ez\Doctrine\ORM\EntityManagerExtended;
use Zend\ServiceManager\ServiceLocatorInterface;

class EntityManagerExtendedFactory extends EntityManagerFactory {
    /**
     * {@inheritDoc}
     * @return EntityManager
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        /* @var $options \DoctrineORMModule\Options\EntityManager */
        $options    = $this->getOptions($sl, 'entitymanager');
        $connection = $sl->get($options->getConnection());
        $config     = $sl->get($options->getConfiguration());

        // initializing the resolver
        // @todo should actually attach it to a fetched event manager here, and not
        //       rely on its factory code
        $sl->get($options->getEntityResolver());

        // here is the staff
        return EntityManagerExtended::create($connection, $config);
    }
}