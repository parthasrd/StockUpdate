<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
$db = new database();
$feeds_obj = new feed();
$feedObjTWo= new feed_two();


$feed_sql = "select * from sync_feeds where is_processed = 'Y' and is_completed = 'Y'";
$db->query($feed_sql);
$active_count = $db->rowCount();
$active_feed = $db->resultsetObj();


if(!empty($active_feed))
{
	foreach($active_feed as $activeFeed)
	{
		$feed_param_rcv = $feeds_obj->feed_param($activeFeed->id, 'feed_connection_param');
		$feed_param = json_decode($feed_param_rcv->meta_value,true); 
		$file_format = $feed_param['file_format'];

		$sql_feed="select * from sync_feeds where id ='".$activeFeed->id."' ";
		$qry_feed = $db->query($sql_feed);
		$feedDtls = $db->singleObj($qry_feed);

		if ( $file_format == 'xlsx' ) {
			if ($feedDtls->feed_type_id=='1') { 
				$feedObjTWo->feed_process_start($activeFeed->id);
			} else { 
				$feedObjTWo->feed_update_start($activeFeed->id);
			}	
		}

		if ( $file_format == 'csv' ) { 
			if ($feedDtls->feed_type_id=='1') { 
				$feeds_obj->feed_process_start($activeFeed->id);
			} else { 
				$feeds_obj->feed_update_start($activeFeed->id);
			}	
		}
			
	}
}

if($active_count>0){
	$sqls = "update sync_test set test_num = test_num + 1  where test_id = 1";
	$db->query($sqls);
	$db->execute();
}
?>