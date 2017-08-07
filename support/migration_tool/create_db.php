<?php
/**
 * This file is executable
 * Use this script to fill your DB with migrations from the 'migrations' directory
 */
require_once dirname(__DIR__).'/../config/init_libs.php';
foreach (glob(ROOTDIR.'/app/modules/*/interfaces/*.php') as $require_path) {
    require_once $require_path;
}
foreach (glob(ROOTDIR.'/app/modules/*/classes/*.php') as $require_path) {
    require_once $require_path;
}
DBConnection::createDB();