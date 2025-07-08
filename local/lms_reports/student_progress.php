<?php

use core_completion\progress;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot .'/lib/blocklib.php'); 
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/lib.php');
global $PAGE,$USER,$CFG;

$context = $PAGE->context;
require_login();
echo html_writer::script(file_get_contents(__DIR__ . '/js/jquery-progresspiesvg.js'));
$courseId = required_param('courseid', PARAM_INT);
$data = new report_overviewstats();
$PAGE->set_url('/local/lms_reports/index.php');
$PAGE->set_title('Reports');
$PAGE->set_pagelayout('standard');
$PAGE->set_heading("Course Progress Report");
echo $OUTPUT->header();
$courseData = array();
$courseprogress = get_usercourse_progress($courseId);
$courseClass = new local_analytics_utils();
$backLink  =  new moodle_url("/local/lms_reports/course_progress.php");
echo '<a href='.$backLink.' class="btn btn-sm btn-warning float-right">Back To Progress Report</a>';
echo '<p><strong> ' . $courseprogress->coursefullname . '</strong></p>';
$data1 = get_mean_dedication_time($courseId,'2024-01-01', '2024-05-03');

$table = "<table class='table'>";
$table .= "<tr><th>Student Name</th><th>Total Time Spent</th><th>Progress</th></tr>";
foreach($courseprogress->students as $student){
 //echo "<pre>";print_r($student);
  
    $table .= "<tr>";
        $table .= "<td>". $student->name."</td>";
        $table .= "<td></td>";
        $table .= '<td class=" "><div style="width: 400px;background-color: #e9ecef;">
        <div class="progress-bar '.$student->progressclass.'" role="progressbar" aria-valuenow="'.$student->progress.'" aria-valuemin="0" aria-valuemax="100" style="max-width: '.$student->progress.'%">
        <span class="title">'.$student->progress.'%</span>
        </div></div>
      
         
       </td>';
      
    $table .= "</tr>";
   
    
}
$table .= "<table>";

echo $table;


function get_usercourse_progress($courseid) {
    global $PAGE;
    $PAGE->set_context(context_system::instance());
    $data = new stdClass;
    $course = get_course($courseid);
    $data->coursefullname = format_text(trim($course->fullname));
    $data->coursesummary = format_text(trim(strip_tags($course->summary)));
    $data->students = [];

    $coursecontext = context_course::instance($courseid);
    $groupid = groups_get_user_groups($courseid, $USER->id);
    $students = get_role_users(5, $coursecontext);
    $roleUser = get_user_roles($coursecontext, $USER->id);
    $roleid = 0;
    foreach($roleUser as $key => $rusers){
        $roleid = $rusers->roleid;
    }
    if($roleid == 4){
        if(count($groupid[0]) > 0){
            $members = groups_get_groups_members($groupid[0]);
            foreach($members as $key => $member){
                if(!in_array($key, array_keys($students))){
                    unset($members[$key]);
                }
            }
            $students = $members;
        }
    }
    
    $studentcnt   = 0;
  //  $coursehandler = coursehandler::get_instance();
    foreach ($students as $studentid => $student) {
        $studentdata = new stdClass;
        $studentdata->index = ++$studentcnt;
        $studentdata->name = fullname($student);
        $studentdata->id = $studentid;
      //  $studentdata->lastaccess = $coursehandler->get_last_course_access_time($courseid, $studentid)->time;
        $progress = (int)progress::get_course_progress_percentage($course, $student->id);
        if (empty($progress)) {
            $progress = 0;
        }
        $studentdata->progress = $progress;
        $studentdata->progressclass = $progress > 70 ? 'bg-success' : ($progress > 30 ? 'bg-warning' : 'bg-danger');
        $data->students[] = $studentdata;
        unset($students[$studentid]);
    }
    return $data;

}


echo $OUTPUT->footer();