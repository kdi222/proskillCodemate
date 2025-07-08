<?php


require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot .'/lib/blocklib.php'); 
 

global $PAGE,$USER,$CFG;

$context = $PAGE->context;
require_login();

$PAGE->set_url('/local/lms_reports/index.php');
$PAGE->set_title('Reports');
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
// Query to retrieve feedback questions

// Get all courses
$courses = $DB->get_records_menu('course', [], '', 'id,fullname');
 // Display the course selection dropdown if no course is selected
 echo '<form method="post" class="row">';
 echo '<label for="courseid">Select a Course:</label>';
 echo '<select name="courseid" id="courseid" class="form-control col-6">';
 
 foreach ($courses as $courseId => $courseName) {
     echo '<option value="' . $courseId . '">' . $courseName . '</option>';
 }

 echo '</select>';
 echo '<input type="submit" value="Show Report" class="btn btn-success">';
 echo '</form>';

// Check if a course is selected
if (isset($_POST['courseid'])) {
        // Replace 'your_course_id' with the actual ID of your course
       
        $courseId = required_param('courseid', PARAM_INT);
        $downloadUrl  =  new moodle_url("/local/lms_reports/download_feedback.php?courseid=".$courseId);
        // Replace 'your_user_id' with the actual ID of your user
        //$userId = required_param('userid', PARAM_INT);
        echo "<a href=".$downloadUrl." class='float-right btn btn-success'>Download Report</a><br>";
        // Get all feedback responses for the user in the course
        $feedbackResponses = $DB->get_records_sql(
            "SELECT
                        fi.id,c.fullname as course_name,
                        g.name AS groupname,
                        u.firstname, u.lastname,
                        fi.name AS question_name,
                        fv.value,
                        fc.timemodified
                    FROM
                        mdl_feedback AS f
                    JOIN
                        mdl_feedback_item AS fi ON fi.feedback = f.id
                    JOIN
                        mdl_feedback_completed AS fc ON fc.feedback = f.id
                    JOIN
                        mdl_feedback_value AS fv ON fv.item = fi.id 
                    JOIN
                        mdl_course AS c ON f.course = c.id
                    JOIN
                        mdl_user AS u ON fc.userid = u.id
                    JOIN 
                        mdl_groups_members gm ON fc.userid = gm.userid
                    JOIN mdl_groups g ON gm.groupid = g.id
                    WHERE  f.course = :courseid ORDER BY fc.timemodified",
                ['courseid' => $courseId],0 , 0
        );

        // Display the results
        echo '<h2>Feedback Questions and Responses</h2>';
        echo '<table class="table">';
        echo '<tr><th>Course Name</th><th>Enrolled Group</th><th>Student Name</th><th>Question</th><th>Response</th><th>Date Time</th></tr>';

        foreach ($feedbackResponses as $response) {
            echo '<tr>';
                echo '<td>' . $response->course_name . '</td>';
                echo '<td>' . $response->groupname. '</td>';
                echo '<td>' . $response->firstname . ' ' . $response->lastname . '</td>';
                echo '<td>' . $response->question_name . '</td>';
               
                echo '<td>' . $response->value . '</td>';
                echo '<td>' . userdate($response->timemodified) . '</td>';
            echo '</tr>';
        }

        echo '</table>';

    } 
       
    

echo $OUTPUT->footer();
?>