<?php
namespace Songbook\Service;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Doctrine\ORM\EntityManager;
use PDO;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Songbook\Entity;

class Profile
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
     * @return \Songbook\Entity\Profile
     */
    public function getCurrentByUser(\User\Entity\User $user)
    {
        // last profile for now
        $em = $this->getEntityManager();
        $repo = $em->getRepository('Songbook\Entity\Profile');
        $profile = $repo->findOneBy(array('user' => $user->id), array('create_time' => 'DESC'));
        return $profile;
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

    /**
     * @return \Sonbook\Model\UserService
     */
    protected function getUserService ()
    {
        if (! $this->userService) {
            $sm = $this->getServiceLocator();
            $this->userService = $sm->get('User\Service\User');
        }
        return $this->userService;
    }


}