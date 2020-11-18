<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/authentication.php');

$feedObj = new feed();
$all_feeds = $feedObj->get_all_feeds();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed Activity Log Files</title>
    <?php include('header-script.php'); ?>
</head>
<body>
    <?php include('header.php'); ?>
    <div class="container">
        <h1>Download your activity log file(s)</h1>
        <hr>
        <?php foreach ($all_feeds as $feed) : 
            $data = $feedObj->activity_log($feed['id'], '');
            foreach ($data as $key => $value) {
                if (!isset($value->activitylog_file) || empty($value->activitylog_file)) {
                    unset($data[$key]);
                }
            }
            ?>
            <?php if ($data) : ?>
                <p>Feed Name: <?php echo $feed['title']; ?></p>
                <p>Activity File Download Link(s):</p>
                <ul>
                    <?php $i = 0; foreach($data as $value) : $i ++; ?>
                        <p>Activity log file <?php echo $i; ?>: <a href="<?php echo conf::ACTIVITY_LOG_PATH . $value->activitylog_file; ?>" download>Download File</a></p>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php include('footer-script.php'); ?>
</body>
</html>
