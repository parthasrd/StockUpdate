<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>
<?php
$session_logout = new session();
$db_store_id = $session_logout->get_session_by_key('_authstore_auto_id');
$keyValid = $session_logout->get_session_by_key('_authstore_id');
$shopify = $_GET;
if($keyValid=='')
{  
	$app_auth_obj= new app_auth();
	if($shopify['shop']!='')
	{
		$shop = $shopify['shop'];
		$is_avail =$app_auth_obj->check_store_url($shop);
		if($is_avail)
		{		
			$api_key = conf::APP_API_KEY;
			$scopes = conf::APP_SCOPES;
			$redirect_uri = "https://getstockupdate.com/authorization.php";
			$install_url = "https://".$shop."/admin/oauth/authorize?client_id=".$api_key."&scope=".$scopes."&redirect_uri=".urlencode($redirect_uri);

			// Redirect
			header("Location: " . $install_url);
			die();
		}
		else
		{
			echo "<script>";
			echo "window.location.href='install.php?shop=$shop'";
			echo "</script>";
		}

	}
	else{
		echo "<script>";
		echo "window.location.href='welcome.php'";
		echo "</script>";
	}
}
else{
	$store_name = $session_logout->get_session_by_key('display_name');
	if($shopify['shop']!='')
	{
		echo "<script>";
		echo "window.location.href='".conf::SITE_URL."'";
		echo "</script>";
	}
}