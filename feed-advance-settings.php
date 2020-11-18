<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
$feed_obj = new feed();
$session = new session();

if(trim($_REQUEST['fid'])!='')
{
    $fid = trim($_REQUEST['fid']);     
}
else
{
    $fid = ''; 
}

if(!$session->exists('feed_id')){
    $session->add_session('feed_id', $fid );
    $session_feed_id = $session->get_session_by_key('feed_id'); 
}


if($session->exists('feed_type'))
{
    $feed_type_id = $session->get_session_by_key('feed_type');
    if($feed_type_id==1){
        $load_fields_page = "connections/add_feed_settings.php";    
        $ajax_method = 'create_feed_advance_setting';    
    }
    if($feed_type_id==2){
        $load_fields_page = "connections/update_feed_settings.php";
        $ajax_method = 'create_feed_update_advance_setting';
    }
}
else{
    $load_fields_page = "";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Feed Step 4 | <?php echo conf::SITE_TITLE; ?></title>
<?php include('header-script.php'); ?>
</head>
<body>
<?php include('header.php'); ?>
<form action="" id="feed_advnc_setting"  method="POST">
    <input type="hidden" name="_edit_id" id="_edit_id" value="<?php echo $fid; ?>">
    <div class="step-2-sec">
        <div class="container">
            <div id="load_section"></div>
        </div>
    </div>

    <!--progress-bar-->
    <div class="step-progress-bar-sec">
        <div class="container">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <!-- <form id="msform"> -->
                        <span class="subError"></span>
                        <a href="/feed-mapping.php?fid=<?php echo $session_feed_id; ?>">
                            <input type="button" name="previous" class="previous action-button-previous" value="Back"/>
                        </a>
                        <!-- progressbar -->
                        <ul id="progressbar">
                            <li class="active"></li>
                            <li class="active"></li>
                            <li class="active"></li>
                        </ul>
                        <!-- fieldsets -->
                        <input type="submit" name="next" class="next action-button" value="Done"/>
                   <!--  </form> -->
                </div>
            </div>
        </div>
    </div>
    
</form>
<?php include('footer-script.php'); ?>

<script type="text/javascript">
$(document).ready(function(){

    $('#feed_advnc_setting').on('submit', function(e) {
        e.preventDefault();
        var edit_id = $('#_edit_id').val();
        $.post("ajax/ajax_feeds.php", {
            method: '<?php echo $ajax_method; ?>',
            data: $( this ).serialize()
        },  function(data) { console.log(data);
            if( data.success ){ 
                if(edit_id==''){
                    var redict_page = '/feed-details.php';
                }
                else{
                    var redict_page = '/feed-details.php?fid='+edit_id;
                }
                window.location.href = redict_page;
            }
            else{
                $('.subError').html(data.message);
            }
        }, 'json');
    });

});
</script>

<script type="text/javascript">
$(document).ready(function(){
    <?php if($session->exists('feed_type') && $load_fields_page!='') { ?>
        $('#load_section').load('<?php echo $load_fields_page; ?>');
    <?php } ?>
});
</script>

</body>
</html>