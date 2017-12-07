<?php
/**
 * Created by PhpStorm.
 * User: manro
 * Date: 18.08.17
 * Time: 15:09
 */
namespace Songbook\Service;

use Zend\ServiceManager\ServiceLocatorInterface;

abstract class AbstractService
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface;
     */
    protected $sl;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

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

}
