<?php
$conf = array(
    'controllers' => array(
        'invokables' => array(
            'Song' => 'Songbook\Controller\SongController',
            'SongAjax' => 'Songbook\Controller\SongAjaxController',
            'SongConsole' => 'Songbook\Controller\SongConsoleController',
            'Tag' => 'Songbook\Controller\TagController',
            'TagAjax' => 'Songbook\Controller\TagAjaxController',
            'List' => 'Songbook\Controller\ListController',
            'ListAjax' => 'Songbook\Controller\ListAjaxController',
            'Sandbox' => 'Songbook\Controller\SandboxController',
            'Concert' => 'Songbook\Controller\ConcertController',
            'ConcertAjax' => 'Songbook\Controller\ConcertAjaxController',
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
    )
);

return $conf;