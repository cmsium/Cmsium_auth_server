<?php
/**
 * Библиотека содержит примитивные функции для построения заголовков ответов сервера на запросы API
 */
class HeadersController {

    private static $instance;

    /**
     * Get  Instance of HeadersController
     *
     * @return object DBConnector New instance or self
     */
    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {}
    protected function __clone(){}

    /**
     * Функция определяет заголовок Content-Type, принимая тип контента для ответа.
     *
     * Опционально можно задать кодировку контента, передав её во второй параметр
     *
     * @param $params array Параметры
     * @return bool Всегда возвращает true
     */
    public function respondContentType($params) {
        $header = "Content-Type: {$params['value']}";
        if (isset($params['charset'])) {
            $header = $header.";charset={$params['charset']}";
        }
        header($header);
        return true;
    }

    /**
     * Функция принимает абсолютный URI для использования в ответе сервера с заголовком Location
     *
     * @param $params array Параметры
     * @return bool Всегда возвращает true
     */
    public function respondLocation($params) {
        header("Location: {$params['value']}");
        return true;
    }

    /**
     * Функция принимает тело запроса и формирует заголовок ответа сервера в виде хэша по спецификации
     *
     * @param $params array Параметры (value - тело запроса для хэширования)
     * @return bool Всегда возвращает true
     */
    public function respondContentMD5($params) {
        header("Content-MD5: ".base64_encode(md5($params['value'], true)));
        return true;
    }

    /**
     * Функция определяет заголовок Content-Location с параметром для ответа сервера
     *
     * @param $params array Параметры
     * @return bool Всегда возвращает true
     */
    public function respondContentLocation($params) {
        header("Content-Location: {$params['value']}");
        return true;
    }

    /**
     * Функция определяет заголовок Content-Disposition с параметром для ответа сервера
     *
     * @param array $params Параметры
     * @return bool Всегда возвращает true
     */
    public function respondContentDisposition($params) {
        $header = "Content-Disposition: {$params['value']}";
        if (isset($params['filename'])) {
            $header = $header.";filename=".'"'.$params['filename'].'"';
        }
        if (isset($params['options'])) {
            $header = $header.';'.$params['options'];
        }
        header($header);
        return true;
    }

    /**
     * Функция определяет заголовок Content-Range с параметром для ответа сервера
     *
     * @param array $params Параметры
     * @return bool Всегда возвращает true
     */
    public function respondContentRange($params) {
        header("Content-Range: {$params['value']}");
        return true;
    }

    /**
     * Функция определяет заголовок Content-Length с параметром для ответа сервера
     *
     * @param array $params Параметры
     * @return bool Всегда возвращает true
     */
    public function respondContentLength($params) {
        header("Content-Length: {$params['value']}");
        return true;
    }

    /**
     * Функция определяет заголовок Cache-Control с массивом параметров для ответа сервера
     *
     * @param array $params Параметры, по умолчанию max-age=3600, must-revalidate
     * @return bool Всегда возвращает true
     */
    public function respondCacheControl($params = ['value' => ['max-age=3600', 'must-revalidate']]) {
        header("Cache-Control: ".implode(', ', $params['value']));
        return true;
    }

    /**
     * Функция определяет заголовок Accept-Language с параметром для ответа сервера
     *
     * @param array $params Значение заголовка, по умолчанию en-CA
     * @return bool Всегда возвращает true
     */
    public function respondAcceptLanguage($params) {
        header("Accept-Language: {$params['value']}");
        return true;
    }

    /**
     * Функция определяет заголовок Allow с массивом параметров для ответа сервера
     *
     * @param array $params Значения заголовка, по умолчанию GET, POST, PUT
     * @return bool Всегда возвращает true
     */
    public function respondAllow($params = ['value' => ['GET', 'POST', 'PUT']]) {
        header("Accept-Ranges: ".implode(', ', $params['value']));
        return true;
    }

    /**
     * Функция определяет заголовок Accept-Ranges с параметром по умолчанию range-unit
     *
     * @param array $params Параметры, по умолчанию range-unit
     * @return bool Всегда возвращает true
     */
    public function respondAcceptRanges($params = ['value' => 'range-unit']) {
        header("Accept-Ranges: {$params['value']}");
        return true;
    }

    /**
     * Функция определяет заголовок Accept-Encoding с массивом параметров для ответа сервера
     *
     * @param array $params Значения заголовка, по умолчанию gzip, deflate
     * @return bool Всегда возвращает true
     */
    public function respondAcceptEncoding($params = ['value' => ['gzip','deflate']]) {
        header("Accept-Encoding: ".implode(', ', $params['value']));
        return true;
    }

    /**
     * Функция определяет заголовок Accept-Charset с параметром для ответа сервера
     *
     * @param array $params Значение заголовка, по умолчанию utf-8
     * @return bool Всегда возвращает true
     */
    public function respondAcceptCharset($params = ['value' => 'utf-8']) {
        header("Accept-Charset: {$params['value']}");
        return true;
    }

    /**
     * Функция определяет заголовок Accept с параметром для ответа сервера
     *
     * @param array $params Значение заголовка, по умолчанию text/plain
     * @return bool Всегда возвращает true
     */
    public function responseAccept($params = ['value' => 'text/plain']) {
        header("Accept: {$params['value']}");
        return true;
    }

    /**
     * Функция посылает заголовок с ошибкой
     *
     * @param array $params Параметры (error_code, message)
     * @return bool True, функция всегда выполняется
     */
    public function respondError($params) {
        header("X-Application-Error-Code:{$params['error_code']}");
        header("X-Application-Error-Message:{$params['message']}");
        return true;
    }

    /**
     * Функция отправляет ответ сервера в виде CSV вместе с корректными заголовками
     *
     * @param array $params Строка, содержащая CSV (content)
     * @return bool Результат работы функции
     */
    public function respondCSV($params) {
        header('Content-Disposition: attachment; filename="csv_api.csv"');
        header("Content-type: text/csv;charset=utf-8");
        echo $params['content'];
        return true;
    }

    /**
     * Функция отправляет ответ сервера в виде XML вместе с корректными заголовками
     *
     * @param array $params Строка, содержащая XML документ (content)
     * @return bool Результат работы функции
     */
    public function respondXML($params) {
        header('Content-Disposition: attachment; filename="xml_api.xml"');
        header("Content-type: text/xml;charset=utf-8");
        echo $params['content'];
        return true;
    }

    /**
     * Функция преобразует полученный массив данных в JSON и отправляет с заголовками
     *
     * @param array $params Именованный массив для преобразования в JSON и отправки (content)
     * @return bool Результат работы функции
     */
    public function respondJSON($params = ['filename' => "json_api.json"]) {
        header("Content-Disposition: attachment; filename=\"{$params['filename']}\"");
        header('Content-type: application/json;charset=utf-8');
        return true;
    }

    /**
     * Функция определяет для ответа сервера набор заголовков запрещающих использование кэша
     *
     * @param array $params Необязательный параметр, md5 хэш содержимого ответа
     * @return bool Всегда возвращает true
     */
    public function respondNoCache($params) {
        $time = gmdate("D, d M Y H:i:s \G\M\T");
        header("Expires: Sat, 01 Jan 1970 00:00:00 GMT");
        header("Pragma: no-cache");
        header("Cache-Control: no-cache");
        header("Last-Modified: $time");
        if (isset($params['etag'])) {
            header("ETag: \"{$params['etag']}\"");
        }
        return true;
    }

    /**
     * Функция определяет для ответа сервера набор заголовков кэширования
     *
     * @param array $params Параметры:
     * etag - необязательный параметр, md5 хэш содержимого ответа
     * offset - время актуальности кэша в секундах, по умолчанию 1 час
     * @return bool Всегда возвращает true
     */
    public function respondCache($params = ['offset' => 3600]) {
        $time = gmdate("D, d M Y H:i:s \G\M\T");
        $time_expires = gmdate("D, d M Y H:i:s \G\M\T", time() + $params['offset']);
        header("Cache-Control: max-age={$params['offset']}, must-revalidate");
        header("Last-Modified: $time");
        header("Expires: $time_expires");
        if (isset($params['etag'])) {
            header("ETag: \"{$params['etag']}\"");
        }
        return true;
    }

    /**
     * Передает пользовательский заголовок с заданным значением
     *
     * @param array $params Параметры: header, value
     * @return bool Всегда возвращает true
     */
    public function respondCustom($params) {
        header("{$params['header']}: {$params['value']}");
        return true;
    }

    public function __destruct() {
        self::$instance = null;
    }

}