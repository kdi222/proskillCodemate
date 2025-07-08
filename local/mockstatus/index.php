<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/local/mockstatus/classes/form/mockstatus_form.php');

global $DB, $PAGE, $OUTPUT;

require_login();
$context = context_system::instance();

if (!has_capability('local/mockstatus:manage', $context)) {
    print_error('nopermission', 'error', '', null, 'You do not have permission to view this page');
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mockstatus/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_mockstatus'));
$PAGE->set_heading(get_string('pluginname', 'local_mockstatus'));

$mform = new \local_mockstatus\form\mockstatus_form();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/my'));
} else if ($data = $mform->get_data()) {
    global $USER;
    $record = new stdClass();
    $record->type = $data->type;
    $record->courseid = $data->courseid;
    $record->studentid = $data->student_id;
    $questions = $data->question;
    $marks = $data->mark;
    $combined = [];
    for ($i = 0; $i < count($questions); $i++) {
        $combined[] = [
            'question' => $questions[$i],
            'mark' => $marks[$i]
        ];
    }

    $json_object = json_encode($combined, JSON_PRETTY_PRINT);
    $record->question = $json_object;
    $record->techmark = array_sum($data->mark); // SUM of $data->mark element
    $record->practquestion = $data->pract_question;
    $record->communicationmark = $data->communicationmark;
    $record->practicalmark = $data->pract_mark;
    $record->remark = $data->remark;
    $record->activitydate = $data->activitydate;
    $record->trainerid = $USER->id;
  
    $DB->insert_record('local_mockstatus', $record);
    redirect(new moodle_url('/local/mockstatus/index.php'), get_string('datainserted', 'local_mockstatus'));
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
