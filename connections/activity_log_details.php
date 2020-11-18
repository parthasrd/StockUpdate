<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
$db = new database();
$fid = $_REQUEST['fid'];
if(trim($_REQUEST['limit'])!=''){ $limit = $_REQUEST['limit']; } else { $limit = ''; }
if(trim($_REQUEST['pos'])!=''){ $pos = $_REQUEST['pos']; } else { $pos = ''; }



$sql="select * from sync_feed_activitylog where activitylog_feedid ='".$fid."' ";
if(trim($pos)=='fd')
{
    $sql.= " and activitylog_run_status !='C' ";
}
$sql.= "  order by activitylog_id DESC ";

if(trim($limit)!='')
{
    $sql.= " limit ".$limit;
}

$db->query($sql);
$activity_log = $db->resultsetObj();

$sql_feed="select * from sync_feeds where id ='".$fid."' ";
$qry_feed = $db->query($sql_feed);
$feedDtls = $db->singleObj($qry_feed);
if($feedDtls->feed_type_id=='1'){ $activity_txt = 'Products added'; } else { $activity_txt = 'Products updated'; }

?> 

<ul class="schedule-timeline-content-list">
 <?php
  foreach ($activity_log as $key => $each_activity) {
    $completed_row = $each_activity->activitylog_completed_row;
    $total_row = $each_activity->activitylog_total_row;
    if($total_row>0){
        $percentage_row = number_format(($completed_row/$total_row)*100,1);
    }
    else{
        $percentage_row = 0;
    }

    $run_status = activity_status($each_activity->activitylog_run_status);
    $activitylog_ts = date("M d, h:i a",strtotime($each_activity->activitylog_ts));
    $cur_ts = time();
    $sto_ts = strtotime($each_activity->activitylog_ts);
    $diff_time = diff_time($cur_ts,$sto_ts);
 ?>
 <li>
     <div class="total-hours">
         <p><?php echo $diff_time; ?> </p>
         <small><?php echo $activitylog_ts; ?></small>
     </div>
     <small><b>Schedule:</b> <i class="fa fa-hourglass-o" aria-hidden="true"></i> <?php echo $run_status; ?></small>
     <p><b><?php echo $completed_row; ?></b> / <?php echo $total_row; ?> (<?php echo $percentage_row; ?>%) <?php echo $activity_txt; ?></p>
 </li>

 <?php } ?>                             
</ul> 
<?php
function activity_status($status)
{
    switch ($status) {
      case 'I':
        $rtn_status = "Feed Initiated";
        break;
      case 'P':
        $rtn_status = "Pending";
        break;
      case 'R':
        $rtn_status = "Running";
        break;
      case 'C':
        $rtn_status = "Completed";
        break;
      case 'F':
        $rtn_status = "Failed";
        break;
      default:
        $rtn_status = "Unknown";
    }
    return $rtn_status;
}

function diff_time($ts_big,$ts_small)
{
    $diff_sec = ($ts_big - $ts_small);   // in seconds
    $diff_sec_txt = "Few sec ago";

    $diff_min = floor($diff_sec / (60));
    $diff_min_txt = $diff_min." min(s) ago";

    $diff_hrs = floor($diff_min / (60));  // in hours
    $diff_hrs_txt = $diff_hrs." hour(s) ago";

    $diff_day = floor($diff_hrs / (24));  // in hours
    $diff_day_txt = $diff_day." day(s) ago";

    $diff_week = floor($diff_day / (7));  // in hours
    $diff_week_txt = $diff_week." week(s) ago";

    if($diff_sec<60){
        $rtn_status = $diff_sec_txt;
    }
    else if($diff_min<60){
        $rtn_status = $diff_min_txt;
    }
    else if($diff_hrs<24){
        $rtn_status = $diff_hrs_txt;
    }
    else if($diff_day<7){
        $rtn_status = $diff_day_txt;
    }
    else{
        $rtn_status = $diff_week_txt;
    }

    return $rtn_status;
}
?>