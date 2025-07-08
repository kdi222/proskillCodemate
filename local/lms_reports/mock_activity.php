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
$addMock  =  new moodle_url("/local/mockstatus/");
echo "<a href=".$addMock." class='float-right btn btn-success'>Add Mock Details</a><br>";
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
        lm.*,
        c.fullname as coursename,
        CONCAT(u.firstname,u.lastname) as username,
        CONCAT(ut.firstname,ut.lastname) as trainer
    FROM {local_mockstatus} lm
    JOIN {course} c ON lm.courseid = c.id
    LEFT JOIN {user} u ON lm.studentid = u.id
    LEFT JOIN {user} ut ON lm.trainerid = ut.id
    WHERE u.suspended = 0 $where
   ORDER BY activitydate
";

// Execute the query
$report_data = $DB->get_records_sql($sql);

}


        
                
      // Use Moodle's output rendering functions to create an HTML view
      $output = \html_writer::start_tag('table', ['class' => 'table table-bordered', 'id' => 'example']);
      $output .= \html_writer::start_tag('thead');
      $output .= \html_writer::start_tag('tr');
        $output .= \html_writer::tag('th', 'Mock Date');
        $output .= \html_writer::tag('th', 'Course Name');
        // $output .= \html_writer::tag('th', 'Last Name');
        $output .= \html_writer::tag('th', 'Student Name ');
        $output .= \html_writer::tag('th', 'Activity Name');
        $output .= \html_writer::tag('th', 'Questions');
        $output .= \html_writer::tag('th', 'Practical Question');
        $output .= \html_writer::tag('th', 'Technical Mark');
        $output .= \html_writer::tag('th', 'Communication Mark');
        $output .= \html_writer::tag('th', 'Practical Mark');
        $output .= \html_writer::tag('th', 'Graded By');
        $output .= \html_writer::tag('th', 'Graded Date');
      $output .= \html_writer::end_tag('tr');
      $output .= \html_writer::end_tag('thead');
      $output .= \html_writer::start_tag('tbody');

      foreach ($report_data as $row) {
          
          $output .= \html_writer::start_tag('tr');
          $output .= \html_writer::tag('td', userdate($row->activitydate));
          $output .= \html_writer::tag('td', $row->coursename);
          $output .= \html_writer::tag('td', $row->username);
        //  $output .= \html_writer::tag('td', $row->lastname);
          $output .= \html_writer::tag('td', $row->type);
          $questions =  $row->question;
          $output .= \html_writer::tag('td', $row->question);
          $output .= \html_writer::tag('td', $row->practquestion);
          $output .= \html_writer::tag('td', $row->techmark);
          $output .= \html_writer::tag('td', $row->communicationmark);
          $output .= \html_writer::tag('td', $row->practicalmark);
          $output .= \html_writer::tag('td', $row->trainer);
          $output .= \html_writer::tag('td', $row->created_date);
          //$output .= \html_writer::tag('td', round($row->courseprogresspercentage). "%");
        /*  $output .= \html_writer::tag('td', '<div style="width: 400px;background-color: #e9ecef;border-radius:50px;">
          <div class="progress-bar '.$progressclass.'" role="progressbar" aria-valuenow="'.round($row->courseprogresspercentage).'" aria-valuemin="0" aria-valuemax="100" style="max-width: '.round($row->courseprogresspercentage).'%; border-radius:50px;">
          <span class="title">'.round($row->courseprogresspercentage).'%</span>
          </div></div>');*/
        
         
        
          $output .= \html_writer::end_tag('tr');
      }

      $output .= \html_writer::end_tag('tbody');
      $output .= \html_writer::end_tag('table');

      echo $output;

    
echo $OUTPUT->footer();
