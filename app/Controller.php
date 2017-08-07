<?php
class Controller {

    private static $instance;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Renders an auth form
     *
     * @return string HTML page
     */
    function loginForm() {
        return file_get_contents(ROOTDIR.'/app/views/login_form.html');
    }

    /**
     * Login user using data from POST
     *
     * @return string Plain HTML message
     */
    function loginUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $auth = UserAuth::getInstance();
            if ($auth->login()) {
                return 'Пользователь успешно авторизован!';
            } else {
                return AUTH_ERROR['text'];
            }
        } else {
            return POST_DATA_ABSENT['text'];
        }
    }

    /**
     * Login user using data from POST with JSON-formatted response
     *
     * @return string JSON message
     */
    function loginUserJSON() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $auth = UserAuth::getInstance();
            if ($token = $auth->login(false)) {
                return json_encode(['status' => 'ok', 'token' => $token]);
            } else {
                return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']]);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => POST_DATA_ABSENT['text']]);
        }
    }

    /**
     * Renders a registration form
     *
     * @return string HTML page
     */
    function registerForm() {
        return file_get_contents(ROOTDIR.'/app/views/regist_form.html');
    }

    /**
     * Register user using data from POST
     *
     * @return string Plain HTML message
     */
    function registerUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $data = $validator->ValidateAllByMask($_POST, 'registMask');
            if (!$data)
                return CREATE_VALIDATION_ERROR['text'];
            if ($_POST['password'] != $_POST['password_repeat'])
                return PASSWORD_CHECK_ERROR['text'];
            if (User::create($data)){
                return CREATE_SUCCESS['text'];
            }
            else
                return CREATE_ERROR['text'];
        }
        return POST_DATA_ABSENT['text'];
    }

    /**
     * Register user using data from POST with JSON-formatted message
     *
     * @return string JSON message
     */
    function registerUserJSON() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $data = $validator->ValidateAllByMask($_POST, 'registMask');
            if (!$data)
                return json_encode(['status' => 'error', 'message' => CREATE_VALIDATION_ERROR['text']]);
            if ($_POST['password'] != $_POST['password_repeat'])
                return json_encode(['status' => 'error', 'message' => PASSWORD_CHECK_ERROR['text']]);
            if (User::create($data)){
                return json_encode(['status' => 'ok']);
            }
            else
                return json_encode(['status' => 'error', 'message' => CREATE_ERROR['text']]);
        }
        return json_encode(['status' => 'error', 'message' => POST_DATA_ABSENT['text']]);
    }

    /**
     * Logout user
     *
     * @return string Plain HTML message
     */
    function logoutUser() {
        $auth = UserAuth::getInstance();
        if ($auth->logout()) {
            return LOGOUT_SUCCESS['text'];
        } else {
            return AUTH_ERROR['text'];
        }
    }

    /**
     * Logout user using data from POST with JSON-formatted response
     *
     * @return string JSON message
     */
    function logoutUserJSON() {
        $validator = Validator::getInstance();
        $data = $validator->ValidateAllByMask($_POST, 'tokenValidation');
        if (!$data) {
            return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']]);
        }
        $auth = UserAuth::getInstance();
        if ($auth->logout($data['token'])) {
            return json_encode(['status' => 'ok']);
        } else {
            return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']]);
        }
    }

    /**
     * Verify user token from POST
     *
     * @return string JSON message
     */
    function checkToken() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $data = $validator->ValidateAllByMask($_POST, 'tokenValidation');
            if (!$data) {
                return json_encode(['is_valid' => false]);
            }
            $auth = UserAuth::getInstance();
            if ($user_id = $auth->check($data['token'])) {
                return json_encode(['is_valid' => true, 'user_id' => $user_id]);
            } else {
                return json_encode(['is_valid' => false]);
            }
        } else {
            return json_encode(['is_valid' => false]);
        }
    }

    /**
     * Verify user token from socket request
     *
     * @param $token string User token
     * @return string Socket formatted answer
     */
    function checkTokenSocket($token) {
        $validator = Validator::getInstance();
        $data = $validator->ValidateAllByMask(['token' => $token], 'tokenValidation');
        if (!$data) {
            return "2";
        }
        $auth = UserAuth::getInstance();
        if ($user_id = $auth->check($token)) {
            return "1;;$user_id";
        } else {
            return "2";
        }
    }

}