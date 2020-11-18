<?php
error_reporting(E_ERROR | E_PARSE);

function __autoload($class_name) {
    if(file_exists($_SERVER['DOCUMENT_ROOT'] . "/classes/" . strtolower($class_name) . ".class.php")) {
        require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/" . strtolower($class_name) . ".class.php");    
    } else {
        throw new Exception("Unable to load {$class_name}.");
    }
}