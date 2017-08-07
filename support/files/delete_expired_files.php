<?php
require dirname(__DIR__).'/../config/defaults.php';
require ROOTDIR.'/webengine/lib/files/FileActions.php';
require ROOTDIR.'/webengine/lib/files/Files.php';
require ROOTDIR.'/webengine/lib/errors/ErrorHandler.php';
require ROOTDIR.'/webengine/lib/errors/MyException.php';
require ROOTDIR.'/app/modules/database/classes/DBConnection.php';
require ROOTDIR.'/app/modules/files/classes/Config.php';

$action_handler = FileActions::getInstance();
$action_handler->deleteExpiredSandboxFiles();