<?php

return [
    'path' => 'modules',

    'namespace' => 'Modules',

    'disabled' => [],

    'priority' => [],

    'routes' => [
        'web' => [
            'prefix' => '',
            'middleware' => ['web'],
        ],
        'api' => [
            'prefix' => 'api',
            'middleware' => ['api'],
        ],
    ],
];
