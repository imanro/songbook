<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Songbook\Controller\Songbook' => 'Songbook\Controller\SongbookController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            'songbook' => __DIR__ . '/../view',
        ),
    ),
);

