<?php
namespace User\Service;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Doctrine\ORM\EntityManager;
use PDO;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


class User
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
     * @return \User\Entity\User
     */
    public function getCurrentUser()
    {
        $em = $this->getEntityManager();
        $user = $em->find('User\Entity\User', 1);
        return $user;
    }

    /**
     * @param \Zend\ServiceManagerServiceLocatorInterface $sl
     */
    public function setServiceLocator (ServiceLocatorInterface $sl)
    {
        $this->sl = $sl;
    }

    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface;
     */
    public function getServiceLocator ()
    {
        return $this->sl;
    }

    /**
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
