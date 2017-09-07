<?php
class UserAuth{

    private static $instance;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct(){}

    /**
     * Функция генерирует токен авторизации, алгоритм md5
     *
     * Генерируется на основе идентификатора пользователя и времени авторизации
     *
     * @param string $user_id ID пользователя в БД
     * @return string Хэш md5
     */
    public function generateToken($user_id) {
        $base_string = $user_id.time();
        return md5($base_string);
    }

    /**
     * Builds the whole token from session and cookie parts and get user_id
     *
     * @return array Auth token and user id
     */
    public function getAuthInfo($token_raw) {
        $token = substr($token_raw, 0, 32);
        $user_id = substr($token_raw, 32, 32);
        return ['user_id' => $user_id, 'token' => $token];
    }

    /**
     * Checks auth presence of the current user
     */
    public function check($token_raw) {
            $conn = DBConnection::getInstance();
            $auth_info = $this->getAuthInfo($token_raw);
            $user_id = $auth_info['user_id'];
            $token = $auth_info['token'];
            $query = "CALL getAuthInfo('$user_id', '$token');";
            $auth_info = $conn->performQueryFetch($query);
            if ($auth_info['token']) {
                $token_create_time = strtotime($auth_info['created_at']);
                if (($token_create_time + TOKEN_LIFETIME) < time()) {
                    $this->logout();
                    return false;
                }
                return $user_id;
            } else {
                return false;
            }
    }

    /**
     * Check required role existence in users roles
     * @param string $req_roles Required roles
     * @return bool
     */
    public static function checkRole($req_roles){
        $req_roles = explode(',',$req_roles);
        $roles = User::getRoles();
        foreach ($req_roles as $req_role) {
            if ($req_role == "0")
                return true;
            if (in_array($req_role, $roles))
                return true;
        }
        return false;
    }

    /**
     * Check if authorized user has the necessary role
     * @param array $allowed_roles Allowed roles
     * @return bool
     */
    public function checkSelfRoles($allowed_roles, $user_id = false){
        if (!$user_id) {
            $user_id = Cookie::getUserId();
        }
        User::setData($user_id);
        $roles = User::getRoles(true);
        $roles_id=[];
        foreach ($roles as $role){
            $roles_id[] = $role['role_id'];
        }
        foreach ($roles_id as $role)
            if (in_array($role,$allowed_roles))
                return true;
        return false;
    }

    /**
     * Login user
     */
    public function login($set_cookie = true) {
        $validator = Validator::getInstance();
        $conn = DBConnection::getInstance();
        if ($validator->ValidateAllByMask($_POST, 'authMask')) {
            $user_presence = User::checkPresence($_POST['logintype'], $_POST['login'], $_POST['password']);
            if ($user_presence) {
                $user_id = $user_presence['id_user'];
                $token = $this->generateToken($user_id);
                if ($set_cookie) {
                    $domain = '.'.Config::get('main_domain');
                    $params = [
                        'name' => 'token',
                        'value' => $token.$user_id,
                        'expire' => time()+TOKEN_LIFETIME,
                        'path' => '/',
                        'domain' => $domain
                    ];
                    var_dump($params);
                    $cookie = new Cookie($params);
                    $cookie->set();
                }
                $query = "CALL writeAuthToken('$user_id','$token');";
                $conn->performQuery($query);
                return $token.$user_id;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Logout user
     * @return bool
     */
    public function logout($raw_token = false) {
        $conn = DBConnection::getInstance();
        if ($raw_token === false) {
            $raw_token = Cookie::getToken() . Cookie::getUserId();
            $cookie = new Cookie(['name' => 'token', 'value' => null, 'expire' => -1, 'path' => '/']);
            $cookie->set();
        }
        $token = $this->getAuthInfo($raw_token)['token'];
        $query = "CALL clearAuthToken('$token');";
        $conn->performQuery($query);
        return true;
    }

    /**
     * Call custom check action permissions function
     *
     * @param string $action Current action
     * @param string|bool $user_id User id
     * @return bool Check permissions status
     */
    public function checkActionPermissions($action, $service_name, $user_id = false){
        $roles = $this->getRoles($action, $service_name);
        if (!$roles) return false;
        if ($roles[0] == 0) return true;
        return $this->checkSelfRoles($roles, $user_id);
    }

    /**
     * Get allowed to action roles list
     *
     * @param string $action Current action
     * @return array|bool Roles array
     */
    public function getRoles($action, $service_name) {
        $conn = DBConnection::getInstance();
        $query = "SELECT * FROM system_actions WHERE name = '$action' AND service_name = '$service_name';";
        $result =  $conn->performQueryFetch($query);
        if (!$result)
            return false;
        $query = "CALL getActionRoles('{$result['action_id']}');";
        $result = $conn->performQueryFetch($query);
        if (!$result)
            return false;
        return explode(',',$result['roles']);
    }

    /**
     * Throw Auth error
     */
//    function error(){
//        ErrorHandler::throwException(AUTH_ERROR);
//    }
}