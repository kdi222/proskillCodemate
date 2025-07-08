<?php

namespace local_mockstatus\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

class mockstatus_form extends \moodleform {
    public function definition() {
        global $DB, $PAGE;

        $mform = $this->_form;

        // Get all courses
        $courses = $DB->get_records_menu('course', null, '', 'id, fullname');
        $courseoptions = [];
        foreach ($courses as $id => $fullname) {
            $courseoptions[$id] = $fullname;
        }
        $mform->addElement('date_selector', 'activitydate', get_string('activitydate', 'local_mockstatus'));

         // Add a select element for courses
         $mform->addElement('select', 'courseid', get_string('courseid', 'local_mockstatus'), $courseoptions);
         $mform->setType('courseid', PARAM_INT);
 
         // Add an empty select element for students
         $mform->addElement('select', 'studentid', get_string('studentid', 'local_mockstatus'), []);
         $mform->setType('studentid', PARAM_INT);

         // Hidden empty input field
        $mform->addElement('hidden', 'student_id', '',array('id' => 'student_id'));
        $mform->setType('student_id', PARAM_RAW);

        // Add a select element for 'type' with predefined options
        $mform->addElement('select', 'type', get_string('type', 'local_mockstatus'), [
            'Mock-1' => 'Mock-1',
            'Mock-2' => 'Mock-2',
            'Mock-3' => 'Mock-3',
            'Git Profile' => 'Git Activity',
            'Soft Skill' => 'Soft Skill',
        ]);
        $mform->setType('type', PARAM_TEXT);

       

        // Add the questions and marks
        $repeatarray = [];
        $repeatarray[] = $mform->createElement('text', 'question', get_string('question', 'local_mockstatus'));
        $repeatarray[] = $mform->createElement('text', 'mark', get_string('mark', 'local_mockstatus'));
        $repeatarray[] = $mform->createElement('html', '<button type="button" class="remove_question">Remove</button>');

        $repeatno = 1; // Initial number of questions
        $repeatoptions = [];
        $repeatoptions['question']['type'] = PARAM_TEXT;
        $repeatoptions['mark']['type'] = PARAM_INT;

        $this->repeat_elements($repeatarray, $repeatno, $repeatoptions, 'question_repeats', 'question_add_fields', 1, get_string('addquestion', 'local_mockstatus'));

        $mform->addElement('textarea', 'pract_question', get_string('pract_question', 'local_mockstatus'));
        $mform->setType('pract_question', PARAM_TEXT);

        $mform->addElement('text', 'pract_mark', get_string('pract_mark', 'local_mockstatus'));
        $mform->setType('pract_mark', PARAM_INT);
        
        $mform->addElement('text', 'communicationmark', get_string('communicationmark', 'local_mockstatus'));
        $mform->setType('communicationmark', PARAM_INT);


        $mform->addElement('textarea', 'remark', get_string('remark', 'local_mockstatus'));
        $mform->setType('remark', PARAM_TEXT);

      

        $this->add_action_buttons();

        // Add JavaScript for AJAX functionality
        $PAGE->requires->js_call_amd('local_mockstatus/studentselector', 'init');
       
    }
}
