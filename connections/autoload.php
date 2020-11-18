<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/includes/autoload.php');
$session = new session();
$s_connection_method = ($session->exists('connection_method')) ? $session->get_session_by_key('connection_method') : '';
$connection_method = (isset($s_connection_method) && ($s_connection_method != '')) ? $s_connection_method : conf::DEFAULT_CONNECT_METHOD ;
include($_SERVER['DOCUMENT_ROOT'].'/connections/'.$connection_method);
?>