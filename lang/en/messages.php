<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flash Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for flash messages throughout
    | the application. These messages are typically used in controllers
    | after performing CRUD operations.
    |
    */

    'created' => ':resource created successfully.',
    'updated' => ':resource updated successfully.',
    'deleted' => ':resource deleted successfully.',
    'restored' => ':resource restored successfully.',

    'error' => [
        'delete_has_relations' => 'Cannot delete :resource because it has related data.',
        'unauthorized' => 'You are not authorized to perform this action.',
        'not_found' => ':resource not found.',
        'self_delete' => 'Cannot delete yourself.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Resource Names
    |--------------------------------------------------------------------------
    */

    'resources' => [
        'area' => 'Area',
        'pop' => 'POP',
        'router' => 'Router',
        'switch' => 'Switch',
        'olt' => 'OLT',
        'ont' => 'ONT',
        'access_point' => 'Access Point',
        'pole' => 'Pole',
        'odp' => 'ODP',
        'tower' => 'Tower',
        'cable' => 'Cable',
        'joint_box' => 'Joint Box',
        'odf' => 'ODF',
        'slack' => 'Slack',
        'splitter' => 'Splitter',
        'server' => 'Server',
        'site' => 'Site',
        'user' => 'User',
        'company' => 'Company',
        'connection' => 'Connection',
        'cpe' => 'CPE',
    ],
];
