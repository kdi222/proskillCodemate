<?php
defined('MOODLE_INTERNAL') || die();

//require_once($CFG->dirroot.'/local/datahub/lib.php');

function xmldb_local_lms_reports_upgrade($oldversion = 0) {
    global $DB, $CFG;

    $result = true;

    $dbman = $DB->get_manager();

    if ($oldversion < 2018031224) { // Replace XXXXXXXXX with the version number when the table should be created
        // Define the table
        $table = new xmldb_table('lms_reports_mdata');
        
        // Define columns
        $table->addField(new xmldb_field('id', XMLDB_TYPE_INTEGER, 10, null, true, true));
        $table->addField(new xmldb_field('title', XMLDB_TYPE_CHAR, 255, null, true));
        $table->addField(new xmldb_field('value', XMLDB_TYPE_TEXT, null, null, true));

        // Add keys
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Create the table
        $dbman->create_table($table);

        // Plugin version update
        upgrade_plugin_savepoint(true, 2018031224,'local','lms_reports');
    }

   
    // Return true to signify that the upgrade was successful
    return true;
}
