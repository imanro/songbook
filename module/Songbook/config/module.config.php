<?php

$conf = array(
    'controllers' => array(
        'invokables' => array(
            'song' => 'Songbook\Controller\SongController',
            'song-ajax' => 'Songbook\Controller\SongAjaxController',
            'songConsole' => 'Songbook\Controller\SongConsoleController',
            'tag' => 'Songbook\Controller\TagController',
            'tagAjax' => 'Songbook\Controller\TagAjaxController',
            'sandbox' => 'Songbook\Controller\SandboxController',
            'concert' => 'Songbook\Controller\ConcertController',
            'concertAjax' => 'Songbook\Controller\ConcertAjaxController',
            'content' => 'Songbook\Controller\ContentController',
            'oauth2' => 'Songbook\Controller\OAuth2Controller',
            'content-ajax' => 'Songbook\Controller\ContentAjaxController',
        )
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(

           'default' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/:controller[/][:action][/][:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Songbook\Controller\Song',
                        'action' => 'index'
                    )
                )
            ),
            'ajax' => array(
                'type' => '\Ez\Mvc\Router\Http\SegmentMap',
                'options' => array(
                    'route' => '/ajax/:controller[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'map' => array(
                        'controller' => array( '/.+/', '\0Ajax' ),
                    ),
                    'defaults' => array(
                        'controller' => 'Songbook\Controller\SongAjax',
                        'action' => 'index'
                    )
                )
            ),
        )
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
              'create-headers' => array(
                    'options' => array(
                        'route' => 'create-headers',
                        'defaults' => array(
                            'controller' => 'SongConsole',
                            'action' => 'create-headers'
                        )
                    )
                ),

                'import-songs' => array(
                    'options' => array(
                        'route' => 'import-songs (db|txt|txt-concerts|folder-slides|folder-texts) [<filename>]',
                        'defaults' => array(
                            'controller' => 'SongConsole',
                            'action' => 'import'
                        )
                    )
                )
             )
        )
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions' => true,

        'template_path_stack' => array(
            'songbook' => __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        ),
    ),

    //'doctrine_factories' => array('entitymanager' => 'Ez\Doctrine\Service\EntityManagerExtendedFactory'),

    'doctrine' => array(
        //'entitymanager' => array( 'orm_default' => array('connection' => 'orm_d') ),
        'driver' => array(
            /*
            'song_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../src/Songbook/Entity',
                )
            ),
            'user_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../../User/src/User/Entity'
                )
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Songbook\Entity' => 'song_entity',
                    'User\Entity' => 'user_entity'
                )
            )
            */
            'song_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver',
                'paths' => array( __DIR__ . '/doctrine' => 'Songbook\Entity' ),
                //'extension' => '.dcm.xml'
            ),
            'user_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver',
                'paths' => array( __DIR__ . '/../../User/config/doctrine' => 'User\Entity' ),
                //'extension' => '.dcm.xml'

            ),

            'orm_default' => array(
                'drivers' => array(
                    'Songbook\Entity' => 'song_entity',
                    'User\Entity' => 'user_entity'
                )
            )
        ),
        'configuration' => array(
            'orm_default' => array(
                'numeric_functions' => array(
                    'RAND' => 'DoctrineExtensions\Query\MySql\Rand'
                )
            )
        )
    ),

    'paths' => array(
        'tmp' => __DIR__ . '/../../../tmp'
    ),

    'cloud' => array(
        'gdrive' => array(
            'application_name' => 'Songbook',
            'auth_config_path' => __DIR__ . '/../../../config/client_secrets.json',
            'redirect_path' => '/oauth2/gdrive',
            'session_ns' => 'gdrive',
            'is_auth_offline' => true,
            'offline_token_path' => __DIR__ . '/../../../.credentials/gdrive_token.json',
        ),
        'sync_time_threshold_seconds' => 600
    ),

    'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'songbook',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            'Zend\Session\Validator\RemoteAddr',
            'Zend\Session\Validator\HttpUserAgent',
        ),
    ),
);

return $conf;