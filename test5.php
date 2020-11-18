<?php require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php'); ?>


<?php
$feedObj= new feed();
// $feedObj->feed_process_start(1);

echo $feedObj->activity_log_entry(1);

?>