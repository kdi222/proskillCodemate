<?php

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2018031225;  // Plugin version. aaaammddhh
$plugin->requires  = 2014051203;
$plugin->component = 'local_lms_reports';
$plugin->release   = '1.0 (Build: 20150522)';
$plugin->maturity  = MATURITY_BETA;
$plugin->cron      = 0;
$tasks = array(
    array(
        'classname' => 'local_lms_reports\task\cron_task',
        'blocking' => 0,
        'minute' => '0',
        'hour' => '0',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    )
);
