<?php

require_once('../../config.php');
require_once($CFG->dirroot.'/local/lms_reports/classes/form/report_form.php');
require_once($CFG->libdir . '/gradelib.php');

require_login();

$userid = optional_param('id','',PARAM_INT);
$PAGE->set_url(new moodle_url('/local/lms_reports/activity_report.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Reports');
$PAGE->set_pagelayout('customreport');
$PAGE->set_heading(" Course & Activity Progress Report");
echo $OUTPUT->header();
// Get the cache instance
$cache = cache::make('local_lms_reports', 'user_custom_data');

$sitecontext = context_system::instance();
if(has_capability('local/lms_reports:show_report_dashboard_graph', $sitecontext)){
    $mform = new \local_lms_reports\form\report_form();
    $studentid =  $USER->id;
    if ($mform->is_cancelled()) {
        redirect(new moodle_url('/local/lms_reports/activity_report.php'));
    } else if ($data = $mform->get_data()) {
        $studentid = $data->studentid;
        $report = generate_report($studentid);
    }

    $mform->display();
} else if(isset($userid) && $userid == $USER->id) {
    $studentid = $userid;
    $report = generate_report($studentid);
} else {
    print_error('User id not found', 'error', '', 'User id not found.');

}
$cachekey = 'customdata_' . $studentid;
?>
 <div class="card">
            <div class="card-header bg-dark ">
                <h3 class="text-white">Student Information</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <?php 
                         $user = \core_user::get_user($studentid);
                        $userpicture = new user_picture($user); 
                        $userpicture->size = 1; // Size f1.
                        $profileimageurl = $userpicture->get_url($PAGE);
                         ?>
                        <img src="<?=$profileimageurl;?>" alt="Student Image" class="img-fluid rounded-circle mb-3" style="max-width: 200px;"><br>
                        <strong><?php echo fullname($user); ?></strong>
                    </div>
                    <div class="col-md-4">
                        <table class="table table-striped table-bordered">
                            <tbody>
                               
                                <tr>
                                    <th scope="row">Group Name</th>
                                    <td><?php  echo get_student_group($studentid); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Program Start Date(Y-M-D)</th>
                                    <td><?php 
                                     $fields = profile_get_user_fields_with_data($studentid);
                                    echo userdate($fields[0]->field->data,'%Y-%m-%d');?></td>
                                </tr>
                                <tr>
                                    <th scope="row">Rank</th>
                                    <td>1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4">
                    <?php $data = $cache->get($cachekey); 
                        //print_r($customdata);
                        
                        // Prepare data for the pie chart
                        $labels = ['Total Mark','Total Taken'];
                        $values = [$data['totalMark'], $data['totalTaken']];

                        // Create a new pie chart
                        $chart = new core\chart_pie();
                        $chart->set_doughnut(true);
                        $chart->set_title("My Progress ".$data['totalPersentage']."%");
                        $series = new core\chart_series('',$values);
                        $chart->add_series($series);
                        $chart->set_labels($labels);
                        echo $OUTPUT->render($chart);

                    ?>
                    </div>
                </div>
            </div>
        </div>


<?php 

if (!empty($report)) {
$totalMark = 800;
$totalTaken = 0;
$totalPersentage = 0;

foreach ($report as $course): 
    $totalMaxg = 0;
    $totalgrade = 0;
    $totalPassg = 0;
?>
    <table class="table table-bordered">
        <tr class="bg-dark text-light">
            <th colspan="7"><?php echo $course['course_name']; ?></th>
        </tr>
        <tr>
            <th>Activity Name</th>
            <th>Mod Name</th>
            <th>Grade Max</th>
            <th>Grade Pass</th> 
            <th>Grade</th>
            <th>Submitted</th> 
            
            <th>Date Graded</th>
        </tr>
        <?php foreach ($course['grades'] as $grade): 
            $totalMaxg += $grade['grademax'];
            $totalPassg += $grade['gradepass'];
            $totalgrade += $grade['grade'];
            //echo "<pre>"; print_r($grade);
            $totalTaken += $grade['grade'];
            ?>
            <tr>
                <td><?php echo $grade['activity_name']; ?></td>
                <td><?php echo $grade['mod_name']; ?></td>
                <td><?php echo round($grade['grademax']); ?></td>
                <td><?php echo round($grade['gradepass']); ?></td>
                <td><?php echo round($grade['grade']); ?></td>
                <td><?php echo ($grade['submitted']) ? userdate($grade['submitted']) : ''; ?></td>
                <td><?php echo ($grade['dategraded']) ? userdate($grade['dategraded']) : ''; ?></td>
            </tr>
           
        <?php endforeach; 
         $user_data['totalTaken'] = $totalTaken;
         $user_data['totalMark'] = $totalMark;
         $user_data['totalPersentage'] = ($totalTaken > 0) ? floor(($totalTaken / $totalMark) * 100) : 0;
         //
         $cache->set($cachekey, $user_data);
        ?>
        <tr>
                <th colspan="2">Total</th>
               
                <th><?php echo $totalMaxg; ?></th>
                <th><?php echo $totalPassg; ?></th>
                <th><?php echo $totalgrade; ?></th>
                <th></th>
            </tr>
    </table>
<?php 

endforeach; 
}

echo $OUTPUT->footer();

function generate_report($studentid) {
    global $DB;
    
    $courses = enrol_get_users_courses($studentid);
    $report = [];

    if ($courses) {
        foreach ($courses as $course) {
            $course_info = [
                'course_name' => format_string($course->fullname),
                'grades' => []
            ];

            $context = context_course::instance($course->id);
            $modinfo = get_fast_modinfo($course);
            $cms = $modinfo->get_cms();

            foreach ($cms as $cm) {
                if ($cm->uservisible) {
                    $module_type = $cm->modname;
                    $instance_id = $cm->instance;

                    $grades = grade_get_grades($course->id, 'mod', $module_type, $instance_id, $studentid);
                    //echo "<pre>";print_r($grades); echo "</pre>";
                    if (!empty($grades->items)) {
                        foreach ($grades->items as $item) {
                            if (isset($item->grades[$studentid])) {
                                $grade = $item->grades[$studentid];
                             //  
                                $course_info['grades'][] = [
                                    'activity_name' => format_string($cm->name),
                                    'mod_name' => format_string($cm->modname),
                                    'grademax' => $item->grademax,
                                    'gradepass' => $item->gradepass,
                                    'grade' => $grade->grade,
                                    'submitted' => $grade->datesubmitted,
                                    'dategraded' => $grade->dategraded
                                ];
                            }
                        }
                    } else {
                       
                    }
                }
            }
            // Retrieve custom grades from mockstatus
            $mockgrades = $DB->get_records('local_mockstatus', ['studentid' => $studentid, 'courseid' => $course->id]);
            
            foreach ($mockgrades as $mockgrade) {
                $course_info['grades'][] = [
                    'activity_name' => $mockgrade->type,
                    'mod_name' => 'Internal',
                    'grademax' => 10,  // Example passing grade, replace with real data if available
                    'gradepass' => 6,  // Example passing grade, replace with real data if available
                    'grade' => ($mockgrade->techmark + $mockgrade->communicationmark + $mockgrade->practicalmark),  // Assuming techmark is the grade, adjust accordingly
                    'dategraded' => $mockgrade->activitydate,  // Assuming submission date is the same as activity date, adjust accordingly
                   
                ];
            }
        

            $report[] = $course_info;
        }
    } else {
        $report[] = [
            'course_name' => null,
            'grades' => [
                [
                    'activity_name' => null,
                    'grade' => get_string('nocourses', 'local_lms_reports')
                ]
            ]
        ];
    }

    return $report;
}



