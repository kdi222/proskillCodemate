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
 * @package    block_btlms_progress
 * @copyright  2004 Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define('DEDICATION_DEFAULT_SESSION_LIMIT', 60 * 60);

require_once($CFG->libdir . "/badgeslib.php");
class block_btlms_progress extends block_base {

    /**
     * Initialise the block.
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_btlms_progress');
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
        global $USER, $CFG,$DB,$PAGE;
        $this->title = '';
        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->config)) {
            $this->config = new stdClass();
        }

        // Number of badges to display.
        if (!isset($this->config->numberofbadges)) {
            $this->config->numberofbadges = 3;
        }

        // Create empty content.
        $this->content = new stdClass();
        $this->content->text = '';

        if (empty($CFG->enablebadges)) {
            $this->content->text .= get_string('badgesdisabled', 'badges');
            return $this->content;
        }

        $courseid = $this->page->course->id;
        if ($courseid == SITEID) {
            $courseid = null;
        }
        $badges = count(badges_get_user_badges($USER->id, $courseid, 0, $this->config->numberofbadges));
        if ($badges = badges_get_user_badges($USER->id, $courseid, 0, $this->config->numberofbadges)) {
            $output = $this->page->get_renderer('core', 'badges');
            $badges_html = $output->print_badges_list($badges, $USER->id, true);
        } else {
            $badges_html = get_string('nothingtodisplay', 'block_badges');
        }
        // Leaderboard

        $leaderboard  = $DB->get_records_sql('SELECT x.*, u.id AS userid,u.picture,u.firstname,u.lastname,
        u.firstnamephonetic,u.lastnamephonetic,u.middlename,u.alternatename,u.imagealt,u.email, ctx.id AS ctxid, 
        ctx.path AS ctxpath, ctx.depth AS ctxdepth, ctx.contextlevel AS ctxlevel, ctx.instanceid AS ctxinstance, ctx.locked AS ctxlocked 
        from {block_xp} x LEFT JOIN {user} u ON x.userid = u.id  JOIN {context} ctx
        ON ctx.instanceid = x.userid
        AND ctx.contextlevel = '.CONTEXT_USER.' where courseid = 1 order by xp DESC LIMIT 3');

        $userhtml = '';
        $userhtml .='<div class="leaderboard_div">';
        $i=1;
        foreach($leaderboard as $row) {
            $user = $DB->get_record('user', array('id' => $row->userid));
            $userpicture = new user_picture($user);
            $userpicture->size = 1; // Size f1.
            $profileimageurl = $userpicture->get_url($PAGE);
            if($i == 1) {
            $no_nm = "st";
            } else if($i == 2) {
                $no_nm = "nd";
            } else {
                $no_nm = "rd";
            }
            $userhtml .='<label class="label_rank">'.$i.$no_nm.'</label><img src="'.$profileimageurl.'" class="" width="35" height="35" style="border-radius: 50%;">';
        $i++;
        } 
        $userhtml .='</div>';

        // Certificates
        $blockinstance = block_instance('mycertificates');
        $blockinstance->config = new stdClass();
        $certificates_html = $blockinstance->get_content()->text;

        $html = '';
        $html .='
        <div class="row ">
          <div class="col btlms_block shadow badges">
            <h3 class="card-title pt-3">BADGES</h3>
            <div class="d-flex">
            <div class="col-4"><p class="fill-text">Your latest achievements</p>
            <button type="button" class="btn btn-primary rounded-pill text-light">View All</button>
            </div>
            <div class="col-8">
            '.$badges_html.'
          </div>
          </div></div>
          <div class="vl"></div>
          
          <div class="col btlms_block shadow">
          <h3 class="card-title pt-3">CERTIFICATES</h3>
          <div class="d-flex">
          <div class="col-4"><p class="fill-text">Your latest certificates</p>
           <button type="button" class="btn btn-primary rounded-pill text-light">View All</button>
           </div>
           <div class="col-8">
          '.$certificates_html.'
          </div>
          </div>
        <!-- <div class="vl"></div>
          <div class="col btlms_block">
          <h3 class="card-title pt-3">LEADERBOARD</h3>
          <p>Top 5 learners</p>
            $userhtml
          </div>-->
        </div>
      ';
      $this->content->text = $html;
        return $this->content;
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