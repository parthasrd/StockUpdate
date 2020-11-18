<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');

$feeds_obj = new feed();
$session = new session();

$feed_id = $session->get_session_by_key('feed_id');

if(trim($_REQUEST['fid'])!='')
{
    $fid = trim($_REQUEST['fid']);
    //Redirect if unauthorized access
    $sessionObj = new session();
    $appAuthObj = new app_auth();
    $authstore_id = $sessionObj->get_session_by_key('_authstore_id');
    $login_store_id = $appAuthObj->store_data($authstore_id)->login_store_id;
    $data = $appAuthObj->check_store_feed($fid, $login_store_id);
    if (!$data) {
        session_start();
        session_destroy();
        header('Location: /');
        die;
    }  
}
else
{
    $fid = $feed_id; 
}

$feedDtls = $feeds_obj->current_feed($fid);

if($feedDtls->feed_type_id=='1')
{
    $activity_txt = 'Products added';
}
else
{
    $activity_txt = 'Products updated';
}

$scheduleFrquencyArray = array(
    'daily' => 'Daily',
    'weekdays' => 'Weekdays Only',
    'weekends' => 'Weekends Only',
    'friday' => 'Friday Only',
    'saturday' => 'Saturday Only',
    'sunday' => 'Sunday Only',
    'monday_satday' => 'Mon to Sat Only'
);

if(trim($_REQUEST['page'])!='')
{
    $page = trim($_REQUEST['page']);
}
else{
    $page = 1;
}

$limit = '5';
$activity_log = $feeds_obj->activity_log($fid,$limit,$page);


$feed_param_rcv = $feeds_obj->feed_param($fid,'feed_connection_param');
$feed_param = json_decode($feed_param_rcv->meta_value,true); 
$file_format = $feed_param['file_format'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
    
<title>Feed Step 5 | <?php echo conf::SITE_TITLE; ?></title>
<?php include('header-script.php'); ?>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

</head>

<body class="">
<?php include('header.php'); ?>
<div class="step-3-sec">
    <div class="container">
        <div class="row">
            <div class="step-header">
                
                <div class="col-sm-6 col-md-8">
                    <div class="step-header-left">
                        <div class="step-seting-field">
                            <a class="setting-icon"><i class="fa fa-cog" aria-hidden="true"></i></a>
                            <a class="setting-field">
                                <small>Step 5</small>
                                <h2>Feed Details</h2>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="filter-sec">
                        <a href="/" class="setup"> Dashboard</a>
                        <!-- <a href="#" class="setup"><span><i class="fa fa-cog" aria-hidden="true"></i></span> Setting</a>
                        <a href="#" class="filter">Filter <span><i class="fa fa-sliders" aria-hidden="true"></i></span></a>
                        <a href="#" class="refresh"><i class="fa fa-refresh" aria-hidden="true"></i></a> -->
                    </div>
                </div>
                <div class="clearfix"></div>
            </div> 
            <div class="feed-stat" style="display: none;">
                <div class=" col-sm-3">
                    <div class="cmn-stat l-update">
                        <small>Last Added</small>
                        <h4>0</h4>
                    </div>
                </div>
                <div class=" col-sm-3">
                    <div class="cmn-stat l-update">
                        <small>No. of  Products Before Added</small>
                        <h4>0</h4>
                    </div>
                </div>
                <div class=" col-sm-3">
                    <div class="cmn-stat l-update">
                        <small>Last Processed </small>
                        <h4>21 hours ago</h4>
                    </div>
                </div>
                <div class=" col-sm-3">
                    <div class="cmn-stat l-update">
                        <small>Feed Status</small>
                        <h4>Ready</h4>
                    </div>
                </div>
                <div class="clearfix"></div>
            </div>
            <!--Form-section-->
            <div class="feed-details-sec">
                <div class="col-sm-8">
                <div class="_feed-details-content">
                    <div class="schedule-timeline-content">
                        <h4>Activity Log History</h4><br>
                        <div id="autolod_activity"></div>                        
                        <div>
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

                                    $run_status = $feeds_obj->activity_status($each_activity->activitylog_run_status);
                                    $activitylog_ts = date("M d, h:i a",strtotime($each_activity->activitylog_ts));
                                    $cur_ts = time();
                                    $sto_ts = strtotime($each_activity->activitylog_ts);
                                    $diff_time = $feeds_obj->diff_time($cur_ts,$sto_ts);

                                    $log_file_name = $_SERVER['DOCUMENT_ROOT'] . conf::ACTIVITY_LOG_PATH . $each_activity->activitylog_file;
                                 ?>
                                 <li>
                                     <div class="total-hours">
                                         <p><?php echo $diff_time; ?> </p>
                                         <small><?php echo $activitylog_ts; ?></small>
                                     </div>
                                     <small><b>Schedule:</b> <i class="fa fa-hourglass-o" aria-hidden="true"></i> <?php echo $run_status; ?></small>
                                     <p><b><?php echo $completed_row; ?></b> / <?php echo $total_row; ?> (<?php echo $percentage_row; ?>%) <?php echo $activity_txt; ?></p>
                                    <?php if ( trim($each_activity->activitylog_file) != '' && trim($each_activity->activitylog_skipped_file) != '' ) { ?>
                                        Download: 
                                        &nbsp;
                                        <a href="<?php echo conf::ACTIVITY_LOG_PATH . $each_activity->activitylog_file; ?>" download><i class="fa fa-download"></i> Used Products</a>
                                         | 
                                        <a href="<?php echo conf::ACTIVITY_LOG_PATH . 'activitylogskippedproducts/' . $each_activity->activitylog_skipped_file; ?>" download><i class="fa fa-download"></i> Unused Products</a>
                                    <?php } ?>
                                 </li>

                                 <?php } ?>                             
                             </ul>
                        </div>
                        <a href="feed-activity-log.php?fid=<?php echo $fid; ?>" class="more-activity">more activity logs</a>

                        <!-- <div class="pagination-sec">
                          <ul class="pagination">
                            <li class="page-item"><a class="page-link" href="#"><i class="fa fa-angle-left" aria-hidden="true"></i></a></li>
                            <li class="page-item"><a class="page-link active" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#"><i class="fa fa-angle-right" aria-hidden="true"></i></a></li>
                            </ul>
                        </div> -->
                    </div>
                </div>
                </div>
                <div class="col-sm-4">
                    <?php 
                    if($feedDtls->is_scheduled=='Y'){ 
                        $sechudleDtls = $feeds_obj->get_schedule_details($feedDtls->id); 
                        $timeset = $sechudleDtls->schedule_time;
                        $frqncyset = $scheduleFrquencyArray[$sechudleDtls->schedule_frequency];
                    ?>
                    <div class="schedule-time">
                         <p>Schedule is on <a href="javascript:void(0)" class="daly-time" onClick="openNav()" style="border:1px solid #0e93fb; color:#0e93fb"><?php echo $frqncyset; ?> at <?php echo $timeset; ?></a></p>
                    </div>
                    <div class="start-buttn-sec">

                     <?php if($feedDtls->is_processed=='N'){ ?>   
                     <a href="javascript:void(0)" id="startprocess"  data-feed_id="<?php echo $feedDtls->id; ?>" class="start-process">Start-Process</a>
                     <?php } else { ?>
                        <a href="javascript:void(0)" class="start-process">Processing</a>                        
                     <?php } ?>
                    </div>
                    <?php } else { ?>
                    <div class="schedule-time">
                        <p>Schedule is off <a href="javascript:void(0)" class="daly-time" onClick="openNav()">Turn on here</a></p>
                    </div>
                    <div class="start-buttn-sec">
                    <?php if($feedDtls->is_processed=='N'){ ?>
                        <a href="javascript:void(0)" id="startprocess"  data-feed_id="<?php echo $feedDtls->id; ?>" class="start-process sp_<?php echo $feedDtls->id; ?>">Start-Process</a>
                    <?php } else { ?>
                        <a href="javascript:void(0)" class="start-process sp_<?php echo $feedDtls->id; ?>">Processing</a>                        
                     <?php } ?>
                    </div>
                    <?php } ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>  

<div id="schedule_feed" class="biz-overlay">
  <div class="biz-overlay-content">
   <div class="diz-middle">
    <div class="biz-form">
        <form id="schedule_form">
         <a href="javascript:void(0)" class="closebtn" onClick="closeNav()">&times;</a>
         <h3><i class="fa fa-long-arrow-right" aria-hidden="true"></i> How frequent should this feed run?</h3>
         <input type="hidden" name="schedule_popup_id" id="schedule_popup_id" value="<?php echo $feedDtls->id; ?>" >
        <div class="message-box-content">
         <div class="sch-enable">
             <label class="switch">
                <input class="switch-input" type="checkbox" name="schedule_status" id="schedule_status" />
                <span class="switch-label" data-on="On" data-off="Off"></span> 
                <span class="switch-handle"></span>
                <span class="switch-label ena-des" data-on="Schedule is enabled" data-off="Schedule is disable"></span>  
            </label>    
         </div>
         <div class="schedule-time-content">
             <div class="col-sm-6">
                 <label><b>*</b> Schedule Frequency:</label>
                 <select id="schedule_frequency" name="schedule_frequency">
                     <i class="fa fa-angle-down" aria-hidden="true"></i>
                    <?php foreach($scheduleFrquencyArray as $fval => $ftxt){ ?>
                    <option value="<?php echo $fval; ?>"><?php echo $ftxt; ?></option>
                    <?php } ?>
                </select>
             </div>
             <div class="col-sm-6">
                 <label><b>*</b> Schedule time:</label>
                 <input type="text" id="timepicker" name="schedule_time" class="form-control timepicker" value="01:00 AM"/>
             </div>             
             <div class="clearfix"></div>
         </div>    
         <a href="javascript:void(0)" onClick="closeNav()" class="help-buttn">Cancel </a>
         <a href="#" id="scheduleBttn" class="delet-buttn">OK</a> 
        </div> 
        </form>
      </div>
    </div>
   </div>
</div>

    
    
<?php include('footer-script.php'); ?>

<!--popup-->
 <script>
    function openNav() {

        $.post("ajax/ajax_feeds.php", {
            method: 'schedule_feed_details',
            data: $( '#schedule_popup_id' ).serialize()
        },  function(data) { 
                if(data.success){
                    
                    $('#timepicker').val(data.success.schedule_time);
                    $('#schedule_frequency').val(data.success.schedule_frequency);
                    if(data.success.schedule_status=='Y') {
                        $('#schedule_status').prop('checked', true);
                    } else {
                        $('#schedule_status').prop('checked', false);
                    }
                }
                else{   

                    $('#timepicker').val('01:00 AM');
                    $('#schedule_status').prop('checked', false);    
                    $('#schedule_frequency').val('daily');  

                }
        }, 'json');

        document.getElementById("schedule_feed").style.height = "100%";
    }

    function closeNav() {
        document.getElementById("schedule_feed").style.height = "0%";
    }
</script>
   
<!--popup-->  
<script>
    jQuery(document).ready(function (e) {
    function t(t) {
        e(t).bind("click", function (t) {
            t.preventDefault();
            e(this).parent().fadeOut()
        })
    }
    e(".dropdown-toggle").click(function () {
        var t = e(this).parents(".button-dropdown").children(".dropdown-menu").is(":hidden");
        e(".button-dropdown .dropdown-menu").hide();
        e(".button-dropdown .dropdown-toggle").removeClass("active");
        if (t) {
            e(this).parents(".button-dropdown").children(".dropdown-menu").toggle().parents(".button-dropdown").children(".dropdown-toggle").addClass("active")
        }
    });
    e(document).bind("click", function (t) {
        var n = e(t.target);
        if (!n.parents().hasClass("button-dropdown")) e(".button-dropdown .dropdown-menu").hide();
    });
    e(document).bind("click", function (t) {
        var n = e(t.target);
        if (!n.parents().hasClass("button-dropdown")) e(".button-dropdown .dropdown-toggle").removeClass("active");
    })
});
</script>


<script type="text/javascript">
$(document).ready(function(){
    var fileFormat = '<?php echo $file_format; ?>'
    $('#scheduleBttn').on('click', function(e) {
        e.preventDefault();
        $.post("ajax/ajax_feeds.php", {
            method: 'schedule_feed_method',
            data: $( '#schedule_form' ).serialize()
        },  function(data) {
                location.reload();
        }, 'json');
    });

    $('#startprocess').on('click', function(e) {
        $('#startprocess').html('Processing');
        e.preventDefault(); 
        if (fileFormat == 'xlsx') {
            $.post("ajax/ajax_feeds_two.php", {
                method: 'feed_process_start',
                feed_id: $(this).data('feed_id')
            },  function(data) {
                location.reload();   
            }, 'json');
        }
        else {
            $.post("ajax/ajax_feeds.php", {
                method: 'feed_process_start',
                feed_id: $(this).data('feed_id')
            },  function(data) {
                location.reload();
                // $('#startprocess').html('Start-Process');
                //console.log(data);
            }, 'json');
        }
    });

});

</script>


<script type="text/javascript">
$(document).ready(function(){
    var fid = '<?php echo $fid; ?>';
    window.setInterval('update_antivity('+fid+')', 1000); // 30 seconds
    update_antivity(fid);
});

function update_antivity(fid)
{
    // var data = 'fid='+ fid + '&pos=fd';
    // $.ajax({
    //     type : 'POST',
    //     url : 'connections/activity_log_details.php',
    //     data:data,
    //     success : function(data){
    //         $('#autolod_activity').html(data);
    //     },
    // });
 
    $.post("ajax/ajax_feeds.php", {
        method: 'feed_process_status',
        feed_id: fid
    },  function(data) {
            if (data.feed_process_status == 'N' && $('.sp_' + fid).text().trim() == 'Processing') {
                $('.sp_' + fid).html('Start-Process').attr('data-feed_id', fid).attr('id', 'startprocess_' + fid);
                window.location.reload();
            }

            if (data.feed_process_status == 'Y') {
                $('.sp_' + fid).html('Processing').attr('data-feed_id', fid).attr('id', 'startprocess_' + fid);
                setTimeout(function(){ 
                    var data = 'fid='+ fid + '&limit=1&pos=fd';
                    $.ajax({
                        type : 'POST',
                        url : 'connections/activity_log_details_feed_details.php',
                        data:data,
                        success : function(data){
                            $('#autolod_activity').html(data);
                        },
                    });
                }, 300);
            }
    }, 'json');
}
</script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="assets/js/jquery.timepicker.js"></script>
<script> $(function() { $('#timepicker').timepicker({ 'timeFormat': 'h:i A' }); }); </script>
 
</body>
</html>