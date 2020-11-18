<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>

<div id="autolod_activity">hello</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){
    // var fid = '<?php echo $fid; ?>';
    var fid = '1';
    window.setInterval('update_antivity('+fid+')', 2000); // 30 seconds
    update_antivity(fid);
});

function update_antivity(fid)
{
    // console.log(fid);
    //$("#autolod_activity").load("connections/activity_log_details.php?fid"+fid);
    var data = 'fid='+ fid;
    $.ajax({
        type : 'POST',
        url : 'connections/activity_log_details.php?fid'+fid,
        data:data,
        success : function(data){
            $('#autolod_activity').html(data);
        },
    });
}


</script>