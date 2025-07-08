<?php

//namespace local_mockstatus;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");

use external_api;
use external_function_parameters;
use external_value;
use context_course;

class local_mockstatus_external extends external_api {

    public static function get_students_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'Course ID')
        ]);
    }

    public static function get_students($courseid) {
        global $DB;

        $context = context_course::instance($courseid);
        self::validate_context($context);
        require_capability('moodle/course:viewparticipants', $context);

        $students = get_enrolled_users($context);
       
        $studentlist[] = ['id' => 0 , 'name' => 'Select Student'];

        foreach ($students as $student) {
            $studentlist[] = ['id' => $student->id, 'name' => fullname($student)];
        }

        return $studentlist;
    }

    public static function get_students_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new external_value(PARAM_INT, 'Student ID'),
                'name' => new external_value(PARAM_NOTAGS, 'Student name')
            ])
        );
    }
}
