<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Course list block.
 *
 * @package    block_course_list
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core\notification;
include_once($CFG->dirroot . '/course/lib.php');

class block_course_calendar extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_course_calendar');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $CFG, $USER, $DB, $OUTPUT;

        if($this->content !== NULL) {
            return $this->content;
        }
        
        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        $courseId = optional_param('course_id', 1, PARAM_INT);
        $this->content->text = $this->get_course_dropdown($courseId) . $this->get_calendar_html($courseId);
    
        return $this->content;
    }

    private function get_calendar_html($courseId=1) {
        global $USER, $DB;
    
        
    
        $output = '<div id="course_calendar"> <div class="tab-wrap">';

        // Add Bootstrap tabs markup here
        $output .= '<input type="radio" id="upcoming" name="course_grid" class="tab" checked><label for="upcoming">Upcoming Session</label>';
        $output .= '<input type="radio" id="completed" name="course_grid" class="tab">
        <label for="completed">Recorded Session</label>';
            $output .= '<div class="tab__content" >';
                // Display upcoming events in a table
                $output .=  '<h3>Upcoming Events</h3>';
                $output .=  '<table class="table table-striped" >';
                $output .=  '<tr class="table-header"><th>Course Name</th><th>Activity Name</th><th>Module Name</th><th>Due Date</th><th>Topic Cover</th><th>Action</th></tr>';
               $upcomingEvents = $this->getBigBlueButtonData($courseId);
               $isTeacher = $DB->record_exists(
                'role_assignments',
                ['userid' => $USER->id, 'contextid' => context_course::instance($courseId)->id, 'roleid' => $DB->get_field('role', 'id', ['shortname' => 'editingteacher'])]
            );
    
            foreach ($upcomingEvents as $event) {
                    
                $cm = get_coursemodule_from_instance('bigbluebuttonbn', $event->id, 0, false, MUST_EXIST);
                $courseModuleId = $cm->id;
                
                // Get the topic name using a database query
                $sql = "SELECT s.id, s.section, s.name, cm.instance
                FROM {course_sections} s
                JOIN {course_modules} cm ON s.id = cm.section
                WHERE cm.id = :cmid
                AND cm.course = :courseid";
    
                $params = [
                'cmid' => $courseModuleId,
                'courseid' => $courseId,
                ];
    
                $sectioninfo = $DB->get_record_sql($sql, $params);
    
                $sqlTopics = "SELECT *
                FROM {session_topics}
                WHERE sessionid = :cmid
                AND courseid = :courseid";
    
                $params = [
                'cmid' => $event->id,
                'courseid' => $courseId,
                ];
    
                $topicsInfo = $DB->get_record_sql($sqlTopics, $params);
               
                $joinurl =  new moodle_url("/mod/bigbluebuttonbn/bbb_view.php?action=join&id=".$courseModuleId."&bn=".$event->id);
                $topicform =  new moodle_url("/local/course_feedback/topic_cover.php?courseid=".$courseId."&modid=".$event->id);
                $courseData = get_course($event->course);
                $output .=  '<tr>';
                $output .=  '<td>' . $courseData->fullname . '</td>';
                $output .=  '<td>' . $event->name . '</td>';
                    $output .=  '<td>' .$sectioninfo->name . '</td>';
                    $output .=  '<td>' . ($event->openingtime ? userdate($event->openingtime) : 'N/A') . '</td>';
                    $output .=  '<td>';
                    if(is_siteadmin($USER->id) || $isTeacher ){
                        $output .=  '<a href='.$topicform.' class="btn btn-success">Add Topic</a>';
                    }
                    $output .= $topicsInfo->topic;
                    $output .=  '</td>';
                $sql = "SELECT sessionid,reason FROM {mdl_batch_notification} WHERE groupid=? ";
                            $exist = $DB->get_record_sql($sql, array($event->id));
                            $sessionid = $exist->sessionid;
                if(empty($exist)){
                    if($event->openingtime <= time()){
                        $output .=  '<td><a href='. $joinurl . ' class="btn btn-success" target="_blank">Join</a></td>';
                    }
                } else {
                    $output .=  '<td><p class="text-danger">Session Cancelled</td>';
                }
               
                $output .=  '</tr>';
            }
               
    
            $output .=  '</table>';
            $output .= "</div>";
            $recordingLinks = $this->getBigBlueButtonRecordingLink($courseId);
            $output .= '<div class="tab__content" >';
            // Display completed events in a table
            $output .=  '<h3>Completed Events</h3>';
            $output .=  '<table class="table table-striped">';
            $output .=  '<tr><th>Activity Name</th><th>Module Name</th><th>Topic Covered</th><th>Session Date</th>';
            if(is_siteadmin($USER->id)){
                $output .=  '<th>Rating</th>';
            }
            $output .=  '<th>Action</th></tr>';
                foreach ($recordingLinks as $records) {
                   
                    $dateTime1 = DateTime::createFromFormat('U', $records->openingtime);
                    $dateTime2 = DateTime::createFromFormat('U', $records->enddate);
                    
                    // Calculate the difference
                    $interval = $dateTime1->diff($dateTime2);
                    
                    // Get hours and minutes
                    $hours = $interval->h + $interval->days * 24;
                    $minutes = $interval->i;
                    $duration = $hours.":".$minutes;
    
                    $cm = get_coursemodule_from_instance('bigbluebuttonbn', $records->modid, 0, false, MUST_EXIST);
                    
                    $courseModuleId = $cm->id;
    
                   // Get the topic name using a database query
                    $sql = "SELECT s.id, s.section, s.name
                    FROM {course_sections} s
                    JOIN {course_modules} cm ON s.id = cm.section
                    WHERE cm.id = :cmid
                    AND cm.course = :courseid";
    
                    $params = [
                    'cmid' => $courseModuleId,
                    'courseid' => $courseId,
                    ];
    
                    $sectioninfo = $DB->get_record_sql($sql, $params);
    
                    $sqlTopics = "SELECT *
                    FROM {session_topics}
                    WHERE sessionid = :cmid
                    AND courseid = :courseid";
        
                    $params = [
                    'cmid' => $records->id,
                    'courseid' => $courseId,
                    ];
        
                    $topicsInfo = $DB->get_record_sql($sqlTopics, $params);
                  
                    $recordingLink = "https://stream.fixityedx.com/playback/presentation/2.3/".$records->recordingid;
                    
                    $output .=  '<tr>';
                    $output .=  '<td>' . $records->name . '</td>';
                    $output .=  '<td>' .$sectioninfo->name . '</td>';
                    $output .=  '<td>';
                   
                    $output .= $topicsInfo->topic;
                    $output .=  '</td>';
                    $output .=  '<td>' . ($records->openingtime ? userdate($records->openingtime) : 'N/A') . '</td>';
                    if(is_siteadmin($USER->id)){
                    $output .=  '<td>' .$this->getModuleRating($records->modid). '</td>';
                    }
                    if($recordingLink != ""){
                        $output .=  '<td><a href="' . $recordingLink . '" target="_blank" class="btn btn-success">View Recording</a></td>';
                    }
                    
                    $output .=  '</tr>';
                }
                $output .=  '</table>';
            $output .= "</div>";
        $output .= "</div></div>";
    
    
        return  $output;
    }

    private function get_course_dropdown($courseId) {
        global $USER, $DB;
        if(is_siteadmin($USER->id)){
            $sql = "SELECT c.id, c.fullname, COUNT(DISTINCT ue.userid) AS enrolled_students_count
            FROM {course} c
            INNER JOIN {enrol} e ON c.id = e.courseid
            INNER JOIN {user_enrolments} ue ON e.id = ue.enrolid
            INNER JOIN {user} u ON ue.userid = u.id
            INNER JOIN {role_assignments} ra ON u.id = ra.userid
            INNER JOIN {role} r ON ra.roleid = r.id
            WHERE r.shortname = 'student' AND r.id = 5
            GROUP BY c.id, c.fullname
            HAVING enrolled_students_count > 1";
           
               // Execute the SQL query
               $courses = $DB->get_records_sql($sql);
        } else {
            $courses = enrol_get_users_courses($USER->id, false, 'id, shortname, fullname');
        }
        // Retrieve course details
       
        $dropdown = '<div class="">';
       
        $dropdown .= '<form action="#" method="post" class="d-flex w-100">';
        $dropdown .= '<select name="course_id" class="form-control w-25">';
        $dropdown .= '<option value="0">All Courses</option>';
        $selected = "";
        foreach ($courses as $course) {
            $selected = ($courseId == $course->id) ? "selected" : "";
            $dropdown .= '<option value="' . $course->id . '" ' . $selected . '>' . $course->fullname . '</option>';
        }
    
        $dropdown .= '</select>&nbsp;';
        $dropdown .= '<input type="submit" value="Show" class="btn btn-sm btn-success">';
        $dropdown .= '</form></div><br>';
        $course = get_course($courseId);
        $dropdown .= '<h4> Session List For :-'.$course->fullname.'</h4>';  
    
        return $dropdown;
    }

    public function getBigBlueButtonRecordingLink( $courseId) {
        global $DB;
    
        $apiEndpoint = 'https://stream.fixityedx.com/bigbluebutton/api';
        $secret = 'Tnh4IuNOnU2KnvezECaBpVKC7dNfJgSvDpNZzSWVLKk';
       
        $sql = "SELECT *, b.timemodified as enddate, b.id as modid
        FROM {bigbluebuttonbn_recordings} bbb
        JOIN {bigbluebuttonbn} b ON b.id = bbb.bigbluebuttonbnid
        WHERE bbb.courseid = :courseid AND bbb.status = 2";
    
        $params = ['courseid' => $courseId];
    
        // Execute the query
       return $DB->get_records_sql($sql, $params);
    
     
        
    }
    
    public function getModuleRating($activityId){
        global $DB;
       
        $sql = "SELECT COALESCE(SUM(fs.rating) / COUNT(fs.rating), 0) as AverageRating 
        FROM {course_feedback} cf
        JOIN {course_feedback_submission} fs ON fs.feedbackid = cf.id
        WHERE cf.acttivity_id = :acttivityid GROUP BY cf.acttivity_id";
    
        // Execute the query (replace $acttivityid with the actual value)
        $params = array('acttivityid' => $activityId); // Adjust the parameter value
        $result = $DB->get_record_sql($sql, $params);
       
        return  number_format($result->averagerating, 1);
    }
    
    public function getBigBlueButtonData($courseId) {
        global $DB, $USER;
        
        // Replace with your actual BigBlueButton API credentials
        $bbbServerURL = 'https://stream.fixityedx.com/playback/presentation/2.3/';
        $bbbSecret = 'Tnh4IuNOnU2KnvezECaBpVKC7dNfJgSvDpNZzSWVLKk';
    
        if ($courseId > 1) {
            $where = "bbb.course = :courseid AND bbb.closingtime >= :currenttime";
            $params = ['courseid' => $courseId, 'currenttime' => time()];
        } else {
            $enrolledCourseIds = [];
            $courses = enrol_get_users_courses($USER->id, false, 'id, shortname, fullname');
        
            foreach ($courses as $myCourse) {
                $enrolledCourseIds[] = $myCourse->id;
            }
        
            if (!empty($enrolledCourseIds)) {
                $placeholders = implode(', ', array_map(function ($id) {
                    return ":courseid_$id";
                }, $enrolledCourseIds));
        
                $params = ['currenttime' => time()];
                foreach ($enrolledCourseIds as $id) {
                    $params["courseid_$id"] = $id;
                }
        
                $where = "bbb.course IN ($placeholders) AND bbb.closingtime >= :currenttime";
            } else {
                // Handle the case where no courses are enrolled
                echo "No courses enrolled.";
                // Add any necessary further handling or return from the function, depending on your needs.
                return;
            }
        }
        
        $sql = "SELECT *
                FROM {bigbluebuttonbn} bbb
                WHERE $where
                ORDER BY bbb.openingtime";
        
        // Execute the query
        $recordings = $DB->get_records_sql($sql, $params);
        
        // Process the $recordings as needed
        
        
        
        // Process the $recordings as needed
        
        
        // Process the $recordings as needed
        
        
       
        return $recordings;
    }
    
    
    public function getJoinMeetingLink($activityId, $courseId) {
        global $DB;
        // Replace with your actual BigBlueButton API credentials
        $bbbServerURL = 'https://stream.fixityedx.com/bigbluebutton/';
        $bbbSecret = 'Tnh4IuNOnU2KnvezECaBpVKC7dNfJgSvDpNZzSWVLKk';
        // Get meeting details from the Moodle database
        $sql = "SELECT br.recordingid
            FROM {bigbluebuttonbn_recordings} br
            JOIN {bigbluebuttonbn} bbb ON br.bigbluebuttonbnid = bbb.id
            WHERE bbb.id = :activityid";
    
        $params = ['activityid' => $activityId];
    
        // Execute the query
        $meeting = $DB->get_records_sql($sql, $params);
        if ($meeting) {
            // Construct the join meeting link
            $meetingID = $meeting->bigbluebutton_id;
            $moderatorPassword = $meeting->moderator_pw;
            $attendeePassword = $meeting->viewer_pw;
    
            $joinMeetingLink = $bbbServerURL . 'join?meetingID=' . $meetingID . '&password=' . $attendeePassword;
    
            return $joinMeetingLink;
        }
    
        return null;
    }

    /**
     * Returns the role that best describes the course list block.
     *
     * @return string
     */
    public function get_aria_role() {
        return 'navigation';
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        global $CFG;

        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = (object) [
            'adminview' => $CFG->block_course_list_adminview,
            'hideallcourseslink' => $CFG->block_course_list_hideallcourseslink
        ];

        return (object) [
            'instance' => new stdClass(),
            'plugin' => $configs,
        ];
    }
}


