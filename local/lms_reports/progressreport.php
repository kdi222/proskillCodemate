<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot .'/local/lms_reports/reportlib.php'); 
global $PAGE,$USER,$CFG;
//Librerias para traer los datos de los reportes para el dashboard, en la variable $charts quedan alojados los datos necesarios para hacer las graficas
require_once(dirname(__FILE__).'/locallib.php');

require_once($CFG->libdir . '/phpspreadsheet/vendor/autoload.php'); // Adjust the path as needed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_login();

$courseId = optional_param('courseid','',PARAM_INT);
$data = new report_overviewstats();
// Check for necessary permissions here.

$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('customreport');
$PAGE->set_url('/local/lms_report/report.php');
$PAGE->set_title(get_string('student_progress', 'local_lms_reports'));
$PAGE->set_heading(get_string('student_progress', 'local_lms_reports'));

echo $OUTPUT->header();

$sql = "SELECT c.id, c.fullname, COUNT(DISTINCT ue.userid) AS enrolled_students_count
FROM {course} c
INNER JOIN {enrol} e ON c.id = e.courseid
INNER JOIN {user_enrolments} ue ON e.id = ue.enrolid
INNER JOIN {user} u ON ue.userid = u.id
INNER JOIN {role_assignments} ra ON u.id = ra.userid
INNER JOIN {role} r ON ra.roleid = r.id AND r.id != 2
WHERE r.shortname = 'student' AND r.id = 5
GROUP BY c.id, c.fullname
HAVING enrolled_students_count >= 1";

// Execute the SQL query
$courses = $DB->get_records_sql($sql);
echo '<form method="post" class="row p-5" >';
echo '<label for="courseid">Select a Course:</label>';
echo '<select name="courseid" id="courseid" class="form-control col-3">';
echo '<option value="0" >Select Course</option>';
foreach ($courses as $course) {
   $selected = ''; // Reset $selected for each iteration
   
   if((isset( $_POST['courseid']) && $course->id == $_POST['courseid']) || $courseId == $course->id) {
       $selected = "selected";
   }
   
   echo '<option value="' . $course->id . '" ' . $selected . '>' . format_string($course->fullname) . '</option>';
}

echo '</select>';
echo '<input type="submit" value="Show Report" class="btn btn-success mr-3 ml-3">';
echo '</form>';
$where ="";

if ($_POST['courseid'] > 0 || $courseId > 0) {
    //echo "<a href=".new moodle_url('/local/course_feedback/download_feedback.php?courseid='.$_POST['courseid'])." class='btn btn-danger '>Download Report</a>";
    $cid = ($_POST['courseid']) ? $_POST['courseid'] : $courseId;
   
    $downloadUrl  =  new moodle_url("/local/lms_reports/progress_download.php?courseid=".$cid);
    echo "<a href=".$downloadUrl." class='float-right btn btn-success'>Download Report</a><br>";

    $where .= " AND c.id =".$cid;

    // Define your SQL query to fetch the required data
    $sql = "
    SELECT
        u.firstname AS firstname,
        u.lastname AS lastname,
        c.fullname AS coursename,
        cc.timeenrolled AS timeenrolled,
        c.id AS courseid,
        u.id AS userid,
        CASE WHEN cc.timecompleted IS NOT NULL THEN 'Completed' ELSE 'Not Completed' END AS coursecompletionstatus,
        COUNT(DISTINCT CASE WHEN cmc.completionstate = 1 THEN cmc.id END) AS activitiescompleted,
        COUNT(DISTINCT cm.id) AS totalactivities,
        ROUND(
            (COUNT(DISTINCT CASE WHEN cmc.completionstate = 1 THEN cmc.id END) / COUNT(DISTINCT cm.id)) * 100, 2
        ) AS courseprogresspercentage
    FROM {user} u
    JOIN {user_enrolments} ue ON u.id = ue.userid
    JOIN {enrol} e ON ue.enrolid = e.id
    JOIN {course} c ON e.courseid = c.id
    LEFT JOIN {course_completions} cc ON ue.userid = cc.userid AND c.id = cc.course
    LEFT JOIN {course_modules} cm ON c.id = cm.course
    LEFT JOIN {course_modules_completion} cmc ON cm.id = cmc.coursemoduleid AND cmc.userid = u.id
    WHERE ue.status = 0 $where
    GROUP BY u.id, u.firstname, u.lastname, c.id, c.fullname, cc.timeenrolled, cc.timecompleted
";

// Execute the query
$report_data = $DB->get_records_sql($sql);

}


        
                
      // Use Moodle's output rendering functions to create an HTML view
      $output = \html_writer::start_tag('table', ['class' => 'table', 'id' => 'example']);
      $output .= \html_writer::start_tag('thead');
      $output .= \html_writer::start_tag('tr');
        $output .= \html_writer::tag('th', 'Full Name');
        // $output .= \html_writer::tag('th', 'Last Name');
        $output .= \html_writer::tag('th', 'Course Name');
        $output .= \html_writer::tag('th', 'Total Activities');
        $output .= \html_writer::tag('th', 'Activities Completed');
        $output .= \html_writer::tag('th', 'Course Progress');
        $output .= \html_writer::tag('th', 'Time Spent');
        $output .= \html_writer::tag('th', 'Course Completion Status');
      $output .= \html_writer::end_tag('tr');
      $output .= \html_writer::end_tag('thead');
      $output .= \html_writer::start_tag('tbody');

      foreach ($report_data as $row) {
            $progressclass = round($row->courseprogresspercentage) > 70 ? 'bg-success' : (round($row->courseprogresspercentage) > 30 ? 'bg-warning' : 'bg-danger');
          $output .= \html_writer::start_tag('tr');
          $output .= \html_writer::tag('td', $row->firstname." ".$row->lastname);
        //  $output .= \html_writer::tag('td', $row->lastname);
          $output .= \html_writer::tag('td', $row->coursename);
          $output .= \html_writer::tag('td', $row->totalactivities);
          $output .= \html_writer::tag('td', $row->activitiescompleted);
          //$output .= \html_writer::tag('td', round($row->courseprogresspercentage). "%");
        /*  $output .= \html_writer::tag('td', '<div style="width: 400px;background-color: #e9ecef;border-radius:50px;">
          <div class="progress-bar '.$progressclass.'" role="progressbar" aria-valuenow="'.round($row->courseprogresspercentage).'" aria-valuemin="0" aria-valuemax="100" style="max-width: '.round($row->courseprogresspercentage).'%; border-radius:50px;">
          <span class="title">'.round($row->courseprogresspercentage).'%</span>
          </div></div>');*/
          $output .= \html_writer::tag('td', round($row->courseprogresspercentage).'%');
          $output .= \html_writer::tag('td', get_total_time_spent_in_course($row->userid, $row->courseid));
          $output .= \html_writer::tag('td', $row->coursecompletionstatus);
         
        
          $output .= \html_writer::end_tag('tr');
      }

      $output .= \html_writer::end_tag('tbody');
      $output .= \html_writer::end_tag('table');

      echo $output;

      function get_total_time_spent_in_course($userid, $courseid) {
        global $DB;
    
        // Define session timeout in seconds (e.g., 30 minutes)
        $sessionTimeout = 30 * 60;
    
        // Fetch log entries for the user and course in batches
        $batchSize = 1000;
        $totalTimeSpent = 0;
        $lastTime = 0;
        $offset = 0;
    
        do {
            // Note: LIMIT and OFFSET values are inserted directly into the query string
            $sql = "SELECT timecreated
                    FROM {logstore_standard_log}
                    WHERE userid = :userid AND courseid = :courseid
                    ORDER BY timecreated ASC
                    LIMIT $batchSize OFFSET $offset";
            $params = ['userid' => $userid, 'courseid' => $courseid];
    
            $logs = $DB->get_records_sql($sql, $params);
    
            foreach ($logs as $log) {
                if ($lastTime > 0) {
                    $duration = $log->timecreated - $lastTime;
                    if ($duration < $sessionTimeout) {
                        $totalTimeSpent += $duration;
                    }
                }
                $lastTime = $log->timecreated;
            }
    
            $offset += $batchSize;
        } while (count($logs) === $batchSize);
    
        // Format total time spent as hours and minutes
        $hours = floor($totalTimeSpent / 3600);
        $minutes = floor(($totalTimeSpent % 3600) / 60);
    
        return sprintf('%02d:%02d', $hours, $minutes);
    }
    
    
echo $OUTPUT->footer();
