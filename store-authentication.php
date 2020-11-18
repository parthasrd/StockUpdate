<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>
<?php
$session = new session();
$keyValid = $session->get_session_by_key('_authstore_id');
if($keyValid!='')
{
   echo "<script>";
   echo "window.location.href='".conf::SITE_URL."'";
   echo "</script>";
}
// $session->kill_session();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="">
<meta name="author" content="">
<title>Login | <?php echo conf::SITE_TITLE; ?></title>
<?php include('header-script.php'); ?>
</head>
<body class="">
    
<header id="js-header">
    <nav id="stuck_container" class="navbar navbar-inverse stuck_container">
        <div class="container" style="margin-top:50px;">
            <div class="row">
            <div class="col-sm-4"></div>
            <div class="col-sm-4">
                <a class="navbar-brand" href="<?php echo conf::SITE_URL; ?>">
                    <img class="img-responsive" src="assets/images/sync-itz-logo.png" alt="logo" style="width:80%; margin:auto;">
                </a>
            </div>
            <div class="col-sm-4"></div>
            </div>
        
         </div>
    </nav>
</header>

<div class="rate-us-sec">
    <div class="container"  style="margin-top:100px;">
        <div class="row">
        <div class="col-sm-2"></div>
            <div class="col-sm-8">
                <form id="authForm">
                    <label>Store ID</label>
                    <input type="text" name="store_id" id="store_id" class="form-control" placeholder="Ex. signatureautoparts" required title="Store ID" >
                    <br>
                    <label>Password</label>
                    <input type="password" name="store_pwd" id="store_pwd" class="form-control" placeholder="Password" required title="Password" >
                    <br>
                    <input type="submit" value="SUBMIT" id="storeBttn" name="storeBttn" class="btn btn-primary">
                </form>
            </div>
            <div class="col-sm-2"></div>
        </div>
    </div>
</div>




<?php include('footer-script.php'); ?>



<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<script>
    $('#storeBttn').on('click', function(e) {
        e.preventDefault();
        var store_id = $('#store_id').val().trim();
        if(store_id==''){ 
            $( "#store_id" ).focus();
            return false; 
        }
        $.post("ajax/ajax_auth.php", {
            method: 'check_authentication',
            data: $( '#authForm' ).serialize()
        },  function(data) { console.log(data); 
                location.reload();                
        }, 'json');
    });
</script>

</body>
</html>
