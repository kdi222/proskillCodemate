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
 * Handles displaying the User Info block.
 *
 * @package    block_user_profile
 * @copyright  2004 Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('DEDICATION_DEFAULT_SESSION_LIMIT', 60 * 60);
class block_user_profile extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_user_profile');
    }

    function applicable_formats() {
        return array('all' => true);
    }
    
    /**
     * Return the content of this block.
     *
     * @return stdClass the content
     */
    public function get_content() {
        global $CFG,$PAGE, $USER, $DB,$OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        // USER Details
        $userpicture = new user_picture($USER);
        $userpicture->size = 1; // Size f1.
        $profileimageurl = $userpicture->get_url($PAGE);
        $username = $USER->firstname." ".$USER->lastname;
        // END
            
        // Badges Count
        $uid = $USER->id;
       

        // Total Course Count
        $courses = enrol_get_users_courses($USER->id,true);
        $total_courses =0;
        if($courses) {
        $total_courses = count($courses);
        }
        // END

        $params = array();
        $where_con = "userid='".$uid."' AND courseid='1' ";
        $getxpdata = $DB->get_records_select('block_xp', $where_con, $params,  'xp,lvl');
        $levelnum = '';
        $xpnum = 0;
        if($getxpdata) {
        $prevlog = array_shift($getxpdata);
        //$levelnum = $prevlog->lvl;
        $xpnum =$prevlog->xp;
        }
       
    
        $profileurl = $CFG->wwwroot . '/user/profile.php?id='.$uid;
        $this->content = new stdClass;
        $this->content->text = '';
        $studentGrade = $this->get_student_grades();
      // 
        $studentAllGrade = overallReport($uid);
       //echo "<pre>";print_r($studentAllGrade);
      
       $totalMaxg = 0;
       $totalgrade = 0;
       $totalPassg = 0;
       $myRank = null;
       $myRank  = $studentAllGrade['student_rank'];
        foreach ($studentAllGrade as $gradeData){
           
          
            foreach ($gradeData['grades'] as $grade){
                $totalMaxg += $grade['grademax'];
                $totalPassg += $grade['gradepass'];
                $totalgrade += $grade['grade'];
            }
        }
      
        //creating array of a user data to pass function
        $user_data['profileimageurl'] = $profileimageurl;
        $user_data['username'] = $username;
        $user_data['mygroup'] = get_student_group($USER->id);
        $user_data['profileurl'] = $profileurl;   
        $user_data['mygrade'] = $count_certificate;  
        $user_data['total_courses'] = $total_courses; 
        $user_data['xpnum'] = $xpnum;   
        $user_data['count_post'] = $count_post;
        $user_data['totalgrade'] = $totalgrade;
        $user_data['myRank'] = $myRank;
        $user_data['totalPassg'] = $totalPassg;
        $user_data['totalMaxg'] = 800;
        $user_data['totalPersentage'] = ($totalgrade > 0) ? floor(($totalgrade / 800) * 100) : 0;
        $renderer= $this->page->get_renderer('block_user_profile');
        $this->content->text .= $renderer->get_user_profiledata($user_data);

        return $this->content;
    }

    function get_student_grades(){
        global $DB, $USER;

        // Query to sum all grades, final grades, and percentages
        $gradesSum = $DB->get_record_sql('
                SELECT
                SUM(gi.grademax),
                SUM(gg.finalgrade) AS total_grade,
                SUM(gg.finalgrade / gi.grademax) * 100 AS total_percentage,
                SUM(gg.finalgrade) / COUNT(gg.finalgrade) AS average_final_grade
            FROM
                {user_enrolments} ue
            JOIN
                {enrol} e ON e.id = ue.enrolid
            JOIN
                {course} c ON c.id = e.courseid
            JOIN
                {grade_items} gi ON gi.courseid = c.id
            JOIN
                {grade_grades} gg ON gg.itemid = gi.id
            WHERE
                ue.userid = :user_id
        ', ['user_id' => $USER->id]);
    
        return $gradesSum; 
    }

    

    function has_config() {
        return true;
    }
    
    function instance_allow_multiple() {
        return false;
    }

    function instance_allow_config() {
        return true;
    }
}