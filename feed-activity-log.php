<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
if (trim($_REQUEST['fid'])!='') {
    $feed_id = trim($_REQUEST['fid']);
    
    //Redirect if unauthorized access
    $sessionObj = new session();
    $appAuthObj = new app_auth();
    $authstore_id = $sessionObj->get_session_by_key('_authstore_id');
    $login_store_id = $appAuthObj->store_data($authstore_id)->login_store_id;
    $data = $appAuthObj->check_store_feed($feed_id, $login_store_id);
    if (!$data) {
        session_start();
        session_destroy();
        header('Location: /');
        die;
    }

    $feedObj = new feed();
    $per_page_record = 20;  // Number of entries to show in a page.   
    // Look for a GET variable page if not found default is 1.        
    if (isset($_GET["page"])) {    
        $page  = $_GET["page"];    
    } else {    
      $page = 1;    
    }

    $feedDtls = $feedObj->get_single_feed($feed_id);
    $feed_title = $feedDtls->title;
    if($feedDtls->feed_type_id == '1') {
        $activity_txt = 'Products added';
    } else {
        $activity_txt = 'Products updated';
    }

    $start_from = ($page-1) * $per_page_record;
    $data = $feedObj->activity_log_with_limit($feed_id, $start_from, $per_page_record);        
    $total_records = count($feedObj->activity_log_with_limit($feed_id, '', ''));
} else {
    header('Location: /');
    die;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Activity Log Files | <?php echo conf::SITE_TITLE; ?></title>
    <?php include('header-script.php'); ?>
    <style>
        .pagination {   
            display: inline-block;   
        }   
        .pagination a {   
            font-weight:bold;   
            font-size:14px;   
            color: #337ab7;   
            float: left;   
            padding: 8px 16px;
            margin-right: 3px;  
            text-decoration: none;   
            border:1px solid #337ab7;   
        }   
        .pagination a.active {   
            background-color: #337ab7;
            color: white;
        }   
        .pagination a:hover:not(.active) {   
            background-color: #337ab7;
            color: white;
        }

        .activity_time_details {
            margin-right: 35%;
        }
        ._feed-details-content:after{
            background: none;
        }
    </style>   
</head>
<body>
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
                                <small><?php echo $feed_title; ?></small>
                                <h2>Activity Log History</h2>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="filter-sec">
                        <a href="/" class="setup"> Dashboard</a>
                        <a href="feed-details.php?fid=<?php echo $feed_id; ?>" class="setup"> Back</a>
                        <!-- <a href="#" class="filter">Filter <span><i class="fa fa-sliders" aria-hidden="true"></i></span></a>
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
                <div class="col-sm-12">
                <div class="_feed-details-content">
                    <div class="schedule-timeline-content">
                       <br> 
                        <?php if ($total_records): ?>                       
                        <div>
                             <ul class="schedule-timeline-content-list">
                                <?php 
                                $activity_log = $data;
                                foreach ($activity_log as $key => $each_activity) { 
                                    $completed_row = $each_activity->activitylog_completed_row;
                                    $total_row = $each_activity->activitylog_total_row;
                                    if($total_row>0){
                                        $percentage_row = number_format(($completed_row/$total_row)*100,1);
                                    }
                                    else{
                                        $percentage_row = 0;
                                    }
                                    $run_status = $feedObj->activity_status($each_activity->activitylog_run_status);
                                    $activitylog_ts = date("M d, h:i a",strtotime($each_activity->activitylog_ts));
                                    $cur_ts = time();
                                    $sto_ts = strtotime($each_activity->activitylog_ts);
                                    $diff_time = $feedObj->diff_time($cur_ts,$sto_ts);                
                                ?>
                                <li class="activity_time_details">
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
                        <!-- <a href="javascript:void(0)" class="more-activity"></a> -->
                        <div class="clearfix"></div>
                       <div class="pagination">    
                            <?php
                            if($total_records>$per_page_record)
                            {
                                // Number of pages required.   
                                $total_pages = ceil($total_records / $per_page_record);     
                                $pagLink = "";       
                            
                                if($page >= 2){   
                                    echo "<a href='feed-activity-log.php?fid=2&page=".($page-1)."'>  Prev </a>";   
                                }       
                                        
                                for ($i=1; $i<=$total_pages; $i++) {   
                                if ($i == $page) {   
                                    $pagLink .= "<a class = 'active' href='feed-activity-log.php?fid=2&page="  
                                                                        .$i."'>".$i." </a>";   
                                }               
                                else  {   
                                    $pagLink .= "<a href='feed-activity-log.php?fid=2&page=".$i."'>   
                                                                        ".$i." </a>";     
                                }   
                                };     
                                echo $pagLink;   

                                if($page<$total_pages){   
                                    echo "<a href='feed-activity-log.php?fid=2&page=".($page+1)."'>  Next </a>";   
                                }
                            }
                            ?>
                        </div>

                        <?php else: ?>
                            <strong>No activity log file(s) found!</strong>
                        <?php endif; ?>
                    </div>
                </div>
                </div>
                
                <div class="clearfix"></div>
            </div>
        </div>
    </div>
</div>  

<?php include('footer-script.php'); ?>
</body>
</html>
