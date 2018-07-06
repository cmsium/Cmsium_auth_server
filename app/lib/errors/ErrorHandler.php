<?php

/**
 * Exception array example: ['code' => 500, 'text' => 'Параметр пользователя не передан!', 'additional_params' => [...]]
 *
 * Class ErrorHandler
 */
class ErrorHandler {

    public static $config_array;

    public static function getConfig($config_name) {
        $path = dirname(__FILE__).'/config.ini';
        if (file_exists($path)) {
            if (self::$config_array == null) {
                $config = parse_ini_file($path);
                self::$config_array = $config;
            }
            return self::$config_array[$config_name];
        } else {
            self::throwException(NO_FILE_FOUND);
        }
    }

    /**
     * Throw app exception
     * @param $exception array Exception name(constant)
     * @param $additional_info mixed Additional params
     * @throws AppException
     */
    public static function throwException($exception, $additional_info = null){
        $message = $exception['text'];
        $code = $exception['code'];
        throw new AppException($message, $code, null, $additional_info);
    }

    /**
     * Render error as response
     * @param $exception array Exception
     * @param $header_only bool True, if message body needed
     * @param $additional_info array Additional information
     */
    public static function renderError($exception, $header_only = true, $additional_info = null) {
        $header_controller = HeadersController::getInstance();
        $header_controller->respondCustom(['header' => 'App-Exception', 'value' => $exception['code']]);
        if ($header_only === true) {
            ob_clean();
            exit;
        } else {
            $converter = DataConverter::getInstance();
            switch (self::getConfig('message_output_format')) {
                case 'text':
                    ob_clean();
                    echo $exception['text'];
                    exit;
                case 'xml':
                    ob_clean();
                    $xml_array = $exception + $additional_info;
                    $xml = $converter->arrayToXML($xml_array, 'exception');
                    $header_controller->respondXML();
                    echo $xml;
                    exit;
                case 'json':
                    ob_clean();
                    $json_array = $exception + $additional_info;
                    $json = $converter->arrayToJSON($json_array);
                    $header_controller->respondJSON();
                    echo $json;
                    exit;
                default:
                    return false;
            }
        }
    }

}