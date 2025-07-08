<?php 

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
global $CFG,$DB;  

$task = optional_param('task', null, PARAM_ALPHA);
$data = new report_overviewstats(); 

switch($task){
	case 'delete':
		$id = optional_param('id', null, PARAM_INT);
		$idcr = optional_param('idcr', null, PARAM_INT);
		
		$return=$data->delete_report($id); 
		if($return){
			echo json_encode( array('success'=>true, 'id' => $id ));
		}
	break;
	case 'dest':
		$id = optional_param('id', null, PARAM_INT);
		$fav = optional_param('fav', null, PARAM_INT);
		$mfd = optional_param('mfd', null, PARAM_INT);
		
		$return = $data->update_fav((empty($fav)?1:0) ,$id);
		$menuhtml = '';
		if( $mfd === 0 ){
			$menu = $data->get_menu_reports();
			$menuhtml .= $data->get_accordion_html($menu);
		}
		
		if($return){
			echo json_encode( array('success'=>true, 'id' => $id, 'fav' => (empty($fav)?1:0), 'mfd' => $menuhtml,  ));
		}
	break;
	case 'searchreport':
		//$DB->set_debug(true);
		
                $txt = optional_param('txt', null, PARAM_TEXT);
                $txt = strtolower($txt);
                $txt = str_replace(' ', '_', $txt);
		$menu=$data->get_menu_reports($txt);
                
		if(!empty($menu)){
			$html=$data->get_accordion_html($menu,$txt);  
		}
		echo json_encode( array('success'=>(empty($html)?false:true), 'menu' => $html ));
	break;
        case 'checkreportname':
            $text = optional_param('txt', null, PARAM_TEXT);
            if ($DB->record_exists_sql("SELECT * FROM {block_configurable_reports} where name = '$text'  AND visible = 1")) {
                $status = array("success"=>true,"message"=>get_string('reportexists', 'block_configurable_reports'));
            } else {
                $status = array("success" => false);
            }
            echo json_encode($status);
        break;
          case 'schedulereport':
            $reportid = optional_param('reportid', null, PARAM_INT);
            
            $table = new html_table();
            $name = get_string('label_repo_scd','local_lms_reports');
            $desc = get_string('description','local_lms_reports');
            $start = get_string('table_next_schedule_table_task','local_lms_reports');
            $runable = get_string('runable_schedule_table_task','local_lms_reports');
            $options = get_string('options_repo_schedule','local_lms_reports');
            
            $context = context_system::instance();
                    
            $table->head = array($name , $desc, $runable, $start,  $options);
            $sql="SELECT ps.id as scheduleid,ps.*, cq.* FROM {local_reports_schedule} ps  LEFT JOIN {report_customsqlp_queries} cq ON ps.customsqlid = cq.id WHERE ps.reportid= ? AND ps.isdeleted = ? ";
            
            $params= array($reportid, 0);
            $records = $DB->get_records_sql($sql, $params );
            if(!empty($records)){
                foreach($records as $record ){
                    if($record->runable == "daily"){
                        $today = date("Y-m-d");
                        $nextDate = date('l,M-d-Y',strtotime($date1 . "+1 days"))." ".$record->at.":00";
                    }

                    if($record->runable == "weekly"){
                        $today = date("Y-m-d");
                        $nextDate = date('l , M-d-Y', strtotime("next week"))." ".$record->at.":00"; 

                    }

                    if($record->runable == "monthly"){
                        $today = date("Y-m-d");
                        $nextDate = date("l , M-d-Y", strtotime(date('m', strtotime('+1 month')).'/01/'.date('Y')))." ".$record->at.":00";

                    }
                    $url = new moodle_url("/local/lms_reports/actions.php?id=".$record->scheduleid."&reportid=$reportid&task=scheduledel");
                    if (has_capability('block/configurable_reports:deleteschedule', $context) ) {
                            $option ="<a href='javascript:void(0)' class='schedule_del' data-sch-id='$record->scheduleid'><i class='fa fa-trash'></i></a>";
                    }

                    $table->data[] = array($record->displayname, $record->description, $record->runable ,$nextDate  ,$option);
                    
                    
                }
                 echo html_writer::table($table);
            } else {
                 $nodata = html_writer::start_tag("div");
                    $nodata .= get_string('no_record', 'local_lms_reports');
                 $nodata .= html_writer::end_tag('div');
                 echo $nodata;
            }
           
        break;
        
        case 'scheduledel':
            $schid = optional_param('schid', null, PARAM_INT);
            $sql = "UPDATE {local_paradireports_schedule} SET isdeleted = 1  WHERE id = ? ";
			$params = array($schid);
			$DB->execute($sql, $params);
            echo 1;
        break;
        
        default:
        break;
}


?>