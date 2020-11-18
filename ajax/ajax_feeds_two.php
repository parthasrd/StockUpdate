<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
$session = new session();
$feedObj= new feed();
$feedObjTWo= new feed_two();

$response  = array(
    'success' => false,
    'message' => ''
);

if( !empty( $_POST ) ) {
    switch( $_POST['method'] ) {
        case 'feed_process_start':
            $feed_id = trim($_POST['feed_id']); 
            $feed_type = $feedObj->get_feed_type($feed_id);

            if ($feed_type->feed_type_id==1) { $data = $feedObjTWo->feed_process_start($feed_id); }

            if ($feed_type->feed_type_id==2) { $data = $feedObjTWo->feed_update_start($feed_id); }

            if ( !empty($feed_id) ) {
                if ( $data == 1 ) {
                    $response['success'] = true;
                    $response['message'] = 'Feed is being processed!';
                } elseif ( $data == 2 ) {
                    $response['message'] = 'Feed is already processed!';
                } elseif ( $data == 3 ) {
                    $response['message'] = 'Feed is under process!';
                } else {
                    $response['message'] = 'Feed doesn\'t exists!';
                }           
            } else {
                $response['message'] = 'Something went wrong. Please try again later!';
            }
            break;
    }
}

echo json_encode( $response ); die(0);
?>
