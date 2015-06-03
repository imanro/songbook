<?php

namespace Songbook;

use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;

use Songbook\Model\SongImport;
use Songbook\Model\SongService;


class Module implements ConsoleBannerProviderInterface
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
               'Songbook\Model\SongService' => function  ($sm)
                {
                    $model = new SongService();
                    $model->setServiceLocator($sm);
                    return $model;
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
        'import-songs [db|txt|txt-concerts] <filename>'        => 'Import songs from database or csv file',
         array('[db|txt|txt-concerts]',    'Import from Database OR text file with songs or songs + concert data'),
         array('<filename>', 'Text file name with data to be imported'),
        'create-headers'        => 'Create headers for all songs',
     );
}
}