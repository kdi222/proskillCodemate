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


require_once('../../config.php');
require_once('viewresult_form.php');
require_once($CFG->libdir . '/formslib.php');
require_once('lib.php'); 

global $OUTPUT, $CFG;
require_login();
$context = context_system::instance();
$PAGE->set_context($context);

	$id = optional_param('cid','',PARAM_INT);
	$PAGE->set_pagelayout('customreport');
	$PAGE->set_url($CFG->wwwroot . '/local/lms_reports/view.php');
	$title = get_string('managetest', 'local_lms_reports');
	$PAGE->set_heading(get_string('student_timespent', 'local_lms_reports'));
	$PAGE->requires->jquery();
	include_once('jslink.php');
	if($id){
		$mform = new local_lms_reports_form($CFG->wwwroot . '/local/lms_reports/view.php',array('cid'=>$id));
	}else{
		$mform = new local_lms_reports_form($CFG->wwwroot . '/local/lms_reports/view.php',array('cid'=>0));
	}
	$data = $mform->get_data();
	if(!empty($data)){
		$course_id = $data->courseid;
		$start_date = $data->reportstart;
		$end_date = $data->reportend;
	
		$arraydata = (array)$data;
		if (array_key_exists('downloadexcel', $arraydata)) {
			$redirecturl = $CFG->wwwroot.'/local/lms_reports/hpcl_excel.php?courses='.'all'.'&dataformat=csv&courseid='.$course_id.'&reportstart='.$start_date.'&reportend='.$end_date.'&city='.$city.'&institution='.$institution.'&department='.$department.'';
			redirect($redirecturl);
		}
	}

	echo $OUTPUT->header();   
	//here we are creating the page heading and other page link button.
	echo'<br>';
	$headingtext = get_string('managetest','local_lms_reports');
	$url = $CFG->wwwroot.'/local/hpanalytics/hpcl_excel.php?courses='.'all'.'&dataformat=csv';
	$heading = get_heading($headingtext,'','','',$url);
	//echo $heading;
	$mform->display();
	if ($mform->is_cancelled()) {
		redirect(moodle_url($CFG->wwwroot.'/my'));
	} else if($data){
		$course_id = $data->courseid;
		$start_date = $data->reportstart;
		$end_date = $data->reportend;
	
		//Sangita : Jan 16 2020 : check courseid.....
		$cid = $data->courseid;
		$completioncount_gsb='';
		//Manju: fo all courses.[30/01/2020].
		if($cid == 0){
			$allcourseavgtimespent_total = 0;
			$videocat = $DB->get_field('course_categories','id',array('idnumber'=>'Video'));
			$sql = 'SELECT id  FROM  {course} 
			WHERE visible = 1 AND id ="'.$course_id.'"';
			$allcourses = $DB->get_records_sql($sql);
			foreach ($allcourses as $key => $allcourse) {
				$allcourseids[] = $allcourse->id;
			}
			//find all enrollment
			$allenrollmentcount = get_enrollment_count($allcourseids,$data);
			$htmldisplay = create_html_for_display_enrollment_records($allenrollmentcount);
			echo $htmldisplay; 
			$allcoursecompletion = get_course_completion_count($allcourseids,$data);
			$htmlcoursecompletion = create_html_for_display_completion_records($allcoursecompletion);
			echo $htmlcoursecompletion;

		

			$sqlall = 'SELECT id  FROM  {course} 
			WHERE visible = 1';
			$alcourseids=[];
			$alcourses = $DB->get_records_sql($sqlall);
			foreach ($alcourses as $key => $alcourse) {
				$alcourseids[] = $alcourse->id;
			}

			//donut chart
			$allusercoursecompletionstatus = all_course_completion_statss($alcourseids,$data);
			$totalcount = $allusercoursecompletionstatus['allcoursecompletion'];
			$completedcount = $allusercoursecompletionstatus['allcoursecpmleted'];
			$inprogresscount = $allusercoursecompletionstatus['allinprogresscourse'];
			$notstartedcount = $allusercoursecompletionstatus['allcoursenotstarted'];
			// $realvalue = $totalcount;
			//all enrolmentcount
			$allcourseenrollmentcount = get_enrollment_count($alcourseids,$data);
			//Manju:Average time spent on course by all users.[31/01/2020]
			$allcoursemean=[];
			foreach ($alcourses as $key => $crs) {
				$coursemean = get_mean_dedication_time($crs->id,$start_date, $end_date);
				$allcoursemean[]=$coursemean;
			}
			if(!empty($allcoursemean)){
				$allcourseavgtime = round((array_sum($allcoursemean))/count($allcoursemean),4);
			}else{
				$allcourseavgtime=0;
			}
			
		}
		//Sangita: Jan 16 2020
		//Manju: for individual courses.[30/01/2020].
		if($cid != 0){
		//manjunath: getting  course completion,inprogress and not started counts
			$allenrollmentcount = get_enrollment_count($cid,$data);
			$course_stats = course_completion_stats($course_id, $start_date, $end_date );
			$indtotalcount = $allenrollmentcount['totalenrolluser'];
			$completedcount = $course_stats['1'];
			$inprogresscount = $course_stats['2'];
			$notstartedcount = $course_stats['3'];
		//Manju: mean time spent by an user in course.[04/02/2020]
			$meandedication = get_mean_dedication_time($cid,$start_date, $end_date);
		

		
			
		}		


		$data = html_writer::start_div('container');
		$data .= html_writer::start_div('row');
	//Manju:For course completion, not-started and in-progress donut chart.
		$data .= html_writer::start_div('col-md-6 col-sm-6 col-xs-12');
		$data .= html_writer::start_div('card enrollments');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('coursecompletion', 'local_lms_reports');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body first-graph');
		$data .= html_writer::start_div('firstchart',array('id' => 'donutchart'));
		$data .= html_writer::end_div();
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//card ends
		$data .= html_writer::end_div();//end column
	//Manju:For total enrollment section.
		$data .= html_writer::start_div('col-md-6 text-center');//enrollments block
		$data .= html_writer::start_div('card enrollments ');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('coursedetails', 'local_lms_reports');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body text-center text-white1 enroll');
		if($cid !=0){
			if(!empty($indtotalcount)){
				$data .= '<h1>'.$indtotalcount.'</h1>'.get_string('enrollments','local_lms_reports');
				
			}else if(empty($indtotalcount) && !empty($course_id)){
				$data .= '<h1>0</h1>'.get_string('enrollments','local_lms_reports');
			
			}
		} else {
			$data .= '<h1>'.$totalcount.'</h1>'.get_string('enrollments','local_lms_reports');
		}
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//end card
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end row
		$data .= html_writer::start_div('row pb-4');
		
	//for all courses no need to show this  chart
		if($cid == 0){
			$meandedication = $allcourseavgtime.get_string('seconds','local_lms_reports');
		}
	//Manju:Average time spent on course by all users.[31/01/2020]
		$data .= html_writer::start_div('col-md-6');
		$data .= html_writer::start_div('card text-white1 bg-success enrollments');
		$data .= html_writer::start_div('card-header text-center');
		$data .= get_string('averagetimespent', 'local_lms_reports');
		$data .= html_writer::end_div();//end header
		$data .= html_writer::start_div('card-body text-center');
		if($meandedication >= 1){
			$data .= '<i class="fa fa-clock-o" style="font-size:50px"></i> &nbsp;<h1>'.$meandedication.'</h1></br>'.get_string('timespentinmin','local_lms_reports');
		}else{
			$data .= '<i class="fa fa-clock-o" style="font-size:50px"></i> &nbsp;<h1>'.$meandedication.'</h1></br>'.get_string('timespentinsec','local_lms_reports');
		}
		$data .= html_writer::end_div();
		$data .= html_writer::end_div();//end card-body
		$data .= html_writer::end_div();//end column
		$data .= html_writer::end_div();//end row
	
	$userDedication = get_user_dedication_time($course_id, $start_date, $end_date);
	// Output table header
	$data .= "<table class='table'>
		<tr>
			<th>User Name</th>
			<th>Last Access</th>
			<th>Dedication Time (hr : min)</th>
		</tr>";

		// Output table rows
		foreach ($userDedication as $dedData) {
			$user = $dedData->user;
			$firstname = $user->firstname;
			$lastname = $user->lastname;
			$lastaccess = date("Y-m-d H:i:s", $user->lastaccess); // Convert timestamp to readable format
			$dedicationtime = $dedData->dedicationtime;
			$dedication_hours = sprintf("%02d",floor($dedicationtime / 60));
    		$dedication_minutes_remaining = sprintf("%02d",$dedicationtime % 60);
			$data .= "<tr>
			<td>{$firstname} {$lastname}</td>
			<td>{$firstname} {$lastname}</td>
			<td>{$dedication_hours} : {$dedication_minutes_remaining}</td>
		</tr>";
		}

		// Close the table
		$data .= "</table>";

	echo $data;

		
	
}
echo $OUTPUT->footer();


?>
<script type="text/javascript">
	google.charts.load("current", {packages:["corechart"]});
	google.charts.setOnLoadCallback(drawChart);
	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Task', 'Hours per Day'],
			['Completed',     <?php echo json_encode($completedcount, JSON_NUMERIC_CHECK); ?>],
			['Inprogress',      <?php echo json_encode($inprogresscount, JSON_NUMERIC_CHECK); ?>],
			['Not Started',  <?php echo json_encode($notstartedcount, JSON_NUMERIC_CHECK); ?>]
			]);

		var options = {
			pieHole: 0.4,
			backgroundColor: { fill:'transparent' },
			legend: 'none',
			'height':300

		};

		var chart = new google.visualization.PieChart(document.getElementById('donutchart'));
		chart.draw(data, options);
	}
</script>
<!--manjunath: badges display js code -->
<!-- 1st badge -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
			['Gold Badges',   <?php echo json_encode($goldbadgecount, JSON_NUMERIC_CHECK); ?>],
			]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'font-size':16,
			'height':150,
			colors: ['#D4AF37'],
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single1'));
		chart.draw(data, options);
	}
</script>
<!-- 2th -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
			['Silver Badges',     <?php echo json_encode($silverbadgecount, JSON_NUMERIC_CHECK); ?>],
			]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'height':150,
			colors: ['#C0C0C0'],
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single2'));
		chart.draw(data, options);
	}
</script>
<!-- 3th -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
			['Bronze Badges',     <?php echo json_encode($bronzebadgecount, JSON_NUMERIC_CHECK); ?>],
			]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'height':150,
			colors: ['#cd7f32'],
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single3'));
		chart.draw(data, options);
	}
</script>
<!-- 4th -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {
		
		var data = google.visualization.arrayToDataTable([
			['Effort', 'Amount given'],
          ['Completion Badges',     <?php echo json_encode($completionbadgecount, JSON_NUMERIC_CHECK); ?>],
          ]);

		var options = {
			pieHole: 0.5,
			pieSliceTextStyle: {
				color: 'black',
			},
			legend: 'none',
			'pieSliceText': 'value',
			pieSliceTextStyle: { color: 'black', fontName: 'Arial', fontSize: 25 } ,
			'width':150,
			'height':150,
			backgroundColor: { fill:'transparent' },
			sliceVisibilityThreshold: 0,
			tooltip: {
				trigger: "none"
			}
		};

		var chart = new google.visualization.PieChart(document.getElementById('donut_single4'));
		chart.draw(data, options);
	}
</script>

<!--manjunath: course completion js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawVisualization);

	function drawVisualization() {
		var data = google.visualization.arrayToDataTable([
			['Course', 'Course Completion'],
			<?php 
			$i =0;
			foreach ($completiongraph as $key => $value) {
				if($i!=0){
					echo ',';
				}
				echo "['".$key."',  ".$value."]";
				$i++;
			}
			?>
			]);
		var options = {
			legend: 'none',
			height: 300,
			backgroundColor: { fill:'transparent' },
			vAxis: {title: 'Completion'},
			hAxis: {title: 'Months'},
			seriesType: 'bars',
			series: {1: {type: 'line'}}
		};

		var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
		chart.draw(data, options);
	}
</script>

<!--manjunath: course enrollments js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Months');
		data.addColumn('number', 'Course enrollments');
		data.addRows([
			<?php 
			$i =0;
			foreach ($graphdata as $key => $value) {
				if($i!=0){
					echo ',';
				}
				echo "['".$key."',  ".$value."]";
				$i++;
			}
			?>
			
			]);

		var options = {
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			height: 300,
			
			axes: {
				y: {
					0: {side: 'top'}
				}
			}
		};

		var chart = new google.charts.Line(document.getElementById('line_top_x'));

		chart.draw(data, google.charts.Line.convertOptions(options));
	}

</script>
<!--Manju: course enrollments year wise js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Year');
		data.addColumn('number', 'Course enrollments');
		data.addRows([
			<?php 
			$i =0;
			if(!empty($yearenroll)){
				foreach ($yearenroll as $key => $value) {
					if($i!=0){
						echo ',';
					}
					echo "['".$key."',  ".$value."]";
					$i++;
				}
			}else{
				echo "[0,  0]";
			}

			?>
			
			]);

		var options = {
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			height: 300,
			
			axes: {
				y: {
					0: {side: 'top'}
				}
			}
		};

		var chart = new google.charts.Line(document.getElementById('year_enrolment'));

		chart.draw(data, google.charts.Line.convertOptions(options));
	}

</script>
<!--Manju: course completions year wise js code -->
<script type="text/javascript">
	google.charts.load('current', {'packages':['line']});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Year');
		data.addColumn('number', 'Course Completions');
		data.addRows([
			<?php 
			$i =0;
			if(!empty($yearcompletion)){
				foreach ($yearcompletion as $gkey => $gvalue) {
					if($i!=0){
						echo ',';
					}
					echo "['".$gkey."',  ".$gvalue."]";
					$i++;
				}
			}else{
				echo "[0,  0]";

			}

			?>
			
			]);

		var options = {
			legend: {position: 'none'},
			backgroundColor: { fill:'transparent' },
			height: 300,
			
			axes: {
				y: {
					0: {side: 'top'}
				}
			}
		};

		var chart = new google.charts.Line(document.getElementById('year_completion'));

		chart.draw(data, google.charts.Line.convertOptions(options));
	}

</script>
