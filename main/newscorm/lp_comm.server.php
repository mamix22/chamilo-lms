<?php //$id: $
/**
 * This script contains the server part of the xajax interaction process. The client part is located
 * in lp_api.php or other api's.
 * This is a first attempt at using xajax and AJAX in general, so the code might be a bit unsettling.
 * @package dokeos.learnpath
 * @author Yannick Warnier <ywarnier@beeznest.org>
 */
/**
 * Script
 */
$langFile[] = 'learnpath';
require_once('back_compat.inc.php');
/**
 * Backup an item's values into the javascript API as "old" values (so we still have them at hand)
 * @param	integer	Learnpath ID
 * @param	integer	User ID
 * @param	integer View ID
 * @param	integer	Item ID
 * @param	double	Current score
 * @param	double	Maximum score
 * @param	double	Minimum score
 * @param	string	Lesson status
 * @param	string	Session time
 * @param	string	Suspend data
 * @param	string	Lesson location
 */
function backup_item_details($lp_id,$user_id,$view_id,$item_id,$score=-1,$max=-1,$min=-1,$status='',$time='',$suspend='',$location='')
{
	$objResponse = new xajaxResponse();
	$objResponse->addScript(
			"old_score=".$score.";" .
			"old_max=".$max.";" .
			"old_min=".$min.";" .
			"old_lesson_status='".$status."';" .
			"old_session_time='".$time."';" .
			"lms_old_item_id='".$item_id."';" .
			"old_suspend_data='".$suspend."';" .
			"old_lesson_location='".$location."';");
	//$objResponse->addAlert('data for item '.$item_id.', user '.$user_id.' backed up');
	return $objResponse;
}
/**
 * Writes an item's new values into the database and returns the operation result
 * @param	integer	Learnpath ID
 * @param	integer	User ID
 * @param	integer View ID
 * @param	integer	Item ID
 * @param	double	Current score
 * @param	double	Maximum score
 * @param	double	Minimum score
 * @param	string	Lesson status
 * @param	string	Session time
 * @param	string	Suspend data
 * @param	string	Lesson location
 */
function save_item($lp_id,$user_id,$view_id,$item_id,$score=-1,$max=-1,$min=-1,$status='',$time=0,$suspend='',$location='',$interactions=array())
{
	$debug=0;
	if($debug>0){error_log('In xajax_save_item('.$lp_id.','.$user_id.','.$view_id.','.$item_id.','.$score.','.$max.','.$min.','.$status.','.$time.',"'.$suspend.'","'.$location.'","'.(count($interactions)>0?$interactions[0]:'').'")',0);}
	$objResponse = new xajaxResponse();
	require_once('learnpath.class.php');
	require_once('scorm.class.php');
	require_once('aicc.class.php');
	require_once('learnpathItem.class.php');
	require_once('scormItem.class.php');
	require_once('aiccItem.class.php');
	$mylp = '';
	if(isset($_SESSION['lpobject']))
	{
		if($debug>1){error_log('////$_SESSION[lpobject] is set',0);}
		$oLP =& unserialize($_SESSION['lpobject']);
		if(!is_object($oLP)){
			if($debug>2){error_log(print_r($oLP,true),0);}
			if($debug>2){error_log('////Building new lp',0);}
			unset($oLP);
			$code = api_get_course_id();
			$mylp = & new learnpath($code,$lp_id,$user_id);
		}else{
			if($debug>2){error_log('////Reusing session lp',0);}
			$mylp = & $oLP;
		}
	}
	//$objResponse->addAlert(api_get_path(REL_CLARO_PATH).'newscorm/learnpathItem.class.php');
	
	$mylpi =& $mylp->items[$item_id];
	//$mylpi->set_lp_view($view_id);
	if($score!=-1){
		$mylpi->set_score($score);
	}
	if($max!=-1){
		$mylpi->max_score=$max;
	}
	if($min!=-1){
		$mylpi->min_score=$min;
	}
	if($status!='')
	{
		if($debug>1){error_log('Calling set_status('.$status.') from xajax',0);}
		$mylpi->set_status($status);
		if($debug>1){error_log('Done calling set_status from xajax',0);}
	}
	if($time!='')
	{
		$mylpi->set_time($time);
	}
	if($suspend!='')
	{
		$mylpi->current_data = $suspend;//escapetxt($suspend);
	}
	if($location!='')
	{
		$mylpi->set_lesson_location($location);
	}
	//deal with interactions provided in arrays in the following format
	//id(0), type(1), time(2), weighting(3),correct_responses(4),student_response(5),result(6),latency(7)
	if(is_array($interactions) && count($interactions)>0){
		foreach($interactions as $index=>$interaction){
			$mylpi->add_interaction($index,$interactions[$index]);
		}
	}
	
	$mylp->save_item($item_id,false);
	
	$mytotal = $mylp->get_total_items_count();
	$mycomplete = $mylp->get_complete_items_count();
	$myprogress_mode = $mylp->get_progress_bar_mode();
	$myprogress_mode = ($myprogress_mode==''?'%':$myprogress_mode);
	//$mylpi->write_to_db();
	$_SESSION['lpobject'] = serialize($mylp);
	$objResponse->addScript("update_toc('".$status."','".$item_id."');");
	$update_list = $mylp->get_update_queue();
	foreach($update_list as $my_upd_id => $my_upd_status)
	{
		if($my_upd_id != $item_id){ //only update the status from other items (i.e. parents and brothers), do not update current as we just did it already
			$objResponse->addScript("update_toc('".$my_upd_status."','".$my_upd_id."');");
		}		
	}
	$objResponse->addScript("update_progress_bar('$mycomplete','$mytotal','$myprogress_mode');");

	if($debug>0){
		$objResponse->addScript("logit_lms('Saved data for item ".$item_id.", user ".$user_id." (status=".$status.")',2)");
		if($debug>1){error_log('End of xajax_save_item()',0);}
	}
	return $objResponse;
}
/**
 * Get one item's details
 * @param	integer	LP ID
 * @param	integer	user ID
 * @param	integer	View ID
 * @param	integer	Current item ID
 * @param	integer New item ID
 */
function switch_item_details($lp_id,$user_id,$view_id,$current_item,$next_item)
{
	$debug=0;
	if($debug>0){error_log('In xajax_switch_item_details('.$lp_id.','.$user_id.','.$view_id.','.$current_item.','.$next_item.')',0);}
	$objResponse = new xajaxResponse();
	/*$item_id may be one of:
	 * -'next'
	 * -'previous'
	 * -'first'
	 * -'last'
	 * - a real item ID
	 */
	require_once('learnpath.class.php');
	require_once('scorm.class.php');
	require_once('aicc.class.php');
	require_once('learnpathItem.class.php');
	require_once('scormItem.class.php');
	require_once('aiccItem.class.php');
	$mylp = '';
	if(isset($_SESSION['lpobject']))
	{
		if($debug>1){error_log('////$_SESSION[lpobject] is set',0);}
		$oLP =& unserialize($_SESSION['lpobject']);
		if(!is_object($oLP)){
			if($debug>1){error_log(print_r($oLP,true),0);}
			if($debug>2){error_log('////Building new lp',0);}
			unset($oLP);
			$code = api_get_course_id();
			$mylp = & new learnpath($code,$lp_id,$user_id);
		}else{
			if($debug>1){error_log('////Reusing session lp',0);}
			$mylp = & $oLP;
		}
	}
	$new_item_id = 0;
	switch($next_item){
		case 'next':
			$mylp->set_current_item($current_item);
			$mylp->next();
			$new_item_id = $mylp->get_current_item_id();
			if($debug>1){error_log('In {next} - next item is '.$new_item_id.'(current: '.$current_item.')',0);}
			break;
		case 'previous':
			$mylp->set_current_item($current_item);
			$mylp->previous();
			$new_item_id = $mylp->get_current_item_id();			
			if($debug>1){error_log('In {previous} - next item is '.$new_item_id.'(current: '.$current_item.')',0);}
			break;
		case 'first':
			$mylp->set_current_item($current_item);
			$mylp->first();
			$new_item_id = $mylp->get_current_item_id();
			if($debug>1){error_log('In {first} - next item is '.$new_item_id.'(current: '.$current_item.')',0);}
			break;
		case 'last':
			break;
		default:
			//should be filtered to check it's not hacked
			if($next_item == $current_item){
				//if we're opening the same item again
				$mylp->items[$current_item]->restart();
			}
			$new_item_id = $next_item;
			$mylp->set_current_item($new_item_id);
			if($debug>1){error_log('In {default} - next item is '.$new_item_id.'(current: '.$current_item.')',0);}
			break;
	}
	$mylp->start_current_item();
	if($mylp->force_commit){
		$mylp->save_current();
	}
	//$objResponse->addAlert(api_get_path(REL_CLARO_PATH).'newscorm/learnpathItem.class.php');
	if(is_object($mylp->items[$new_item_id])){
		$mylpi = & $mylp->items[$new_item_id];
	}else{
		if($debug>1){error_log('In switch_item_details - generating new item object',0);}
		$mylpi =& new learnpathItem($new_item_id,$user_id);
		$mylpi->set_lp_view($view_id);
	}
	/*
	 * now get what's needed by the SCORM API:
	 * -score
	 * -max
	 * -min
	 * -lesson_status
	 * -session_time
	 * -suspend_data
	 */
	$myscore = $mylpi->get_score();
	$mymax = $mylpi->get_max();
	$mymin = $mylpi->get_min();
	$mylesson_status = $mylpi->get_status();
	$mylesson_location = $mylpi->get_lesson_location();
	$mytotal_time = $mylpi->get_scorm_time('js');
	/*
	if($mylpi->get_type() == 'asset'){
		//temporary measure to save completion of an asset. Later on, Dokeos should trigger something on unload, maybe... (even though that would mean the last item cannot be completed)
		$mylesson_status = 'completed';
		$mylpi->set_status('completed');
		$mylpi->save();
	}
	*/
	$mysession_time = $mylpi->get_total_time();
	$mysuspend_data = $mylpi->get_suspend_data();
	$mylesson_location = $mylpi->get_lesson_location();
	$objResponse->addScript(
			"score=".$myscore.";" .
			"max=".$mymax.";" .
			"min=".$mymin.";" .
			"lesson_status='".$mylesson_status."';" .
			"lesson_location='".$mylesson_location."';" .
			"session_time='".$mysession_time."';" .
			"suspend_data='".$mysuspend_data."';" .
			"lesson_location='".$mylesson_location."';" .
			"total_time = '".$mytotal_time."';" .
			"interactions = new Array();");
	/*
	 * and re-initialise the rest
	 * -saved_lesson_status = 'not attempted'
	 * -lms_lp_id
	 * -lms_item_id
	 * -lms_old_item_id
	 * -lms_new_item_id
	 * -lms_been_synchronized
	 * -lms_initialized
	 * -lms_total_lessons
	 * -lms_complete_lessons
	 * -lms_progress_bar_mode
	 * -lms_view_id
	 * -lms_user_id
	 */
	$mytotal = $mylp->get_total_items_count();
	$mycomplete = $mylp->get_complete_items_count();
	$myprogress_mode = $mylp->get_progress_bar_mode();
	$myprogress_mode = ($myprogress_mode==''?'%':$myprogress_mode);
	$mynext = $mylp->get_next_item_id();
	$myprevious = $mylp->get_previous_item_id();
	$myitemtype = $mylpi->get_type();
	$mylesson_mode = $mylpi->get_lesson_mode();
	$mycredit = $mylpi->get_credit();
	$mylaunch_data = $mylpi->get_launch_data();
	$myinteractions_count = $mylpi->get_interactions_count();
	$objResponse->addScript(
			"saved_lesson_status='not attempted';" .
			"lms_lp_id=".$lp_id.";" .
			"lms_item_id=".$new_item_id.";" .
			"lms_old_item_id=0;" .
			"lms_been_synchronized=0;" .
			"lms_initialized=0;" .
			"lms_total_lessons=".$mytotal.";" .
			"lms_complete_lessons=".$mycomplete.";" .
			"lms_progress_bar_mod='".$myprogress_mode."';" .
			"lms_view_id=".$view_id.";" .
			"lms_user_id=".$user_id.";" .
			"next_item=".$new_item_id.";" . //this one is very important to replace possible literal strings
			"lms_next_item=".$mynext.";" .
			"lms_previous_item=".$myprevious.";" .
			"lms_item_type = '".$myitemtype."';" .
			"lms_item_credit = '".$mycredit."';" .
			"lms_item_lesson_mode = '".$mylesson_mode."';" .
			"lms_item_launch_data = '".$mylaunch_data."';" .
			"lms_item_interactions_count = '".$myinteractions_count."';"
			);
	$objResponse->addScript("update_toc('unhighlight','".$current_item."');");
	$objResponse->addScript("update_toc('highlight','".$new_item_id."');");
	$objResponse->addScript("update_toc('$mylesson_status','".$new_item_id."');");
	$objResponse->addScript("update_progress_bar('$mycomplete','$mytotal','$myprogress_mode');");

	$mylp->set_error_msg('');
	$mylp->prerequisites_match(); //check the prerequisites are all complete
	if($debug>1){error_log('Prereq_match() returned '.htmlentities($mylp->error),0);}
	$objResponse->addScript("update_message_frame('".str_replace("'","\'",htmlentities($mylp->error))."');");
	$_SESSION['lpobject'] = serialize($mylp);
	return $objResponse;
}
require('lp_comm.common.php');
$xajax->processRequests();
?>