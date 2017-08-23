<?php
define('REFERENCE_SCHEMA', [
    'files' => [
        'create' => 'FileActions.create',
        'read' => 'FileActions.get',
        'update' => 'Event.updateEventFile',
        'delete' => 'FileActions.delete'
    ],
    'multiple_files' => [
        'files_to_sandbox'=>'FileActions.multipleCreateInSandbox',
        'create' => 'Event.createEventFiles',
        'read' => 'Event.getEventFiles',
        'update_form' => 'Event.updateEventFilesForm',
        'update'=>'Event.createEventFiles',
        'delete' => 'FileActions.delete'
    ],
    'users' => [
        'create' => '',
        'read' => 'User.find',
        'update' => '',
        'delete' => ''
    ],
    'institutes' => [
        'create' => '',
        'read' => 'Institutes.read',
        'update' => '',
        'delete' => ''
    ],
    'departments' => [
        'create' => '',
        'read' => 'Departments.find',
        'update' => '',
        'delete' => ''
    ],
    'document' => [
        'create' => '',
        'read' => 'DocumentHandler.findDocument',
        'update' => '',
        'delete' => ''
    ],
    'userfiles' => [
        'create' => 'FileActions.create',
        'read' => 'FileActions.get',
        'update' => 'User.updateFile',
        'delete' => 'FileActions.delete'
    ],
    'address_object'=> [
        'create' => 'Address.save',
        'read' => 'Address.read',
        'update' => 'Address.save',
        'delete' => ''
    ],
    'full_address_object'=> [
        'create' => 'Address.save',
        'read' => 'Address.read',
        'update' => 'Address.save',
        'delete' => ''
    ],
    'address_country' => [
        'create' => '',
        'read' => 'Country.getName',
        'update' => '',
        'delete' => ''
    ]
]);