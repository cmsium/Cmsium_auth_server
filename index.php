<?php
require 'config/init_libs.php';
require REQUIRES;

$router = Router::getInstance();
$router->executeAction($_SERVER['REQUEST_URI']);