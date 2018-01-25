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

require_all(ROOTDIR."/app/lib");
require_once ROOTDIR.'/app/Controller.php';
require_once ROOTDIR.'/app/Router.php';
require_once ROOTDIR.'/app/ErrorHandler.php';