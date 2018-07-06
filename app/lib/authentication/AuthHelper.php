<?php
/**
 * Class AuthHelper Auth helper methods
 */
class AuthHelper {

    /**
     * Функция генерирует токен авторизации, алгоритм md5
     *
     * Генерируется на основе идентификатора пользователя и времени авторизации
     *
     * @param string $user_id ID пользователя в БД
     * @return string Хэш md5
     */
    public static function generateToken($user_id) {
        $base_string = $user_id.time();
        return md5($base_string);
    }

    /**
     * Builds the whole token from session and cookie parts and get user_id
     *
     * @return array Auth token and user id
     */
    public static function getAuthInfo($token_raw) {
        $token = substr($token_raw, 0, 32);
        $user_id = substr($token_raw, 32, 32);
        return ['user_id' => $user_id, 'token' => $token];
    }

}