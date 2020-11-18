<?php
error_reporting(0);
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');

$session = new session();
$feedObj= new feed();
$response  = array(
    'success' => false,
    'message' => ''
);

$file_error = $_FILES['upld_file']['error'];
$file_name = $_FILES['upld_file']['name'];
$target_dir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/uploadfiles/";
$target_file = $target_dir . basename($file_name);
$FileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
$FileName = strtolower(pathinfo($target_file,PATHINFO_FILENAME));
$newfileName = str_shuffle(time()).rand(1111,9999)."_".str_replace(" ","_",$FileName).".".$FileType;
$full_path = $target_dir . $newfileName;
$filetype_allow = array('csv', 'xlsx');

$feed_meta['first_row_is_header'] = $first_row_is_header = 1;
$feed_meta['after_process'] = 0;

$connection_method = trim($_POST['connection_method']);
$feed_meta['file_format'] = $file_format = trim($_POST['file_format']);
$feed_meta['file_name'] = $newfileName;


$fid_edit = (trim($_POST['_edit_id'])!='') ? trim($_POST['_edit_id']) : '';
$is_old_file = (trim($_POST['_is_old_file'])!='') ? trim($_POST['_is_old_file']) : '';

if ($file_format != $FileType) {
    $response['message'] = 'File couldn\'t be uploaded! Please try again!';
    echo json_encode($response); die(0);
}

if($file_error<1 && in_array($FileType, $filetype_allow))
{
    $upld_file_tmp = $_FILES['upld_file']['tmp_name']; 
    $moved = move_uploaded_file($upld_file_tmp, $full_path);
    if($moved){ 
        $response['success'] = true;
        $response['message'] = 'File uploaded successfully!';
    } else{
        $response['message'] = 'File couldn\'t be uploaded! Please try again!';
    }

    $feed_type_id = $session->get_session_by_key('feed_type');
    if($fid_edit=='')
    {
        $feed_id = $feedObj->create_new_feed( $feed_type_id, $connection_method ); 
    }
    else
    {
        $feed_id = $fid_edit;
    }
    if ($feed_id) {
        $feed_meta_data = array(
                'feed_id'=> $feed_id,
                'meta_key'=> 'feed_connection_param',
                'meta_value'=> json_encode($feed_meta)
            );
        $feedObj->create_new_feed_metas( $feed_meta_data, $fid_edit );
        $feedObj->get_csv_heaer_title($full_path);
        $session->add_session('feed_id', $feed_id );
    } 
}
else{
    if($is_old_file!=''){
        $response['success'] = true;
        $response['message'] = 'File uploaded successfully!';
    }
    else{
        $response['message'] = 'File couldn\'t be uploaded. Please try again!';
    }
}

echo json_encode($response); die(0);

?>