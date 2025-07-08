<?php

use core_completion\progress;

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot .'/lib/blocklib.php'); 
require_once(dirname(__FILE__).'/locallib.php');
global $PAGE,$USER,$CFG;

$context = $PAGE->context;
require_login();
echo html_writer::script(file_get_contents(__DIR__ . '/js/jquery-progresspiesvg.js'));
$data = new report_overviewstats();
$PAGE->set_url('/local/lms_reports/index.php');
$PAGE->set_title('Reports');
$PAGE->set_pagelayout('customreport');
$PAGE->set_heading("Course Progress Report");
echo $OUTPUT->header();

$courseprogress = array();
$coursecount = 0;
$isteacher = false;

// Cache the student role IDs
$studentroles = $DB->get_records_sql("SELECT id FROM {role} WHERE archetype = 'student'");
$studentroleids = array_keys($studentroles);

// Fetch all courses
$mycourses = $DB->get_records('course', array());

// Prepare the data for display
$courseprogress = [];
$coursecount = 0;

foreach ($mycourses as $course) {
    $coursecontext = context_course::instance($course->id);

    // Get the enrolled students for the course in bulk
    list($roleidsql, $params) = $DB->get_in_or_equal($studentroleids, SQL_PARAMS_NAMED, 'roleid');
    $params['contextid'] = $coursecontext->id;
    $students = $DB->get_records_sql("
        SELECT DISTINCT u.id
        FROM {user} u
        JOIN {role_assignments} ra ON ra.userid = u.id
        WHERE ra.contextid = :contextid AND ra.roleid $roleidsql
    ", $params);

    $courseprogress[] = get_course_progress($course, $students, true, ++$coursecount);
}

// Prepare the HTML table
$table = "<table class='table'>
    <thead>
        <tr>
            <th>Course Name</th>
            <th>Course Category</th>
            <th>Course Start Date</th>
            <th>Total Enrolled</th>
            <th>Course %</th>
        </tr>
    </thead>
    <tbody>";

foreach ($courseprogress as $course) {
    if ($course->enrolledStudents > 0) {
        $category = core_course_category::get($course->category);
        $courseUrl = new moodle_url("/local/lms_reports/progressreport.php?courseid=" . $course->id);
        $table .= "<tr class='{$course->backColor}'>
            <td><a href='$courseUrl'>{$course->fullname}</a></td>
            <td>{$category->name}</td>
            <td>{$course->startdate}</td>
            <td>{$course->enrolledStudents}</td>
            <td><span class='progress-percent'>{$course->percentage}%</span></td>
        </tr>";
    }
}

$table .= "</tbody></table>";

echo $table;

function get_course_progress($course, $students, $loadprogress, $coursecount) {
    $courseprogress = new stdClass();
    $courseprogress->id = $course->id;
    $courseprogress->fullname = format_text($course->fullname);
    $courseprogress->shortname = format_text($course->shortname);
    $courseprogress->category = $course->category;
    $courseprogress->startdate = date("Y M, d", $course->startdate);
    $courseprogress->enddate = !empty($course->enddate) ? date("Y M, d", $course->enddate) : '';
    $courseprogress->timecreated = $course->timecreated;
    $courseprogress->backColor = ($coursecount % 2 == 0) ? 'even-row' : 'odd-row';

    if ($loadprogress) {
        $totalpercentage = 0;
        foreach ($students as $student) {
            $totalpercentage += progress::get_course_progress_percentage($course, $student->id);
        }
        $courseprogress->percentage = count($students) > 0 ? ceil($totalpercentage / count($students)) : 0;
    } else {
        $courseprogress->percentage = -1;
    }

    $courseprogress->enrolledStudents = count($students);
    return $courseprogress;
}

function get_total_time_spent_in_course($userid, $courseid) {
  global $DB;
  
  // Define session timeout in seconds (e.g., 30 minutes)
  $sessionTimeout = 30 * 60;
  
  // Fetch log entries for the user and course
  $sql = "SELECT timecreated
          FROM {logstore_standard_log}
          WHERE userid = :userid AND courseid = :courseid
          ORDER BY timecreated ASC";
  $params = ['userid' => $userid, 'courseid' => $courseid];
  $logs = $DB->get_records_sql($sql, $params);
  
  if (empty($logs)) {
      return 0; // No log entries found
  }

  // Calculate total time spent
  $totalTimeSpent = 0;
  $lastTime = 0;
  foreach ($logs as $log) {
      if ($lastTime > 0) {
          $duration = $log->timecreated - $lastTime;
          if ($duration < $sessionTimeout) {
              $totalTimeSpent += $duration;
          }
      }
      $lastTime = $log->timecreated;
  }
  
  return $totalTimeSpent;
}


$PAGE->requires->js_call_amd('local_lms_reports/userstats');
echo $OUTPUT->footer();