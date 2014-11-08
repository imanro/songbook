<?php

namespace Songbook;

use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Songbook\Model\SongImport;


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
                    return new SongImport();
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
        'import-songs [db|csv]'        => 'Import songs from database or csv file',
         array('[db|csv]',    'Import from Database OR Csv file'),
     );
}
}