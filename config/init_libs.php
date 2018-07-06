<?php
/**
 * Данный скрипт предназначен для подключения всех библиотек и файлов конфигураций
 */
require_once 'defaults.php';

/**
 * Функция производит рекурсивное подключение всех php файлов из указанной директории
 *
 * @param string $dir Путь для подключения файлов библиотек
 * @param int $depth "Глубина" рекурсивного подключения файлов из директорий
 */
function require_all($dir, $depth=0) {
    // require all php files
    $scan = glob("$dir/*");
    foreach ($scan as $path) {
        if (preg_match('/\.php$/', $path)) {
            require_once $path;
        }
        elseif (is_dir($path)) {
            require_all($path, $depth+1);
        }
    }
}

/**
 * Require modules' defaults from lib directory
 *
 * @param $dir
 */
function require_defaults($dir) {
    $scan = glob("$dir/*/*_defaults.php");
    foreach ($scan as $file) {
        require_once $file;
    }
}

/**
 * Require PHP files from 1-level directories
 *
 * @param string $dir Library directory path
 */
function require_libs($dir) {
    $scan = glob("$dir/*/*.php");
    foreach ($scan as $lib) {
        require_once $lib;
    }
}

require_defaults(ROOTDIR."/app/lib");
require_libs(ROOTDIR."/app/lib");
require_once ROOTDIR.'/app/Controller.php';
require_once ROOTDIR.'/app/Router.php';