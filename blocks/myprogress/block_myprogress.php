<?php
defined('MOODLE_INTERNAL') || die();

class block_myprogress extends block_base {

	function init() {
		$this -> title = get_string('pluginname', 'block_myprogress');
	}

	function instance_allow_multiple() {
		return false;
	}

	function has_config() {
		return true;
	}

	function applicable_formats() {
		return array('all' => true);
	}

	function instance_allow_config() {
		return true;
	}

	public function specialization() {
		if (empty( $this->config->title )) {
			$this->title = get_string('pluginname', 'block_myprogress');
		} else {
			$this->title = $this -> config -> title;
		}
	}

	function get_content() {
		global $COURSE, $USER, $CFG, $DB, $OUTPUT, $PAGE,$SESSION;
		
		$this->content = new stdClass();

		$PAGE->requires->css(new moodle_url('/blocks/myprogress/css/my_progress.css'));

		//get user
		$uid = $USER->id;
		$user = $DB->get_record('user', array('id' => $USER -> id));

		if ( isset($this->config) && ! isset($this->config->numberofrecords) ) {
			$this->config->numberofrecords = 2;
		}

		// Get courses percent : not started, in progress and completed
		//get courses enrolled for this user
		$courses = enrol_get_users_courses($USER -> id, false);

		/**
		* Validating if user has progress
		* @author Deyby G.
		* @since April 08 of 2017
		* @ticket 916
		* @paradiso
		*/
		if (count($courses) > 0){

			$ccompleted = 0;
			$cnoyetstarted = 0;
			$cinprogress = 0;
            require_once($CFG->dirroot . '/lib/completionlib.php');
			foreach ($courses as $course) {
				// Load course.
				$course = $DB->get_record('course', array('id' => $course -> id), '*', MUST_EXIST);

				// Load completion data.
				$info = new completion_info($course);
				
				/**
				* Jump course from iteration if course dont have 
				* course completion tracking
				* @author Esteban E. 
				* @since June 28 2017
				* @paradiso
				*/

				//if(!$info->is_enabled())
					//continue ;

				// Is course complete?
				$coursecomplete = $info->is_course_complete($uid);

				// Has this user completed any criteria?
				$criteriacomplete = $info->count_course_user_data($uid);

				// Load course completion.
				$params = array('userid' => $uid, 'course' => $course -> id);

				$ccompletion = new completion_completion($params);

				if ($coursecomplete) {
					$ccompleted++;
				} else if (!$criteriacomplete && !$ccompletion->timestarted) {
					$cnoyetstarted++;
				} else {
					$cinprogress++;
				}
			}
			//===================================================

			//get courses enrolled for this user
			$courses = count(enrol_get_users_courses($uid, true));

			if ($courses > 0) {
				$progress = ($ccompleted / $courses) * 100;
			} else {
				$progress = 0;
			}

			//user data array
			$userdata = array();

			$userdata[] = $OUTPUT->user_picture($user, array('size' => 50));

			//user info
			$userdata[] = '<a target="_blank" href="' . new moodle_url('/user/profile.php', array('id' => $user -> id)) . '">' . $user -> firstname . ' ' . $user -> lastname . '</a>';

			//data
			$per1 = number_format(($ccompleted / $courses) * 100, 2);
			$per2 = number_format(($cnoyetstarted / $courses) * 100, 2);
			$per3 = number_format(($cinprogress / $courses) * 100, 2);
			
			//color
			$color0 = get_config('block_myprogress', 'backgroundcolor');
			$color1 = get_config('block_myprogress', 'completedcolor');
			$color2 = get_config('block_myprogress', 'notyetstartedcolor');
			$color3 = get_config('block_myprogress', 'inprogresscolor');

			/**
			* replace by default my progrress legends bar color
			* @author: Miguel @paradiso
			* @since: Dec 2016
			*/
			if($color1 == '#04EB62' && $color2 == '#C60300' && $color3 == '#FF950A'){
				$sql = "UPDATE {config_plugins} SET value = '#8fb644' WHERE plugin = 'block_myprogress' AND name = 'completedcolor' ";
	            $DB->execute($sql);
				$color1 = '#8fb644';	
				$sql = "UPDATE {config_plugins} SET value = '#cf6284' WHERE plugin = 'block_myprogress' AND name = 'notyetstartedcolor' ";
	            $DB->execute($sql);
				$color2 = '#cf6284';		
				$sql = "UPDATE {config_plugins} SET value = '#3f9ddb' WHERE plugin = 'block_myprogress' AND name = 'inprogresscolor' ";
	            $DB->execute($sql);
	            $color3 = '#3f9ddb';
			}

			$user_id = $USER->id;
			$PAGE->requires->jquery();

			/**
			* Get Brand Color
			* @author Abhishek Vaidya
			* @since 12 Jan 2021 
			* @ticket 413 My Progress Block New Design
			* @paradiso
			*/
			
			$brandarr = $DB->get_record('config_plugins', array('name' => 'theme_remui', 'name' => 'brandprimary') );
			
			$brandcolor = "#7EB198";
			$second_color = "#FFD166";
			$third_color = "#F28482";
			// END
			$this -> content -> text = '';

			$data['userid'] = $USER->id;
			$data['brandcolor'] = $brandcolor;
			$data['ccompleted'] = $ccompleted;
			$data['cnoyetstarted'] = $cnoyetstarted;
			$data['cinprogress'] = $cinprogress;
			$data['per1'] = $per1;
			$data['per2'] = $per2;
			$data['per3'] = $per3;
			$data['second_color'] = $second_color;
			$data['third_color'] = $third_color;

			$data['comletedtxt'] = $comletedtxt;
			$data['inprogresstxt'] = $inprogresstxt;
			$data['notyetstartedtxt'] = $notyetstartedtxt;

			$list_completed_url = new moodle_url('/blocks/myprogress/list_completed.php',array('uid'=>$USER->id));
			$data['list_completed_url'] = $list_completed_url;
				
			$list_notstarted_url = new moodle_url('/blocks/myprogress/list_notstarted.php',array('uid'=>$USER->id));
			$data['list_notstarted_url'] = $list_notstarted_url;

			$list_inprogress = new moodle_url('/blocks/myprogress/list_inprogress.php',array('uid'=>$USER->id));
			$data['list_inprogress'] = $list_inprogress;

			$highchart_url = new moodle_url('/blocks/myprogress/js/highcharts.js');
			$data['highchart_url'] = $highchart_url;

			$this->content->text = $OUTPUT->render_from_template('block_myprogress/myprogress', $data);
			// end code
       	}
	}

	/**
    * Convert brand color to adjustbrightness color
    * @author Abhishek Vaidya
    * @since 12 Jan 2021 
    * @ticket 413 My Progress Block New Design
    * @paradiso
    */
	function adjustBrightness_second($hexCode, $adjustPercent) {
		$hexCode = ltrim($hexCode, '#');
		if (strlen($hexCode) == 3) {
			$hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
		}
		$hexCode = array_map('hexdec', str_split($hexCode, 2));
		foreach ($hexCode as & $color) {
			$adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
			$adjustAmount = ceil($adjustableLimit * $adjustPercent);
	
			$color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
		}
		return '#' . implode($hexCode);
	}

	function adjustBrightness_third($hexCode, $adjustPercent) {
		$hexCode = ltrim($hexCode, '#');
		if (strlen($hexCode) == 3) {
			$hexCode = $hexCode[0] . $hexCode[0] . $hexCode[1] . $hexCode[1] . $hexCode[2] . $hexCode[2];
		}
		$hexCode = array_map('hexdec', str_split($hexCode, 2));
		foreach ($hexCode as & $color) {
			$adjustableLimit = $adjustPercent < 0 ? $color : 255 - $color;
			$adjustAmount = ceil($adjustableLimit * $adjustPercent);
	
			$color = str_pad(dechex($color + $adjustAmount), 2, '0', STR_PAD_LEFT);
		}
		return '#' . implode($hexCode);
	}
	// END
    /**
    * Add new function to save config for each blocks
    * @author Jonatan Uribe
    * @since Jul 24 of 2017 
    * @ticket 958 Home settings On MT is not working
    * @paradiso
    */
    public function instance_create() {
        global $SESSION;
        $company = isset($SESSION->currenteditingcompany) ? $SESSION->currenteditingcompany : 0;
        $config = (object)array(
            'tenant' => $company,
        );
        $this->instance_config_save($config);
		return true;
    }
}
