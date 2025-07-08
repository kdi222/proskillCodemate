<?php

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_mockstatus', get_string('pluginname', 'local_mockstatus'));
    $ADMIN->add('localplugins', $settings);
}

