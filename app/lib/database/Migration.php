<?php
class Migration {

    public static function migrate() {
        $history_path = Config::get('history_path');
        $migrations_path = Config::get('migrations_path');
        foreach (glob(ROOTDIR.$migrations_path.'/*/up.sql') as $dir) {
            preg_match('/.+\/(.+)\/.+\.sql$/', $dir, $matches);
            $dir_name = $matches[1];
            if (MigrationHistoryHandler::read(ROOTDIR.$history_path) == false) {
                $query = file_get_contents($dir);
                DBConnection::performMigrationQuery($query);
                MigrationHistoryHandler::write(ROOTDIR.$history_path, $dir_name);
            } else {
                if (in_array($dir_name, MigrationHistoryHandler::read(ROOTDIR.$history_path))) {
                    continue; // Drop current iteration if migration found in history
                } else {
                    $query = file_get_contents($dir);
                    DBConnection::performMigrationQuery($query);
                    MigrationHistoryHandler::write(ROOTDIR.$history_path, $dir_name);
                }
            }
        }
    }

    public static function rollback($migration = 'last') {
        $history_path = Config::get('history_path');
        $migrations_path = Config::get('migrations_path');
        if (empty(MigrationHistoryHandler::read(ROOTDIR.$history_path))) {
            return false;
        } else {
            if ($migration == 'last') {
                $migr_history = MigrationHistoryHandler::read(ROOTDIR.$history_path);
                $last_migr = end($migr_history);
                $query = file_get_contents(ROOTDIR.$migrations_path."/$last_migr/down.sql");
                DBConnection::performMigrationQuery($query);
                MigrationHistoryHandler::deleteLast(ROOTDIR.$history_path);
            } else {
                $rollback_history = array_reverse(glob(ROOTDIR.$migrations_path.'/*/down.sql'));
                foreach ($rollback_history as $dir) {
                    preg_match('/.+\/(.+)\/.+\.sql$/', $dir, $matches);
                    $dir_name = $matches[1];
                    if ($dir_name == $migration) {
                        // Break the loop
                        break;
                    } else {
                        // Perform rollback
                        $query = file_get_contents($dir);
                        DBConnection::performMigrationQuery($query);
                        MigrationHistoryHandler::deleteLast(ROOTDIR.$history_path);
                    }
                }
            }
        }
    }

}