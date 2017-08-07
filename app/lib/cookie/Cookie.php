<?php
class Cookie {

    private $params;

    public function __construct($params) {
        $this->params = $params;
    }

    public function set() {
        $params = $this->params;
        $name = $params['name'];
        $value = isset($params['value']) ? $params['value'] : "";
        $expire = isset($params['expire']) ? $params['expire'] : 0;
        $path = isset($params['path']) ? $params['path'] : "";
        $domain = isset($params['domain']) ? $params['domain'] : "";
        $secure = isset($params['secure']) ? $params['secure'] : false;
        $httponly = isset($params['httponly']) ? $params['httponly'] : false;
        if (!setcookie($name, $value, $expire, $path, $domain, $secure, $httponly)) {
            throw new Exception(COOKIE_SET_ERROR['text']);
        }
    }

    public static function checkToken() {
        if (isset($_COOKIE['token'])) {
            return true;
        } else {
            return false;
        }
    }

    public static function getToken() {
        if (isset($_COOKIE['token'])) {
            return substr($_COOKIE['token'], 0, 32);
        } else {
            throw new Exception(COOKIE_GET_ERROR['text']);
        }
    }

    public static function getUserId() {
        if (isset($_COOKIE['token'])) {
            return substr($_COOKIE['token'], 32, 32);
        } else {
            return NULL;
            //ErrorHandler::throwException(COOKIE_GET_ERROR, 'page');
        }
    }

    public function __destruct() {}

}