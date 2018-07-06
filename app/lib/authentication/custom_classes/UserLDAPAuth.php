<?php
/**
 * Class UserLDAPAuth Authenticate users with LDAP
 */
class UserLDAPAuth {

    private static $instance;

    /**
     * Singleton realisation. Returns only one existing instance of class
     *
     * @return UserLDAPAuth Instance of class
     */
    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Checks if current user is authenticated
     *
     * @param string $token_raw Raw token from request
     * @return bool True if auth is valid, else false
     */
    public function check($token_raw) {
        $conn = DBConnection::getInstance();
        $auth_info = AuthHelper::getAuthInfo($token_raw);
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

    // Roles

    /**
     * Check required role existence in users roles
     *
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
     *
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
     * @param string $action Current action
     * @param string|bool $user_id User id
     * @return bool Check permissions status
     */
    public function checkActionPermissionsById($action_id, $user_id = false){
        $roles = $this->getRolesById($action_id);
        if (!$roles) return false;
        if ($roles[0] === '') return false;
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
     * Get allowed to action roles list by id
     *
     * @param string $action Current action
     * @return array|bool Roles array
     */
    public function getRolesById($action_id) {
        $conn = DBConnection::getInstance();
        $query = "CALL getActionRoles('$action_id');";
        $result = $conn->performQueryFetch($query);
        if (!$result)
            return false;
        return explode(',',$result['roles']);
    }

    // Login & Logout

    public function login($set_cookie = true) {
        $validator = Validator::getInstance();
        $conn = DBConnection::getInstance();
        if ($validator->ValidateAllByMask($_POST, 'authMask')) {
            $user_ldap_presence = $this->checkPresence($_POST['logintype'], $_POST['login'], $_POST['password']);
            if (!$user_ldap_presence) {
                return false;
            }
            $user_presence = User::checkPresence($_POST['logintype'], $_POST['login'], $_POST['password']);
            if (!$user_presence && LDAP_CREATE_ON_SIGNIN) {
                // TODO: Create user account
                return false;
            } else if (!$user_presence && !LDAP_CREATE_ON_SIGNIN) {
                return false;
            }
            $user_id = $user_presence['id_user'];
            $token = AuthHelper::generateToken($user_id);
            if ($set_cookie) {
                $domain = Config::get('main_domain');
                $params = [
                    'name' => 'token',
                    'value' => $token.$user_id,
                    'expire' => time()+TOKEN_LIFETIME,
                    'path' => '/',
                    'domain' => $domain
                ];
                $cookie = new Cookie($params);
                $cookie->set();
            }
            $query = "CALL writeAuthToken('$user_id','$token');";
            $conn->performQuery($query);
            return $token.$user_id;
        } else {
            return false;
        }
    }

    public function logout($raw_token = false) {
        $conn = DBConnection::getInstance();
        if ($raw_token === false) {
            $raw_token = Cookie::getToken() . Cookie::getUserId();
            $domain = Config::get('main_domain');
            $cookie = new Cookie([
                'name' => 'token',
                'value' => null,
                'expire' => -1,
                'path' => '/',
                'domain' => $domain
            ]);
            $cookie->set();
        }
        $token = AuthHelper::getAuthInfo($raw_token)['token'];
        $query = "CALL clearAuthToken('$token');";
        $conn->performQuery($query);
        return true;
    }

    // LDAP

    /**
     * Checks user existence in LDAP directory and validates password
     *
     * @param string $type Identifier type, possible options: username, email, phone
     * @param string $identifier User identifier value, which is looked for
     * @param string $raw_password User input password
     * @return bool Result
     */
    public function checkPresence($type, $identifier, $raw_password) {
        $ldap = LDAPConnection::getInstance();
        switch ($type) {
            case 'username': $search_attr = 'cn'; break;
            case 'email': $search_attr = 'mail'; break;
            case 'phone': $search_attr = 'telephoneNumber'; break;
        }
        $search = "($search_attr=$identifier)";
        $attrs = ['cn','mail','telephoneNumber'];
        $result = $ldap->search($search, $attrs);
        if ($result['count'] === 0) {
            return false;
        }
        $user_dn = $result[0]['dn'];
        return $ldap->bind($user_dn, $raw_password);
    }

}