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
$history_path = Config::get('history_path');
$migrations_path = Config::get('migrations_path');

foreach (glob(ROOTDIR.$migrations_path.'/*/up.sql') as $dir) {
    preg_match('/.+\/(.+)\/.+\.sql$/', $dir, $matches);
    $dir_name = $matches[1];
    if (MigrationHistoryHandler::read(ROOTDIR.$history_path) == false) {
        $query = file_get_contents($dir);
        DBConnection::performMigrationQuery($query);
        echo "Migration $dir_name complete\n";
        MigrationHistoryHandler::write(ROOTDIR.$history_path, $dir_name);
    } else {
        if (in_array($dir_name, MigrationHistoryHandler::read(ROOTDIR.$history_path))) {
            echo "Migration $dir_name already done\n";
            continue; // Drop current iteration if migration found in history
        } else {
            $query = file_get_contents($dir);
            DBConnection::performMigrationQuery($query);
            echo "Migration $dir_name complete\n";
            MigrationHistoryHandler::write(ROOTDIR.$history_path, $dir_name);
        }
    }
}