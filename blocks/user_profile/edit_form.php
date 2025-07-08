<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/formslib.php');
 
class block_user_profile_edit_form extends block_edit_form {

    // Perform some extra moodle validation
    function validation($data, $files) {
        return $this->validation_high_security($data, $files);    
    }
 
    protected function specific_definition($mform) {
 		global $CFG,$PAGE,$USER;
        $context = context_system::instance();
        $roles = get_user_roles($context, $USER->id);
        $companymanager = false;
        
        foreach ($roles as $role) {
            if ($role->shortname == 'paradisoprojectmanager') {
                $companymanager = true;
                break;
            }
        }

        
        // Fields for editing HTML block title and contents.
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('advcheckbox', 'config_userlevelup', get_string('config_userlevelup', 'block_user_profile'));
            
        
    }


    function validation_high_security($data, $files) {
        global $DB, $CFG, $db, $USER;
        
        if(isset($data['config_querysql']) && !empty($data['config_querysql'])){
            $errors = parent::validation($data, $files);

            $sql = $data['config_querysql'];
            $sql = trim($sql);

            // Simple test to avoid evil stuff in the SQL.
            if (preg_match('/\b(ALTER|CREATE|DELETE|DROP|GRANT|INSERT|INTO|TRUNCATE|UPDATE|SET|VACUUM|REINDEX|DISCARD|LOCK)\b/i', $sql)) {
                $errors['config_querysql'] = get_string('notallowedwords', 'block_paradiso_recommendation');

            // Do not allow any semicolons.
            } else if (strpos($sql, ';') !== false) {
                $errors['config_querysql'] = get_string('nosemicolon', 'report_customsql');

            // Make sure prefix is prefix_, not explicit.
            } else if ($CFG->prefix != '' && preg_match('/\b' . $CFG->prefix . '\w+/i', $sql)) {
                $errors['config_querysql'] = get_string('noexplicitprefix', 'block_paradiso_recommendation');

            // Now try running the SQL, and ensure it runs without errors.
            } else {
                
                $rs = $DB->get_records_sql($sql, null, $limitfrom = 0, $limitnum = "");

                if (!$rs) {
                    $errors['config_querysql'] = get_string('queryfailed', 'block_paradiso_recommendation', $db->ErrorMsg());
                } else if (!empty($data['singlerow'])) {
                    if (rs_EOF($rs)) {
                        $errors['config_querysql'] = get_string('norowsreturned', 'block_paradiso_recommendation');
                    }
                }

                // if ($rs) {
                //     $rs->close();
                // }
            }

            return $errors;
        }
    }

}   