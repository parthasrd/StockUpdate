<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');

$session = new session();
$authObj= new auth();
$response  = array(
    'success' => false,
    'message' => ''
);

if( !empty( $_POST ) ) {
    switch( $_POST['method'] ) {
               
        case 'check_authentication':
            parse_str( $_POST['data'], $data );
            $auth_data['store_id'] = $store_id = trim($data['store_id']);
            $auth_data['store_pwd'] = $store_pwd = trim($data['store_pwd']);
            $r = $authObj->get_authentication($auth_data);
            $response['success'] = $r;
            break;
        
    }
}
echo json_encode( $response ); die(0);
?>
