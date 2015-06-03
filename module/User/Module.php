<?php
namespace User;

class Module
{

    public function getAutoloaderConfig ()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getServiceConfig ()
    {
        return array(
            'factories' => array(
                'User\Service\User' => function  ($sm)
                {
                    $service = new \User\Service\User();
                    $service->setServiceLocator($sm);
                    return $service;
                }
            )

        );
    }

    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}