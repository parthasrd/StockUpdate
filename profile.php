<?php 
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');
$authObj = new app_auth();
$session = new session();
$api = conf::MASTER_APP_USER_ID;
$shop = $session->get_session_by_key('_authstore_id');

$ShopDtls = $authObj->get_installed_token_by_shop($shop);

$store = $ShopDtls->login_store_url;
$store_id = str_replace(".myshopify.com","",$store);
$token = $ShopDtls->login_store_token;


$result = $authObj->shopdtls_curlexect($api,$store_id,$token);
$resultDtls = json_decode($result['response']);
$shopDtls = $resultDtls->shop;

// echo "<pre>";
// print_r($shopDtls);
// echo "</pre>";

$password_form = false;
if($_REQUEST['state']=='Password')
{
    $password_form = true;
}


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
<style type="text/css">
    .prof_dtls{
        padding-top: 50px;
        display: block;
    }
    .pwdfrmcls{
        display: none;
    }
    #errmsg_cont, #sucmsg_cont{
        display: none;
    }
</style>
</head>
<body class="">
    <?php include('header.php'); ?>

    <div class="step-2-sec">
        <div class="container">
            <div class="row">
                <div class="step-header">
                    <div class="col-sm-8">
                        <div class="step-header-left">
                            <div class="step-seting-field">
                                <a class="setting-icon"><i class="fa fa-user" aria-hidden="true"></i></a>
                                <a class="setting-field">
                                    <h2>Store Details</h2>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <!--breadcrumb-->
                        <div class="bredcum-sec">
                            <ul class="breadcrumb">
                                <li><a href="/">Home</a></li>
                                <li>Store Details</li>
                            </ul>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="rate-us-sec">
        <div class="container">
            <div class="row prof_dtls">
                <div class="col-md-6">
                    <div><label>Name: </label> <?php echo $shopDtls->name; ?></div>
                    <div><label>Email: </label> <?php echo $shopDtls->email; ?></div>
                    <div><label>Domain: </label> <?php echo $shopDtls->domain; ?></div>
                    <div><label>Shop Owner: </label> <?php echo $shopDtls->shop_owner; ?></div>
                </div>
                    
                <div class="col-md-6" style="display:none;">
                    <div><label>Login : </label> <?php echo str_replace(".myshopify.com","",$shopDtls->myshopify_domain); ?></div>
                    <div><label>Password: </label> ******* <a href="javascript:void(0)" id="cngpwd">Change Password</a></div>
                    
                    <div id="pwdfrm"<?php if(!$password_form){ ?> class="pwdfrmcls"<?php } ?>>
                        <br>
                        <form action="" method="post" id="cngpwdfrm">
                            <div>
                                <label>Old Password: </label> 
                                <input type="password" name="old_pwd" class="form-control" placeholder="Old Password" required="required" title="Old Password">
                            </div>
                            <br>
                            <div>
                                <label>New Password: </label> 
                                <input type="password" name="new_pwd" class="form-control" placeholder="New Password" required="required" title="New Password">
                            </div>
                            <br>
                            <div>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <button type="button" id="cnclBttn" class="btn btn-link">Cancel</button>
                            </div>
                            <br>
                            <div class="alert alert-danger" id="errmsg_cont">
                              <strong>Warning!</strong> <span id="errmsg"></span>
                            </div>
                            <div class="alert alert-success" id="sucmsg_cont">
                              <strong>Success!</strong> <span id="sucmsg"></span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>

<?php include('footer-script.php'); ?>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $("#cngpwd").click(function(){
            var $t = $('#pwdfrm');
            if ($t.is(':visible')) {
                $t.slideUp();
                history.pushState({urlPath:'/profile.php'},"",'/profile.php');                
            } else {
                $t.slideDown();
                history.pushState({urlPath:'/profile.php'},"",'/profile.php?state=Password');
            }
        });
        $("#cnclBttn").click(function(){
            var $t = $('#pwdfrm');
            $t.slideUp();
            history.pushState({urlPath:'/profile.php'},"",'/profile.php'); 
        });

        $('#cngpwdfrm').on('submit', function(e) {
            e.preventDefault();
            $.post("ajax/ajax_auth.php", {
                method: 'change_password',
                data: $( "#cngpwdfrm" ).serialize()
            },  function(data) { console.log(data);
                if( data.success ){
                    $('#cngpwdfrm')[0].reset();
                    $("#sucmsg").html(data.message);
                    $('#sucmsg_cont').show().delay(5000).fadeOut(400);                    
                }
                else{
                    $("#errmsg").html(data.message);
                    $('#errmsg_cont').show().delay(5000).fadeOut(400);
                }
            }, 'json');
        });

    });
</script>

</body>
</html>
