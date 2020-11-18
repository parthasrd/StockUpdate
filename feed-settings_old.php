<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
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

    <form action="" id="feed_settings"  method="POST">
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
                        <span class="subError"></span>
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
        $('#conn_status').html("Please wait for a while connect...");
        $.post("ajax/ajax_feeds.php", {
            method: 'test_connection_method',
            data: $( "#feed_settings" ).serialize()
        },  function(data) {
                $('#conn_status').html(data.message);
        }, 'json');
    });

    $('#feed_settings').on('submit', function(e) {
        e.preventDefault();
        $.post("ajax/ajax_feeds.php", {
            method: 'create_new_feed_connection_method',
            data: $( this ).serialize()
        },  function(data) {
            if( data.success )
                window.location.href = "/feed-mapping.php";
            else
                $('.subError').html(data.message);
        }, 'json');
    });
});
    function load_connection_method(){
        $('#load_method').load("connections/autoload.php");
    }
</script>
</body>
</html>