<?php
require 'config/init_libs.php';
require REQUIRES;
include_once ROOTDIR.'/config/commands_map.php';

if (isset ($argv[1])) {
    $controller = Controller::getInstance();
    $arg = explode(';;', urldecode($argv[1]));
    $command = array_shift($arg);
    if (!isset($commands[$command])) {
        echo "Command not found".PHP_EOL;
        return;
    }
    $command = $commands[$command];
    if (!empty($arg)){
       echo $controller->$command(...$arg).PHP_EOL;
    } else {
       echo $controller->$command().PHP_EOL;
    }
    return;
}