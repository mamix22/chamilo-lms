#!/usr/local/bin/php

<?php
$cidReset=true;
require_once ('/home/dokeos/portal/user_161/main/inc/global.inc.php');
require_once (api_get_path(LIBRARY_PATH).'/fileManage.lib.php');
require_once (api_get_path(CONFIGURATION_PATH) ."/add_course.conf.php");
require_once (api_get_path(LIBRARY_PATH)."/add_course.lib.inc.php");
require_once (api_get_path(LIBRARY_PATH)."/course.lib.php");
require (api_get_path(LIBRARY_PATH)."/groupmanager.lib.php");
require_once (api_get_path(LIBRARY_PATH)."/surveymanager.lib.php");
require_once (api_get_path(LIBRARY_PATH)."/usermanager.lib.php");
$table_reminder=Database::get_main_table(MAIN_SURVEY_REMINDER_TABLE);
$sql_remind="SELECT * FROM $table_reminder WHERE access='0'";
$res_remind=api_sql_query($sql_remind);
while($obj=mysql_fetch_object($res_remind))
{
$time=getdate();
$time=$time['yday'];
if($obj->access=='0' && $obj->reminder_time==$time)
{
 $surveyid=$obj->sid;
 $db_name=$obj->db_name;
 $to=$obj->email;
 $subject=$obj->subject;
 $message=$obj->content;
 $sender_name = $_SESSION['_user']['lastName'].' '.$_SESSION['_user']['firstName'];
 $email = $_SESSION['_user']['mail']; 	
 $headers  = " MIME-Version: 1.0 \r\n";
 $headers .= " Content-type: text/html; charset=iso-8859-1\r\n";	
 $headers .= 'From: '.$sender_name.' <'.$to.'>' . "\r\n";
 $headers="From:$to\r\nReply-to: $to\r\nContent-type: text/html; charset=us-ascii";
 @mail($to,$subject,$message,$headers);
 $end=explode("-",$obj->avail_till);
 $date=mktime(0, 0, 0, $end[1],$end[2],$end[0]);
 $curr=mktime(0, 0, 0, $time['mon'], $time['mday'], $time['year']);
 if($curr<$date)
	{
	 switch($obj->reminder_choice)
	 {
		case "1":
		 {
			$day = $time+7;
			$sql_insert="UPDATE reminder_dummy SET reminder_time='$day' WHERE sid='$surveyid' AND db_name='$db_name' AND email='$to'";
			$res_insert=mysql_query($sql_insert);
			break;
		 }
		case "2":
		 {
			$day = $time+14;
			$sql_insert="UPDATE reminder_dummy SET reminder_time='$day' WHERE sid='$surveyid' AND db_name='$db_name' AND email='$to'";
			$res_insert=mysql_query($sql_insert);
			break;
		 }
		 case "3":
		 {
			$day = $time+30;
			$sql_insert="UPDATE reminder_dummy SET reminder_time='$day' WHERE sid='$surveyid' AND db_name='$db_name' AND email='$to'";
			$res_insert=mysql_query($sql_insert);
			break;
		 }
	 }
   }
 }
}

?>
