<?php
namespace Songbook\Service;
use Zend\ServiceManager\ServiceLocatorInterface;

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
     * @param \Zend\ServiceManager\ServiceLocatorInterface $sl
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
     * @return \User\Service\User
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