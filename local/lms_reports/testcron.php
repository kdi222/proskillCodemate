<?php 
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;




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

// Insert JSON data into the database
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
?>
