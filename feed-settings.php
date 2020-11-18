<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
$session = new session();
?>
<?php
if(trim($_REQUEST['fid'])!='')
{
    $feedObj= new feed();
    $fid = trim($_REQUEST['fid']); 
    $session->add_session('edit_fid', $fid );   
    $feedDtls = $feedObj->get_single_feed($fid);
    $session->add_session( 'feed_type', $feedDtls->feed_type_id );
    if($feedDtls->feed_method_id==1)
    {
        $connection_method ='file_upload.php';        
    }
    if($feedDtls->feed_method_id==2)
    {
        $connection_method ='ftp.php';
    }
    $session->add_session('connection_method', $connection_method );    
}
else
{
    $fid = '';
    $session->delete_session_by_key('edit_fid'); 
    $session->delete_session_by_key('connection_method'); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Feed Step 2 | <?php echo conf::SITE_TITLE; ?></title>
<?php include('header-script.php'); ?>
</head>
<body>
    <?php include('header.php'); ?>
    <div class="step-2-sec">
        <div class="container">
            <div class="row">
                <div class="step-header">
                    <div class="col-sm-8">
                        <div class="step-header-left">
                            <div class="step-seting-field">
                                <a class="setting-icon"><i class="fa fa-cog" aria-hidden="true"></i></a>
                                <a class="setting-field">
                                    <small>Step 2</small>
                                    <h2>Feed Settings</h2>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <!--breadcrumb-->
                        <div class="bredcum-sec">
                            <ul class="breadcrumb">
                                <li><a href="/">Home</a></li>
                                <li><a href="/feed-type.php">Feed Type</a></li>
                                <li>Feed Settings</li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <form action="" id="feed_settings"  method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_edit_id" id="_edit_id" value="<?php echo $fid; ?>">
        <div class="container">
            <div class="row">
                <div id="load_method">

                </div>
            </div>
        </div>


        <div class="step-progress-bar-sec">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 col-md-offset-2">
                        <span class="subError" style="top: -25px"></span>
                        <a href="/feed-type.php">
                            <input type="button" name="previous" class="previous action-button-previous" value="Back"/>
                        </a>
                        <!-- progressbar -->
                        <ul id="progressbar">
                            <li class="active"></li>
                            <li></li>
                            <li></li>
                        </ul>
                        <!-- fieldsets -->
                        <input type="submit" id="NxtBttn" name="nextStep_bttn" class="next action-button" value="Next"/>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
<?php include('footer-script.php'); ?>

<script type="text/javascript">
$(document).ready(function(){
    load_connection_method();

    $(document).on('change', '#method_changer', function(event){
        $.post("ajax/ajax_feeds.php", {
            method: 'set_connection_method',
            connection_method: $(this).val()
        },  function(data) {
            if(data.success){
                load_connection_method();            }
        }, 'json');
    });

    $(document).on('click', '#test_conn_bttn', function(event){
        $('#conn_status').html("Please wait for a while to connect...").fadeIn();
        $.post("ajax/ajax_feeds.php", {
            method: 'test_connection_method',
            data: $( "#feed_settings" ).serialize()
        },  function(data) {
                $('#conn_status').html(data.message).delay(3000).fadeOut(1000);
        }, 'json');
    });

    $('#feed_settings').on('submit', function(e) {
        e.preventDefault();

        var edit_id = $('#_edit_id').val();
        var method_changer = $('#method_changer').val();
        
        var feed_type = '<?php echo $session->get_session_by_key('feed_type'); ?>';
        
        if(feed_type==2){
            if(edit_id==''){
                var redict_page = '/update-feed-mapping.php';
            }
            else{
                var redict_page = '/update-feed-mapping.php?fid='+edit_id;
            }            
            console.log('type2');
        }
        else{

            if(edit_id==''){
                var redict_page = '/feed-mapping.php';
            }
            else{
                var redict_page = '/feed-mapping.php?fid='+edit_id;
            }            
            console.log('type1');
        }
        

        if(method_changer.trim()=='file_upload.php'){            
            var form_data = new FormData(this);  
            $('#up_txt').html('File uploading, please wait...').css('color', '#0e93fb').fadeIn();           
            $.ajax({
                url: 'ajax/ajax_fileupload.php', 
                dataType: 'JSON',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,                         
                type: 'post',
                success: function(data){
                    // console.log(data);
                    if ( data.success ) {                       
                        window.location.href = redict_page
                    } else {
                        $('#up_txt').html(data.message).css('color', 'red').delay(3000).fadeOut(1000)
                    }                 
                }
            });

        }
        else{
            $('.subError').html('Processing, please wait…').css('color', '#0e93fb').fadeIn();    
            $.post("ajax/ajax_feeds.php", {
                method: 'create_new_feed_connection_method',
                data: $( "#feed_settings" ).serialize()
            },  function(data) { console.log(data);
                if( data.success ){
                    window.location.href = redict_page;
                }
                else{
                    $('.subError').html(data.message).delay(3000).fadeOut(1000)
                }
            }, 'json');
        }
        
    });

    // $(document).on('change', '#upld_file', function(event){
    //     alert('Hello');
    //     $.post("ajax/ajax_feeds.php", {
    //         method: 'set_connection_method',
    //         connection_method: $(this).val()
    //     },  function(data) {
    //         if(data.success){
    //             load_connection_method();            }
    //     }, 'json');
    // });

});
    function load_connection_method(){
        $('#load_method').load("connections/autoload.php");
    }
</script>
</body>
</html>