<?php
$conf = array(
    'controllers' => array(
        'invokables' => array(
            'Song' => 'Songbook\Controller\SongController',
            'SongAjax' => 'Songbook\Controller\SongAjaxController',
            'Tag' => 'Songbook\Controller\TagController',
            'TagAjax' => 'Songbook\Controller\TagAjaxController',
            'List' => 'Songbook\Controller\ListController',
            'ListAjax' => 'Songbook\Controller\ListAjaxController',
        )
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(

           'default' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/:controller[/][:action][/:id]',
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

    'view_manager' => array(
        'template_path_stack' => array(
            'songbook' => __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy'
        ),
    ),

    'doctrine' => array(
        'driver' => array(
            'song_entity' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'paths' => array(
                    __DIR__ . '/../src/Songbook/Entity'
                )
            ),

            'orm_default' => array(
                'drivers' => array(
                    'Songbook\Entity' => 'song_entity'
                )
            )
        )
    )
);

return $conf;