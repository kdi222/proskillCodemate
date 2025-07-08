<?php
require_once ('../../config.php');
require_once ($CFG -> dirroot . '/my/lib.php');
require_once ($CFG -> dirroot . '/tag/lib.php');
require_once ($CFG -> dirroot . '/user/profile/lib.php');
require_once ($CFG -> libdir . '/filelib.php');
/**
* This file is not found, not working, comment this line
* @autor Hugo S.
* @since 18/06/2018
* @paradiso
* @ticket 21
*/
//require_once ($CFG -> dirroot . '/user/profile/field/multiselect/field.class.php');
require_once ($CFG -> dirroot . '/grade/querylib.php');
global $COURSE, $USER, $CFG, $DB, $OUTPUT, $PAGE;
require_login();
//get id user from url
$uid = required_param('uid', PARAM_INT);
//get user object
$user = $user = $DB -> get_record('user', array('id' => $uid));
$context = context_system::instance();
$PAGE -> set_url(new moodle_url('/blocks/myprogress/list_notstarted.php'));
$PAGE -> set_context($context);
// base, standard, course, mydashboard
$PAGE -> set_pagelayout('report');
$PAGE -> set_title(get_string('listinprogresscourses', 'block_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE -> set_heading(get_string('listinprogresscourses', 'block_myprogress', ($user->firstname.' '.$user->lastname)));
$PAGE -> requires -> css(new moodle_url('/blocks/myprogress/css/my_myprogress.css'));
$PAGE -> requires -> js(new moodle_url('/blocks/myprogress/js/common.js'));
$PAGE -> navbar -> add(get_string('listinprogresscourses', 'block_myprogress', ($user->firstname.' '.$user->lastname)));

echo $OUTPUT -> header();
echo $OUTPUT -> heading(get_string('listinprogresscourses', 'block_myprogress', ($user->firstname.' '.$user->lastname)));
//Build the table with the list of users
//get courses enrolled for this user
$courses = enrol_get_users_courses($uid,true);
//completed courses array
$completed = array();
$tt= array();
foreach ($courses as $course) {
	$course = $DB -> get_record('course', array('id' => $course -> id), '*', MUST_EXIST);
	// Load completion data.
	$info = new completion_info($course);
	/**
	* Jump course from iteration if course dont have 
	* course completion tracking
	* @author Esteban E. 
	* @since June 28 2017
	* @paradiso
	*/
	if(!$info->is_enabled())
	continue ;
	
	// Is course complete?
	$coursecomplete = $info -> is_course_complete($uid);
	// Has this user completed any criteria?
	$criteriacomplete = $info -> count_course_user_data($uid);
	// Load course completion.
	$params = array('userid' => $uid, 'course' => $course -> id);
	$ccompletion = new completion_completion($params);
	//is not the current course net started 
	if($criteriacomplete > 0 && !$coursecomplete){

		$template_data['course_link'] = new moodle_url('/course/view.php',array('id'=>$course->id));
		$template_data['course_name'] = $course->fullname;
		$template_data['enrollment_date'] = date('Y-m-d h:i',$ccompletion->timeenrolled);
		$tt[]=$template_data;
	}
	//$tt[]=$template_data;
}

$data['course_listing'] = $tt;

echo $OUTPUT->render_from_template('block_myprogress/list_inprogress', $data);
echo $OUTPUT -> footer();