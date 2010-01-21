<?php
function autoload($class)
{
    $class = str_replace('_', '/', $class);
    include $class . '.php';
}

spl_autoload_register("autoload");

set_include_path(dirname(__FILE__).'/../src'.PATH_SEPARATOR.dirname(__FILE__).'/../lib/php');
 
$db  = new PDO(
    'mysql:host=localhost;dbname=wdn',
    'wdn',
    'wdn'
);
ini_set('display_errors', true);
error_reporting(E_ALL);