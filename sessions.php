<?php
require_once( $_SERVER['DOCUMENT_ROOT'] . '/includes/autoload.php' );
$oSession = new session();
$session = $oSession->get_all_sessions();

if ( isset( $_GET['param'] ) && 'kill' == $_GET['param'] ) {
    $oSession->kill_session();
    die;
}

echo '<pre>';
// phpcs:ignore Squiz.PHP.DisallowMultipleAssignments.Found
var_dump( gethostname(), $_SERVER['SERVER_ADDR'], gethostbyaddr( $_SERVER['SERVER_ADDR'] ) );
print_r( $session );
print_r( $_COOKIE );
