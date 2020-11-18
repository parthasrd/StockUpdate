<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
$session = new session();
$feedObj= new feed();
$response  = array(
    'success' => false,
    'message' => ''
);

if( !empty( $_POST ) ) {
    switch( $_POST['method'] ) {
        case 'set_connection_method':
            $session->add_session('connection_method', $_POST['connection_method'] );
            $response['success'] = true;
            break;
        case 'test_connection_method':
            parse_str( $_POST['data'], $data );
            switch( $data['connection_method'] ){
                case 'ftp.php':
                    $ftp_host = trim($data['ftp_host']);
                    $ftp_user = trim($data['ftp_user']);
                    $ftp_pwd = trim($data['ftp_pwd']);
                    $res = $feedObj->ftp_method_test_connection($ftp_host,$ftp_user,$ftp_pwd);
                    if( $res ) {
                        $response['success'] = true;
                        $response['message'] = '<p style="color: green">Test connection successful</p>';
                    }
                    else{
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">Test connection not successful</p>';
                    }
                    break;
            }
            break;
        case 'create_new_feed_connection_method':
            parse_str( $_POST['data'], $data );
            switch( $data['connection_method'] ) {
                case 'ftp.php':
                    $connection_method = trim($data['connection_method']);
                    // $feed_meta['first_row_is_header'] = $first_row_is_header = isset($data['first_row_is_header']) ? 1 : 0;
                    $feed_meta['first_row_is_header'] = $first_row_is_header = isset($data['first_row_is_header']) ? 1 : 1;
                    $feed_meta['ftp_user'] = $ftp_user = trim($data['ftp_user']);
                    $feed_meta['ftp_pwd'] = $ftp_pwd = trim($data['ftp_pwd']);
                    $feed_meta['ftp_host'] = $ftp_host = trim($data['ftp_host']);
                    $feed_meta['ftp_dir_path'] = $ftp_dir_path = trim($data['ftp_dir_path']);
                    $feed_meta['after_process'] = $after_process = trim($data['after_process']);
                    $feed_meta['file_format'] = $file_format = trim($data['file_format']);


                    $fid_edit = (trim($data['_edit_id'])!='') ? trim($data['_edit_id']) : '';

                    $FileType = strtolower(pathinfo($ftp_dir_path, PATHINFO_EXTENSION));

                    if ($file_format != $FileType) {
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">Invalid file format!</p>';
                        echo json_encode($response); die(0);
                    }


                    if (!isset($ftp_user) || $ftp_user == '') {
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">Please provide FTP user details</p>';
                        echo json_encode( $response ); die(0);
                    }
                    if (!isset($ftp_pwd) || $ftp_pwd == '') {
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">Please provide FTP password details</p>';
                        echo json_encode( $response ); die(0);
                    }
                    if (!isset($ftp_host) || $ftp_host == '') {
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">Please provide FTP host details</p>';
                        echo json_encode( $response ); die(0);
                    }

                    $res = $feedObj->ftp_method_test_connection($ftp_host, $ftp_user, $ftp_pwd);
                    if (!$res) {
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">FTP connection faild, please check your details.</p>';
                        echo json_encode( $response ); die();
                    }

                    if (!isset($ftp_dir_path) || $ftp_dir_path == '') {
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">Please provide directory & file details</p>';
                        echo json_encode( $response ); die(0);
                    }

                    $filename = 'ftp://' . $ftp_user . ':' . $ftp_pwd . '@' . $ftp_host . $ftp_dir_path;
                    if (!file_exists($filename)) {
                        $response['success'] = false;
                        $response['message'] = '<p style="color: red">We can\'t connect the file, please check the file details.</p>';
                        echo json_encode( $response ); die(0);
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
                        $feedObj->get_csv_heaer_title($filename);
                        $session->add_session('feed_id', $feed_id );
                        $response['success'] = true;
                    }
                break;
                
            }
            break;
        case 'create_field_mapping':
            parse_str( $_POST['data'], $data );
            $build_arr = array();
            $values = $data['mapping']["'values'"];
            $keys = $data['mapping']["'keys'"];

            $fid_edit = (trim($data['_edit_id'])!='') ? trim($data['_edit_id']) : '';


            $stored_feed_type = $session->get_session_by_key('feed_type');

            $build_arr = array_combine($keys,$values);

            $variant_sku = trim($build_arr['variants-sku']);
            $title = trim($build_arr['title']);
            $variant_price = trim($build_arr['variants-price']);
            $variant = trim($build_arr['variant']);


            $meta_data = array();
            $arrtmp = array();
            
            $c=0;
            foreach($keys as $vkey)
            {
                $vkeystrng = strtolower($vkey);
                $search = 'metafield';
                if(preg_match("/{$search}/i", strtolower($vkeystrng))) {
                    $vkey_rmvspc = str_replace(' ', '_', $vkeystrng)."_val";
                    
                    $meta_data['metafield_namespace'] = $metafield_namespace =  $data['metafield_namespace'][$c];
                    $meta_data['metafield_key'] = $metafield_key =  $data['metafield_key'][$c];
                    $meta_data['meta_val_type'] = $meta_val_type =  $data['meta_val_type'][$c];
                    $meta_data['metafield_owner'] = $metafield_owner =  $data['metafield_owner'][$c]; 
                    $meta_data['metafield_value'] = $metafield_value =  $build_arr[$vkey];

                    $arrtmp[] = $meta_data;
                    $c++;
                    // if($c==1){break;}    
                }                
                
            }            

            $build_arr['metafield'] = $arrtmp;
            $new_build_arr = $build_arr;

            
            if($stored_feed_type==2)
            {
                if($variant_sku =='')
                {
                    $response['success'] = false;
                    $response['message'] = '<p style="color: red">SKU field is required</p>';
                    echo json_encode( $response ); die(0);
                }
            }
            else
            {
                if($variant_sku =='' || $title =='' || $variant_price =='')
                {
                    $response['success'] = false;
                    $response['message'] = '<p style="color: red">SKU, Title and Price fields are required</p>';
                    echo json_encode( $response ); die(0);
                }
            }

            $filtered_array = array_filter($new_build_arr);
            $resp = $feedObj->feed_mapping_fields($filtered_array, $fid_edit);
            // $response['success'] = $resp;
            $response['success'] = true;
            break;
        case 'delete_feed_method':
            parse_str( $_POST['data'], $data );
            $feed_id = trim($data['feed_id']);
            $feed_data['inactive_reason'] = $delete_message = trim($data['delete_message']);
            $feed_data['status'] = 'N';
            if($feed_id=='')
            {
                $response['success'] = false;
                echo json_encode( $response ); die(0);
            }
            $feedObj->feed_delete_process($feed_data,$feed_id);
            $response['success'] = true;
            break;
        case 'create_feed_advance_setting':
            parse_str( $_POST['data'], $data );

            $fid_edit = (trim($data['_edit_id'])!='') ? trim($data['_edit_id']) : '';

            $feed_meta['auto_publish_product'] = $auto_publish_product = isset($data['auto_publish_product']) ? 1 : 0;
            $feed_meta['same_image_variant'] = $same_image_variant = isset($data['same_image_variant']) ? 1 : 0;
            $feed_meta['first_image_to_all_variant'] = $first_image_to_all_variant = isset($data['first_image_to_all_variant']) ? 1 : 0;
            $feed_meta['skip_zero_quantity'] = $skip_zero_quantity = isset($data['skip_zero_quantity']) ? 1 : 0;
            $feed_meta['new_product_tag'] = $new_product_tag = trim($data['new_product_tag']);
            $meta_key = 'feed_advance_setting';
            $feedObj->create_feed_advance_setteng($feed_meta,$meta_key,$fid_edit);
            $response['success'] = true;
            break;
        case 'create_feed_update_advance_setting':
            parse_str( $_POST['data'], $data );

            $fid_edit = (trim($data['_edit_id'])!='') ? trim($data['_edit_id']) : '';

            $feed_meta['auto_publish_products'] = $auto_publish_products = trim($data['auto_publish_products']);
            $feed_meta['auto_hide_products'] = $auto_hide_products = trim($data['auto_hide_products']);
            // $feed_meta['low_stock_lavel'] = $low_stock_lavel =  trim($data['low_stock_lavel']);
            $feed_meta['low_stock_lavel'] = $low_stock_lavel =  0;
            $meta_key = 'feed_update_advance_setting';
            $rv = $feedObj->create_feed_advance_setteng($feed_meta,$meta_key,$fid_edit);
            $response['success'] = true;
            break;
        case 'schedule_feed_method':
            parse_str( $_POST['data'], $data );
            $feed_meta['schedule_status'] = $schedule_status = isset($data['schedule_status']) ? 'Y' : 'N';
            $feed_meta['schedule_frequency'] = $schedule_frequency = trim($data['schedule_frequency']);
            $feed_meta['schedule_time'] = $schedule_time = trim($data['schedule_time']);
            $feed_meta['schedule_popup_id'] = $schedule_popup_id = trim($data['schedule_popup_id']);

            if($schedule_time =='' || $schedule_popup_id =='')
            {
                $response['success'] = false;
                $response['message'] = '<p style="color: red">Please put correct information and try again.</p>';
                echo json_encode( $response ); die(0);
            }

            $feedObj->create_schedule_feed($feed_meta);
            $response['success'] = true;
            break;
        case 'schedule_feed_details':
            parse_str( $_POST['data'], $data );
            $recv = $feedObj->get_schedule_details($data['schedule_popup_id']);
            if(!$recv){
                $response['success'] = false;
                $response['message'] = '<p style="color: red">Please put correct information and try again.</p>';
                echo json_encode( $response ); die(0);
            }
            else{
                $response['success'] = $recv;
            }
            break;
        case 'upload_product':
            $response['num'] = $feedObj->file_headerfields(2);
            break;
        case 'feed_process_start':
            $feed_id = trim($_POST['feed_id']); 
            $feed_type = $feedObj->get_feed_type($feed_id);

            if($feed_type->feed_type_id==1){ $data = $feedObj->feed_process_start($feed_id); }
            if($feed_type->feed_type_id==2){ $data = $feedObj->feed_update_start($feed_id); }

            // $response['message'] = $data;
            // echo json_encode( $response ); die(0);
            // $data
            
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
        case 'update_title_feed_method':
            parse_str( $_POST['data'], $data );
            $feed_id = trim($data['feed_id']);
            $feed_data['title'] = $feed_title = trim($data['feed_title']);
            if($feed_id=='')
            {
                $response['success'] = false;
                echo json_encode( $response ); die(0);
            }
            $feedObj->update_feed_title($feed_data,$feed_id);
            $response['success'] = true;
            break;
        case 'feed_process_status':
            $feed_id = trim($_POST['feed_id']);
            $response['success'] = true;
            $feed = $feedObj->get_single_feed($feed_id);
            $response['feed_process_status'] = $feed->is_processed;
            break;            
    }
}

echo json_encode( $response ); die(0);
?>
