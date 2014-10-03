<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Songbook\Controller\Song' => 'Songbook\Controller\SongController'
        )
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'song' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/song[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+'
                    ),
                    'defaults' => array(
                        'controller' => 'Songbook\Controller\Song',
                        'action' => 'index'
                    )
                )
            )
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'songbook' => __DIR__ . '/../view'
        )
    )
);

