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
 * btlms_progress block rendrer
 *
 * @package    block_btlms_progress
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @copyright  2017 Mathew May <mathewm@hotmail.co.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

/**
 * btlms_progress block rendrer
 *
 * @copyright  2012 Adam Olley <adam.olley@netspot.com.au>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_btlms_progress_renderer extends plugin_renderer_base {

    /**
     * Show user profile block details.
     *
     * @param array $user_data details of a user
     * @return string html of user profile block
     */
    public function get_user_profiledata($user_data) {
        global $CFG,$OUTPUT;
        return $OUTPUT->render_from_template('block_btlms_progress/user-profile-view',$user_data);

    }
}
