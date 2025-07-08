<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/phpspreadsheet/vendor/autoload.php'); // Adjust the path as needed

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$courseId = required_param('courseid', PARAM_INT);
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

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers

$sheet->setCellValue('A1', 'Course Name');
$sheet->setCellValue('B1', 'Group Name');
$sheet->setCellValue('C1', 'First Name');
$sheet->setCellValue('D1', 'Last Name');
$sheet->setCellValue('E1', 'Question Name');
$sheet->setCellValue('F1', 'Response');
$sheet->setCellValue('G1', 'Submitted Date');

// Populate data
$row = 2;
foreach ($feedbackResponses as $response) {
    $courseName = $response->course_name;
    $sheet->setCellValue('A' . $row, $response->course_name);
    $sheet->setCellValue('B' . $row, $response->groupname);
    $sheet->setCellValue('C' . $row, $response->firstname);
    $sheet->setCellValue('D' . $row, $response->lastname);
    $sheet->setCellValue('E' . $row, $response->question_name);
    $sheet->setCellValue('F' . $row, $response->value);
    $sheet->setCellValue('G' . $row, date('Y-m-d H:i:s', $response->timemodified));
    $row++;
}

// Set appropriate headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename='.$courseName.'_feedback_report.xlsx');
header('Cache-Control: max-age=0');

// Save the Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

?>
