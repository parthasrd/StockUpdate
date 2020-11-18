<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); 
// Set variables for our request
$shop = $_GET['shop'];
$api_key = conf::APP_API_KEY;
$scopes = conf::APP_SCOPES;
$redirect_uri = "https://getstockupdate.com/generate_token.php";

// Build install/approval URL to redirect to
$install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

// Redirect
header("Location: " . $install_url);
die();