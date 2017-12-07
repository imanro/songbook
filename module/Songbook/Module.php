<?php

namespace Songbook;

use Ez\Api\Response;
use Zend\ModuleManager\Feature\ConsoleBannerProviderInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Songbook\Model\SongImport;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

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

    public function bootstrapSession($e)
    {
        /* @var $e \Zend\Mvc\MvcEvent */
        $session = $e->getApplication()
            ->getServiceManager()
            ->get('Zend\Session\SessionManager');
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
            $serviceManager = $e->getApplication()->getServiceManager();
            $request = $serviceManager->get('Request');

            $session->regenerateId(true);
            $container->init = 1;
            $container->remoteAddr = $request->getServer()->get('REMOTE_ADDR');
            $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

            $config = $serviceManager->get('Config');
            if (!isset($config['session'])) {
                return;
            }

            $sessionConfig = $config['session'];
            if (isset($sessionConfig['validators'])) {
                $chain = $session->getValidatorChain();

                foreach ($sessionConfig['validators'] as $validator) {
                    switch ($validator) {
                        case 'Zend\Session\Validator\HttpUserAgent':
                            $validator = new $validator($container->httpUserAgent);
                            break;
                        case 'Zend\Session\Validator\RemoteAddr':
                            $validator = new $validator($container->remoteAddr);
                            break;
                        default:
                            $validator = new $validator();
                    }

                    $chain->attach('session.validate', array($validator, 'isValid'));
                }
            }
        }
    }

    public function onBootstrap(MvcEvent $e)
    {
        /* @var $e \Zend\Mvc\MvcEvent */
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $serviceManager = $e->getApplication()->getServiceManager();
        $request = $serviceManager->get('Request');

        if (!$request instanceof \Zend\Console\Request) {
            $this->bootstrapSession($e);
        }

        $events = $e->getTarget()->getEventManager();
        $events->attach(MvcEvent::EVENT_RENDER, array($this, 'onRenderError'));
    }

    public function onRenderError(MvcEvent $event)
    {
        $viewModel = $event->getResult();

        if($viewModel instanceof ViewModel){
            $variables = $viewModel->getVariables();

            if(isset($variables['exception'])){
                $e = $variables['exception'];

                if ($e instanceof \Ez\Api\Exception) {
                    $statusCode = $e->getStatusCode();
                } else {
                    $statusCode = \Zend\Http\Response::STATUS_CODE_500;
                }

                $response = new Response();
                $model = $response->prepareData(array('type' => get_class($e), 'message' => $e->getMessage(), 'code' => $e->getCode(), 'previous' => $e->getPrevious()),
                    $statusCode);
                $event->setResponse($response);
                $event->setViewModel($model);
            }
        }
    }


    public function getServiceConfig ()
    {
        return array(
            // invokables - for singletone cases, creating one instance when needed
            'invokables' => array (

            ),
            'factories' => array(
                // classes must implement factory interface __or__ values must be an callbacks
                'Songbook\Model\Cloud' => function($sm)
                {
                    $model = new \Songbook\Model\Cloud();
                    $model->setServiceLocator($sm);

                    $driver = new \Songbook\Model\Cloud\Driver\GDrive();
                    $model->setDriver($driver);

                    return $model;
                },
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

                'Songbook\Service\Setting' => function  ($sm)
                {
                    $service = new \Songbook\Service\Setting();
                    $service->setServiceLocator($sm);
                    return $service;
                },

                'Songbook\Service\Content' => function  ($sm)
                {
                    $service = new \Songbook\Service\Content();
                    $service->setServiceLocator($sm);
                    return $service;
                },

                'Songbook\Service\Mail' => function  ($sm)
                {
                    $service = new \Songbook\Service\Mail();
                    $service->setServiceLocator($sm);
                    return $service;
                },

                'User\Service\User' => function  ($sm)
                {
                    $service = new \User\Service\User();
                    $service->setServiceLocator($sm);
                    return $service;
                },

                'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
                    } else {
                        $sessionManager = new SessionManager();
                    }
                    Container::setDefaultManager($sessionManager);
                    return $sessionManager;
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