<?php

defined('MOODLE_INTERNAL') || die();

$definitions = array(
    'user_custom_data' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'ttl' => 3600, // Time to live in seconds (1 hour)
    ),
);
