<?php
class Config {

    /**
     * Функция извлечения значений параметров из файла конфигурации
     *
     * @param string $config_name Имя конфига, который нужно прочитать
     * @param bool|string $path Путь до файла с настройками, по умолчанию определяется константой
     * @return string Возвращает значение настройки
     */
    public static function get($config_name, $path = SETTINGS_PATH) {
        if (file_exists($path)) {
            $config = parse_ini_file($path);
            return $config[$config_name];
        } else {
            ErrorHandler::throwException(NO_FILE_FOUND);
        }
    }

}