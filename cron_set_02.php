<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
$db = new database();

$current_weekdays = date('w');
$current_time = date("h:i A");
$current_daytime_in_minutes = schedule_time_num($current_time);

$sql_shedule = "select sync_feed_schedule.* from sync_feed_schedule inner join sync_feeds on sync_feed_schedule.schedule_feed_id = sync_feeds.id  where sync_feed_schedule.schedule_status = 'Y' and sync_feeds.status ='Y' and sync_feeds.is_completed = 'Y' and sync_feeds.is_processed = 'N' ";

$db->query($sql_shedule);
$shedule_count = $db->rowCount();
$sheduled_feed = $db->resultsetObj();


foreach($sheduled_feed as $eachfeed){
	$feed_id = $eachfeed->schedule_feed_id;
	$frequency = $eachfeed->schedule_frequency;
	$time = $eachfeed->schedule_time;

	$weekdays = schedule_frequency_num($frequency);
	$schedule_daytime_in_minutes = schedule_time_num($time);

	if(in_array($current_weekdays, $weekdays) && $schedule_daytime_in_minutes == $current_daytime_in_minutes)
	{
		$sql_upd = "update sync_feeds set is_processed ='Y' where id='".$feed_id."' ";
		$db->query($sql_upd);
		$db->execute();
	}
	
}


function schedule_frequency_num($frequency){

	switch ($frequency) {
	  case 'daily':
	    $rtn_val = array(0,1,2,3,4,5,6);
	    break;
	  case 'weekdays':
	    $rtn_val = array(0,1,2,3,4);
	    break;
	  case 'weekends':
	    $rtn_val = array(5,6);
	    break;
	  case 'friday':
	    $rtn_val = array(5);
	    break;
	  case 'saturday':
	    $rtn_val = array(6);
	    break;
	  case 'sunday':
	    $rtn_val = array(0);
	    break;
	  case 'monday_satday':
	    $rtn_val = array(1,2,3,4,5,6);
	    break;
	  default:
	    $rtn_val = array();
	}

	return $rtn_val;
}

function schedule_time_num($time)
{
	$time_expo = explode(" ",$time);
	$time_only = $time_expo[0];
	$time_format = $time_expo[1];
	$time_only_expo = explode(":",$time_only);
	$time_hrs = $time_only_expo[0];
	$time_min = $time_only_expo[1];	

	if(strtoupper($time_format) == 'PM')
	{
		$dayinminute = ($time_hrs + 12) * 60 + $time_min;
	}
	else
	{
		$dayinminute = ($time_hrs) * 60 + $time_min;
	}
	return $dayinminute;
}


?>