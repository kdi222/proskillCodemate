<?php

$functions = [
    'local_mockstatus_get_students' => [
        'classname' => 'local_mockstatus\external',
        'methodname' => 'get_students',
        'classpath' => 'local/mockstatus/externallib.php',
        'description' => 'Fetches students enrolled in a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'moodle/course:viewparticipants',
    ],
];

$services = [
    'mockstatus_service' => [
        'functions' => ['local_mockstatus_get_students'],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];
