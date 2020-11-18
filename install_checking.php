<?php
// Set variables for our request
$shop = $_GET['shop'];
$api_key = "dd67b49ee49d922de11b5a7816beb7f6;";
$scopes = "write_orders,write_products,write_content,write_script_tags,write_inventory";
$redirect_uri = "https://getstockupdate.com/authorization.php";

// Build install/approval URL to redirect to
$install_url = "https://" . $shop . "/admin/oauth/authorize?client_id=" . $api_key . "&scope=" . $scopes . "&redirect_uri=" . urlencode($redirect_uri);

// Redirect
header("Location: " . $install_url);
die();