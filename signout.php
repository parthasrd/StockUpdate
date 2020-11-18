<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>
<?php
$session = new session();
$session->kill_session();

echo "<script>";
// echo "window.location.href='store-authentication.php'";
echo "window.location.href='welcome.php'";
echo "</script>";
?>
<div style="margin: 100px auto; text-align:center;">
    <h1>You are loging out, thanks</h1>
</div>
