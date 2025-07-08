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


namespace local_lms_reports\task;

use stdClass;

defined('MOODLE_INTERNAL') || die();



/**
 * The main scheduled task for the forum.
 *
 * @package    local_lms_reports

 */
class cron_task extends \core\task\scheduled_task {

    // Use the logging trait to get some nice, juicy, logging.
    use \core\task\logging_trait;

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('crontask', 'local_lms_reports');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        global $CFG, $DB;
        // Fetch data from the database view, limiting to top 5 records
        mtrace("Executing course_viewed report...");

        // Define the SQL query with a smaller date range for testing
        $sql = "SELECT 
                    COUNT(l.id) AS `Views`,
                    c.shortname 'Course', 
                    c.id AS 'Courseid'
                    FROM {logstore_standard_log} l,
                    {course} c,
                    {role_assignments} ra 
                WHERE
                    c.visible = 1 AND c.id<>1 
                    AND l.eventname = '\\\core\\\\event\\\course_viewed' 
                    AND l.action = 'viewed' 
                    AND l.target ='course'
                    AND l.courseid = c.id 
                    AND l.userid = ra.userid 
                    AND l.contextid = ra.contextid 
                    AND ra.roleid = 5 
                GROUP BY l.`courseid`
                ORDER BY Views DESC LIMIT 5";
        
        
        
        // Execute the SQL query
        try {
            $top_viewed = $DB->get_records_sql($sql);
            mtrace("SQL Executed. Number of records: " . count($top_viewed));
        } catch (Exception $e) {
            mtrace("SQL Execution Error: " . $e->getMessage());
            return;
        }
        
        // Check if data is empty
        if (empty($top_viewed)) {
            mtrace("Course view not found");
            return; // No data to store
        }
        
        // Convert to JSON format
        $json_data = array();
        foreach ($top_viewed as $course) {
          //  print_r($course);
            // Check if properties exist
            if (!isset($course->course) || !isset($course->views)) {
                mtrace("Error: Missing expected properties 'Course' or 'Views' in fetched data.");
                continue;
            }
        
            $json_data[] = array(
                'course' => $course->course,
                'view' => $course->views
            );
            mtrace("In Loop.. " . json_encode($json_data));
        }
              
        $json_encoded = json_encode($json_data);
        
        $existing_record = $DB->get_record('lms_reports_mdata', ['title' => 'top_view_course']);

        if ($existing_record) {
            // Update existing record
            $existing_record->value = $json_encoded;
            
            mtrace("Preparing to update record in lms_reports_mdata");
            try {
                $DB->update_record('lms_reports_mdata', $existing_record);
                mtrace("Record updated in lms_reports_mdata");
            } catch (Exception $e) {
                mtrace("Database Update Error: " . $e->getMessage());
            }
        } else {
            // Insert new record
            $record = new stdClass();
            $record->title = 'top_view_course';
            $record->value = $json_encoded;
            
            mtrace("Preparing to insert record into lms_reports_mdata");
            try {
                $DB->insert_record('lms_reports_mdata', $record);
                mtrace("Record inserted into lms_reports_mdata");
            } catch (Exception $e) {
                mtrace("Database Insertion Error: " . $e->getMessage());
            }
        }
    }

  
}
