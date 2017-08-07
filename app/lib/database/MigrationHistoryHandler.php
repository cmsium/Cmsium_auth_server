<?php
class MigrationHistoryHandler {

    /**
     * Функция, читающая csv файл, содержащий историю миграций
     *
     * @param string $path
     * @return array Массив, состоящий из значений csv файла
     */
    public static function read($path) {
        if (file_exists($path)) {
            $file = fopen($path, "r");
            $contents = fgetcsv($file);
            fclose($file);
            return $contents;
        } else {
            return [];
        }
    }

    /**
     * Функция добавляет версию миграции в файл с историей
     *
     * @param string $path Путь к файлу истории
     * @param string $version Строка, содержащая версию миграции
     */
    public static function write($path, $version) {
        preg_match('/^.+\/(.+)$/', $path, $matches);
        if (trim(file_get_contents($path))) {
            file_put_contents($path, ",$version", FILE_APPEND);
        } else {
            file_put_contents($path, $version);
        }
    }

    /**
     * Функция очищает файл с историей миграций
     *
     * @param string $path Путь к файлу истории миграций
     */
    public static function clear($path) {
        preg_match('/^.+\/(.+)$/', $path, $matches);
        if (file_exists($path)) {
            file_put_contents($path, "");
        } else {
            die('File not found!');
        }
    }

    /**
     * Функция удаляет последнюю версию из файла истории миграций
     *
     * @param $path string Путь к файлу с историей миграций
     */
    public static function deleteLast($path) {
        preg_match('/^.+\/(.+)$/', $path, $matches);
        if (count(self::read(ROOTDIR.Config::get('history_path'))) > 1) {
            $str = preg_replace('/,[a-zA-Z0-9._-]+$/', '', file_get_contents($path));
            file_put_contents($path, $str);
        } else {
            self::clear($path);
        }
    }

}