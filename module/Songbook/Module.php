<?php

namespace Songbook;

use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Songbook\Model\SongImport;
use Zend\Mvc\MvcEvent;

class Module implements ConsoleBannerProviderInterface
{
    public function getAutoloaderConfig ()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                    'User' => __DIR__ . '/../User/src/User',
                )
            )
        );
    }

    public function getConfig ()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig ()
    {
        return array(
            'factories' => array(
                'Songbook\Model\SongImport' => function  ($sm)
                {
                    $model = new SongImport();
                    $model->setServiceLocator($sm);
                    return $model;
                },

                'User\Entity\User' => function  ($sm)
                {
                    $entity = new \User\Entity\User();
                    return $entity;
                },

                // Services
               'Songbook\Service\Song' => function  ($sm)
                {
                    $service = new \Songbook\Service\Song();
                    $service->setServiceLocator($sm);
                    return $service;
                },

               'Songbook\Service\Concert' => function  ($sm)
                {
                    $service = new \Songbook\Service\Concert();
                    $service->setServiceLocator($sm);
                    return $service;
                },

               'Songbook\Service\Profile' => function  ($sm)
                {
                    $service = new \Songbook\Service\Profile();
                    $service->setServiceLocator($sm);
                    return $service;
                },

                'User\Service\User' => function  ($sm)
                {
                    $service = new \User\Service\User();
                    $service->setServiceLocator($sm);
                    return $service;
                },

            )
        );
    }

    public function getConsoleBanner (Console $console)
    {
        return "==------------------------------------------------------==\n" .
                "        Welcome to my ZF2 Console-enabled app             \n" .
                "==------------------------------------------------------==\n";
    }

    public function getConsoleUsage(Console $console){
     return array(
        'import-songs [db|txt|txt-concerts|folder-slides|folder-texts] <filename>'        => 'Import songs from database or csv file',
         array('[db|txt|txt-concerts|folder-slides|folder-texts]',    'Import from Database OR text file with songs or songs + concert data'),
         array('<filename>', 'Text file name with data to be imported'),
        'create-headers'        => 'Create headers for all songs',
     );
}
}