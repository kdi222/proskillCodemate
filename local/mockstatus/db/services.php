<?php

$functions = array(

    'local_mockstatus_get_students' => array(
        'classname' => 'local_mockstatus_external',
        'methodname' => 'get_students',
        'classpath' => 'local/mockstatus/externallib.php',
        'description' => 'Fetches students enrolled in a course',
        'type' => 'read',
        'ajax' => true,
        'capabilities' => 'mod/forum:viewdiscussion',
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE)
    )
);
