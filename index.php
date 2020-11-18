<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
?>
<?php
$feeds_obj = new feed();
$isfeeds = $feeds_obj->get_all_feed_with_details('');

$scheduleFrquencyArray = array(
    'daily' => 'Daily',
    'weekdays' => 'Weekdays Only',
    'weekends' => 'Weekends Only',
    'friday' => 'Friday Only',
    'saturday' => 'Saturday Only',
    'sunday' => 'Sunday Only',
    'monday_satday' => 'Mon to Sat Only'
);

if(isset($_REQUEST['s']))
{
    $srch_val = $_REQUEST['s'];
    if(trim($srch_val)!='')
    {
        $srch_val = $srch_val;
    }
    else
    {
        $srch_val = '';
    }
}
else
{
    $srch_val = '';
}
$feeds = $feeds_obj->get_all_feed_with_details($srch_val);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Dashboard | <?php echo conf::SITE_TITLE; ?></title>
<?php include('header-script.php'); ?>
</head>
<body class="">
    <?php include('header.php'); ?>
<div class="rate-us-sec">
    <div class="container">
        <div class="row">
            <?php
            if( !$isfeeds ) { 
                ?>
                <div class="pro-details no_feed">
                    <h3>Keep over 22 millions inventory levels accurately everyday</h3>
                    <p>Connect your product inventory or feed data from your suppliers, warehouses or others with Stock Update</p>
                    <a href="<?php echo conf::SITE_URL; ?>feed-type.php" class="setup">+ Setup New Feed</a>
                </div>
                <?php
            }
            else{
                                  
                ?>
                    <ul class="rate-point">
                        <li>rate us 5-stars to help StockUpdate even more awesome. Thanks! .</li>
                        <li><a href="#">Give us a 5-stars .</a></li>
                        <li><a href="#">How do we earn it?</a></li>
                    </ul>
                    <div class="filter-sec">
                        <a href="<?php echo conf::SITE_URL; ?>feed-type.php" class="setup">+ Setup New Feed</a>
                        <input type="text" id="srch_field" class="search-fild" name="s" value="<?php echo $srch_val; ?>" placeholder="search feed name...">
                        <a href="javascript:void(0)" id="anch_srch" class="refresh"><!-- <i class="fa fa-refresh" aria-hidden="true"> </i>-->SEARCH</a>
                        <a href="javascript:void(0)" id="cancel_srch" class="refresh"><i class="fa fa-refresh" aria-hidden="true"> </i></a>
                        
                        <?php if($srch_val!=''){ ?>
                            <hr>
                            <label>Search for</label> : <?php echo $srch_val; ?>
                            <hr>
                        <?php } ?>
                        
                    </div>
                    <?php if( $feeds ) { ?>
                        <!--ftp-feed-box-->
                        <?php foreach($feeds as $key => $feedDtls ){ ?>
                        <?php
                            if($feedDtls->is_scheduled=='Y'){
                                $sechudleDtls = $feeds_obj->get_schedule_details($feedDtls->id);
                            }

                            if($feedDtls->feed_type_id=='1')
                            {
                                $editfrompage = 'feed-mapping.php?fid='.$feedDtls->id;
                                $activity_txt = 'Products added';
                            }
                            else
                            {
                                $editfrompage = 'update-feed-mapping.php?fid='.$feedDtls->id;
                                $activity_txt = 'Products updated';
                            }


                            $feed_param_rcv = $feeds_obj->feed_param($feedDtls->id, 'feed_connection_param');
                            $feed_param = json_decode($feed_param_rcv->meta_value,true); 
                            $file_format = $feed_param['file_format'];
                        ?>
                        <?php
                        $limit = 1;
                        $page = 1;
                        $activity_log = $feeds_obj->activity_log_home($feedDtls->id,$limit);
                        ?>
                        <div class="col-sm-4 no-padding">
                            <div class="filter-box">
                                <div class="ftp-sec">
                                    <span><i class="fa fa-server" aria-hidden="true"></i> <?php echo $feedDtls->connection_title; ?></span>
                                    <ul class="ftp-filter">
                                        <li><a href="<?php echo $editfrompage; ?>" class="active"><span class="ftp-set"><i class="fa fa-cogs" aria-hidden="true"></i></span></a></li>
                                        <li><a href="#"><span class="ftp-filt"><i class="fa fa-filter" aria-hidden="true"></i></span></a></li>
                                        <li class="button-dropdown" >
                                            <a href="javascript:void(0)" class="dropdown-toggle">
                                                <span class="ftp-arrow"><i class="fa fa-caret-down" aria-hidden="true"></i></span>
                                            </a>
                                            <ul class="dropdown-menu ftp-remove">
                                                <li>
                                                    <a href="javascript:void(0)" class="popup-butn" onClick="openNav('delete',<?php echo $feedDtls->id; ?>)">
                                                        Delete
                                                    </a>

                                                </li>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                                <div class="update-feed">
                                    <div class="check-box">
                                        <input type="checkbox" class="check">
                                        <label> <?php echo $feedDtls->title; ?></label>
                                        <input type="hidden" id="hidtittle_<?php echo $feedDtls->id; ?>" value="<?php echo $feedDtls->title; ?>">
                                    </div>
                                    <a href="javascript:void(0)" onclick="openNav('edit_title',<?php echo $feedDtls->id; ?>)" class="edit"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>
                                </div>
                                <div class="schedule-timeline">
                                    <div class="schedule-timeline-content">
                                        <!-- <div class="total-hours">
                                            <p>21 hours ago</p>
                                            <small><?php echo date("M d h:i A",strtotime($feedDtls->datetime)); ?></small>
                                        </div> -->
                                        <div id="activity_<?php echo $feedDtls->id; ?>">
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
                                                ?>
                                                <li>
                                                    <div class="total-hours">
                                                        <p><?php echo $diff_time; ?></p>
                                                        <small><?php echo $activitylog_ts; ?></small>
                                                    </div>
                                                    <small><b>Schedule:</b> <i class="fa fa-hourglass-o" aria-hidden="true"></i> <?php echo $run_status; ?></small>
                                                    <p><b><?php echo $completed_row; ?></b> / <?php echo $total_row; ?> (<?php echo $percentage_row; ?>%) <?php echo $activity_txt; ?></p>
                                                </li>
                                                <?php } ?>

                                                <!-- <li>
                                                    <small><b>Schedule:</b> <i class="fa fa-hourglass-o" aria-hidden="true"></i> 5minutes</small>
                                                    <p><b>2025</b> / 2100 (94.8%) Products update</p>
                                                </li> -->

                                            </ul>
                                        </div>
                                        <?php if($feedDtls->is_completed=='Y'){ ?>
                                        <a href="feed-details.php?fid=<?php echo $feedDtls->id; ?>" class="more-activity">more activity logs</a>
                                        <?php } else { ?>
                                        <a href="javascript:alert('Feed is incomplete')" class="more-activity">more activity logs</a>
                                        <?php } ?>
                                    </div>
                                </div>
                                
                                <?php if($feedDtls->is_completed=='Y'){ ?>
                                <?php
                                if($feedDtls->is_scheduled=='Y'){
                                    $sechudleDtls = $feeds_obj->get_schedule_details($feedDtls->id);
                                    $timeset = $sechudleDtls->schedule_time;
                                    $frqncyset = $scheduleFrquencyArray[$sechudleDtls->schedule_frequency];
                                ?>
                                <div class="schedule-time">
                                    <p>Schedule is on <a href="javascript:void(0)" class="daly-time" onClick="openNav('schedule',<?php echo $feedDtls->id; ?>)" style="border:1px solid #0e93fb; color:#0e93fb" ><?php echo $frqncyset; ?> at <?php echo $timeset; ?></a></p>
                                </div>
                                <?php } else { ?>
                                <div class="schedule-time">
                                    <p>Schedule is off <a href="javascript:void(0)" class="daly-time" onClick="openNav('schedule',<?php echo $feedDtls->id; ?>)">Turn on here</a></p>
                                </div>
                                <?php } ?>

                                <?php } else { ?>
                                <div class="schedule-time">
                                    <p>Schedule is off <a href="javascript:alert('Feed is incomplete')" class="daly-time">Turn on here</a></p>
                                </div>
                                <?php } ?>


                                <div class="start-buttn-sec">
                                    <?php if($feedDtls->is_completed=='N'){ ?>
                                        <a href="<?php echo $editfrompage; ?>" class="start-process" >Feed incomplete</a>
                                    <?php } else { ?>
                                        <?php if($feedDtls->is_processed=='Y'){ ?>
                                            <a href="javascript:void(0)" data-type="<?php echo $file_format; ?>" class="start-process startprocessCls sp_<?php echo $feedDtls->id; ?>" >Processing</a>
                                        <?php } else { ?>
                                            <a href="javascript:void(0)" data-type="<?php echo $file_format; ?>" class="start-process startprocessCls sp_<?php echo $feedDtls->id; ?>" id="startprocess_<?php echo $feedDtls->id; ?>" data-feed_id="<?php echo $feedDtls->id; ?>">Start-Process</a>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>


                        <script type="text/javascript">
                        $(document).ready(function(){
                            var fid = '<?php echo $feedDtls->id; ?>';
                            window.setInterval('update_antivity('+fid+')', 2000); // 30 seconds
                            update_antivity(fid);
                        });                        
                        </script>

                        <?php } ?>
                    <?php } else { ?>
                           <div style="margin: 100px auto; text-align: center; color: #bfbdbd; font-size: 30px; ">No Feed Found</div> 
                    <?php } ?>
                    <!--ftp-feed-box-end-->
                    <div class="bottom-batch-buttn-sec" style="display: none;">
                        <a href="#" class="inventory-buttn"><span><i class="fa fa-rocket" aria-hidden="true"></i></span> Batch Inventory Update</a>
                        <p>Learn more about Sync-itz dashboard <a href="#" class="more">here</a></p>
                    </div>
            <?php                
            }
            ?>



        </div>
    </div>
</div>

<!--poop-up-->
<div id="del_feed" class="biz-overlay">
  <div class="biz-overlay-content">
   <div class="diz-middle">
    <div class="biz-form">
        <form id="del_form">
         <a href="javascript:void(0)" class="closebtn" onClick="closeNav()">&times;</a>
         <h3>Are you having trouble setting up this feed?</h3>
         <input type="hidden" name="feed_id" id="del_popup_id" >
        <div class="message-box-content">
         <p>If youneed help setting up the feed, please let us know and will take a look at the issue.</p>
         <textarea name="delete_message" id="delete_message" rows="3" placeholder="Message" class="form-control" required="required"></textarea>
         <!-- <a href="#" class="help-buttn">No, I Need help </a> -->
         <a href="javascript:void(0)" id="delete_bttn" class="delet-buttn">Delete Anyway</a>
        </div>
        </form>
    </div>
    </div>
   </div>
</div>
 <!--poop-up2-->
<div id="schedule_feed" class="biz-overlay">
  <div class="biz-overlay-content">
   <div class="diz-middle">
    <div class="biz-form">
        <form id="schedule_form">
         <a href="javascript:void(0)" class="closebtn" onClick="closeNav()">&times;</a>
         <h3><i class="fa fa-long-arrow-right" aria-hidden="true"></i> How frequent should this feed run?</h3>
         <input type="hidden" name="schedule_popup_id" id="schedule_popup_id" >
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

<!--poopup edit feed title-->
<div id="edit_feed_title" class="biz-overlay">
  <div class="biz-overlay-content">
   <div class="diz-middle">
    <div class="biz-form">
        <form id="updt_title_form">
         <a href="javascript:void(0)" class="closebtn" onClick="closeNav()">&times;</a>
         <h3>Rename Feed</h3>
         <input type="hidden" name="feed_id" id="ed_feed_id" >
        <div class="message-box-content">
         <input type="text" name="feed_title" id="feed_title_id" class="form-control">
         <br>
         <a href="javascript:void(0)" class="help-buttn" onClick="closeNav()">Cancel </a>
         <a href="javascript:void(0)" id="updateTileBttn" class="delet-buttn">Update</a>
        </div>
        </form>
    </div>
    </div>
   </div>
</div>

<?php include('footer-script.php'); ?>

<!--popup-->
 <script>
    function openNav(purpose,feedId) {
        if(purpose=='delete'){ document.getElementById("del_feed").style.height = "100%"; $('#del_popup_id').val(feedId); }
        if(purpose=='schedule'){

            $('#schedule_popup_id').val(feedId);

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

        if(purpose=='edit_title')
        { 
            var feed_title = $('#hidtittle_'+feedId).val();            
            $('#ed_feed_id').val(feedId); 
            $('#feed_title_id').val(feed_title); 
            document.getElementById("edit_feed_title").style.height = "100%";
        }

    }

    function closeNav() {
        $('#timepicker').val('01:00 AM');
        $('#schedule_status').prop('checked', false);
        $('#schedule_frequency').val('daily');
        document.getElementById("del_feed").style.height = "0%";
        document.getElementById("schedule_feed").style.height = "0%";
        document.getElementById("edit_feed_title").style.height = "0%"; 
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

    $('#delete_bttn').on('click', function(e) {
        e.preventDefault();
        $.post("ajax/ajax_feeds.php", {
            method: 'delete_feed_method',
            data: $( '#del_form' ).serialize()
        },  function(data) {
            location.reload();
        }, 'json');
    });

    $('#scheduleBttn').on('click', function(e) {
        e.preventDefault();
        $.post("ajax/ajax_feeds.php", {
            method: 'schedule_feed_method',
            data: $( '#schedule_form' ).serialize()
        },  function(data) {
                location.reload();
        }, 'json');
    });

    $('#updateTileBttn').on('click', function(e) {
        e.preventDefault();
        $.post("ajax/ajax_feeds.php", {
            method: 'update_title_feed_method',
            data: $( '#updt_title_form' ).serialize()
        },  function(data) {
                location.reload();
        }, 'json');
    });

    $('.startprocessCls').on('click', function(e) {
        
        $(this).html('Processing');
        var fid = $(this).data('feed_id');
        var fileFormat = $(this).data('type');
        e.preventDefault();

        if (fileFormat == 'xlsx') {
            $.post("ajax/ajax_feeds_two.php", {
                method: 'feed_process_start',
                feed_id: fid
            },  function(data) {
                location.reload();   
            }, 'json');
        } else {
            $.post("ajax/ajax_feeds.php", {
                method: 'feed_process_start',
                feed_id: fid
            },  function(data) {
                // $('#startprocess_'+fid).html('Start-Process');
                location.reload();
                // console.log(data);
            }, 'json');
        }
    });

    $('#anch_srch').on('click', function(e) { 
        e.preventDefault();
        var srch_field = $('#srch_field').val(); 
        window.location.href = '/index.php?s='+ srch_field; 
    });

    $('#cancel_srch').on('click', function(e) { 
        e.preventDefault();
        var srch_field = $('#srch_field').val(); 
        window.location.href = '/'; 
    });

    
});

</script>

<script type="text/javascript">
function update_antivity(fid)
{
    // var data = 'fid='+ fid + '&limit=1&pos=home';
    // $.ajax({
    //     type : 'POST',
    //     url : 'connections/activity_log_details.php',
    //     data:data,
    //     success : function(data){
    //         $('#activity_'+fid).html(data);
    //     },
    // });

    $.post("ajax/ajax_feeds.php", {
        method: 'feed_process_status',
        feed_id: fid
    },  function(data) {
            if (data.feed_process_status == 'N' && $('.sp_' + fid).text().trim() == 'Processing') {
                $('.sp_' + fid).html('Start-Process').attr('data-feed_id', fid).attr('id', 'startprocess_' + fid);
                var data = 'fid='+ fid + '&limit=1&pos=home';
                $.ajax({
                    type : 'POST',
                    url : 'connections/activity_log_details.php',
                    data:data,
                    success : function(data){
                        $('#activity_'+fid).html(data);
                    },
                });
            }

            if (data.feed_process_status == 'Y') {
                $('.sp_' + fid).html('Processing').attr('data-feed_id', fid).attr('id', 'startprocess_' + fid);
                var data = 'fid='+ fid + '&limit=1&pos=home';
                $.ajax({
                    type : 'POST',
                    url : 'connections/activity_log_details.php',
                    data:data,
                    success : function(data){
                        $('#activity_'+fid).html(data);
                    },
                });
            }

    }, 'json');
}
</script>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="assets/js/jquery.timepicker.js"></script>
<script> $(function() { $('#timepicker').timepicker({ 'timeFormat': 'h:i A' }); }); </script>

</body>
</html>
