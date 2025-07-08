<?php

namespace local_lms_reports\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class report_form extends \moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Add student selection dropdown
        
        $students = $this->get_students();
        $mform->addElement('select', 'studentid', get_string('student', 'local_lms_reports'), $students);
        $mform->setType('studentid', PARAM_INT);
        
        $this->add_action_buttons(false, get_string('generate_report', 'local_lms_reports'));
    }

    private function get_students() {
        global $DB;
        
        $students = $DB->get_records_sql_menu("SELECT id, CONCAT(firstname, ' ', lastname) AS name FROM {user} WHERE suspended = 0 AND  deleted = 0 AND id > 2");
        return $students;
    }
}
