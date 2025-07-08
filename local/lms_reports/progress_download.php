<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/phpspreadsheet/vendor/autoload.php'); // Adjust the path as needed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$courseId = required_param('courseid', PARAM_INT);
$courseProgress = $DB->get_records_sql(
    "SELECT
    u.firstname AS firstname,
    u.lastname AS lastname,
    c.fullname AS coursename,
    cc.timeenrolled AS timeenrolled,
    c.id AS courseid,
    u.id AS userid,
    CASE WHEN cc.timecompleted IS NOT NULL THEN 'Completed' ELSE 'Not Completed' END AS coursecompletionstatus,
    COUNT(DISTINCT CASE WHEN cmc.completionstate = 1 THEN cmc.id END) AS activitiescompleted,
    COUNT(DISTINCT cm.id) AS totalactivities,
    ROUND(
        (COUNT(DISTINCT CASE WHEN cmc.completionstate = 1 THEN cmc.id END) / COUNT(DISTINCT cm.id)) * 100, 2
    ) AS courseprogresspercentage
FROM {user} u
JOIN {user_enrolments} ue ON u.id = ue.userid
JOIN {enrol} e ON ue.enrolid = e.id
JOIN {course} c ON e.courseid = c.id
LEFT JOIN {course_completions} cc ON ue.userid = cc.userid AND c.id = cc.course
LEFT JOIN {course_modules} cm ON c.id = cm.course
LEFT JOIN {course_modules_completion} cmc ON cm.id = cmc.coursemoduleid AND cmc.userid = u.id
WHERE ue.status = 0 AND courseid = :courseid
GROUP BY u.id, u.firstname, u.lastname, c.id, c.fullname, cc.timeenrolled, cc.timecompleted",
        ['courseid' => $courseId],0 , 0
);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers

$sheet->setCellValue('A1', 'Full Name');
$sheet->setCellValue('B1', 'Course Name');
$sheet->setCellValue('C1', 'Total Activities');
$sheet->setCellValue('D1', 'Activities Completed');
$sheet->setCellValue('E1', 'Course Progress');
$sheet->setCellValue('F1', 'Time Spent');
$sheet->setCellValue('G1', 'Course Completion Status');
//$sheet->setCellValue('G1', 'Submitted Date');

// Populate data
$row = 2;
foreach ($courseProgress as $response) {
    $courseName = $response->coursename;
    $sheet->setCellValue('A' . $row, $response->firstname." ".$row->lastname);
    $sheet->setCellValue('B' . $row, $response->coursename);
    $sheet->setCellValue('C' . $row, $response->totalactivities);
    $sheet->setCellValue('D' . $row, $response->activitiescompleted);
    $sheet->setCellValue('E' . $row, round($response->courseprogresspercentage).'%');
    $sheet->setCellValue('F' . $row, get_total_time_spent_in_course($response->userid, $response->courseid));
    $sheet->setCellValue('G' . $row, $response->coursecompletionstatus);
    //$sheet->setCellValue('G' . $row, date('Y-m-d H:i:s', $response->timemodified));
    $row++;
}

// Set appropriate headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename='.$courseName.'_progress_report.xlsx');
header('Cache-Control: max-age=0');

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

function get_total_time_spent_in_course($userid, $courseid) {
    global $DB;
    
    // Define session timeout in seconds (e.g., 30 minutes)
    $sessionTimeout = 30 * 60;
    
    // Fetch log entries for the user and course in batches
    $batchSize = 1000;
    $totalTimeSpent = 0;
    $lastTime = 0;
    $offset = 0;
    
    do {
        // Note: LIMIT and OFFSET values are inserted directly into the query string
        $sql = "SELECT timecreated
                FROM {logstore_standard_log}
                WHERE userid = :userid AND courseid = :courseid
                ORDER BY timecreated ASC
                LIMIT $batchSize OFFSET $offset";
        $params = ['userid' => $userid, 'courseid' => $courseid];
        
        $logs = $DB->get_records_sql($sql, $params);
        
        foreach ($logs as $log) {
            if ($lastTime > 0) {
                $duration = $log->timecreated - $lastTime;
                if ($duration < $sessionTimeout) {
                    $totalTimeSpent += $duration;
                }
            }
            $lastTime = $log->timecreated;
        }
        
        $offset += $batchSize;
    } while (count($logs) === $batchSize);
    
    return date("H:m",$totalTimeSpent);
}
?>
