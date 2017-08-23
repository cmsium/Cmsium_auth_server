<?php
class Controller {

    private static $instance;
    public static $user_id;

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
                return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']],JSON_UNESCAPED_UNICODE);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => POST_DATA_ABSENT['text']],JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Renders a registration form
     *
     * @return string HTML page
     */
    function registerForm() {
        $roles = User::getAllRoles();
        include ROOTDIR.'/app/views/regist_form.html.php';
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
            $data['roles'] = $_POST['roles'];
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
                return json_encode(['status' => 'error', 'message' => CREATE_VALIDATION_ERROR['text']],JSON_UNESCAPED_UNICODE);
            $data['roles'] = $_POST['roles'];
            if ($_POST['password'] != $_POST['password_repeat'])
                return json_encode(['status' => 'error', 'message' => PASSWORD_CHECK_ERROR['text']],JSON_UNESCAPED_UNICODE);
            if (User::create($data)){
                return json_encode(['status' => 'ok']);
            }
            else
                return json_encode(['status' => 'error', 'message' => CREATE_ERROR['text']],JSON_UNESCAPED_UNICODE);
        }
        return json_encode(['status' => 'error', 'message' => POST_DATA_ABSENT['text']],JSON_UNESCAPED_UNICODE);
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
            return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']],JSON_UNESCAPED_UNICODE);
        }
        $auth = UserAuth::getInstance();
        if ($auth->logout($data['token'])) {
            return json_encode(['status' => 'ok']);
        } else {
            return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']],JSON_UNESCAPED_UNICODE);
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

    function getUserRolesJSON() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $data = $validator->ValidateAllByMask($_POST, 'tokenValidation');
            if (!$data) {
                return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']], JSON_UNESCAPED_UNICODE);
            }
            $auth = UserAuth::getInstance();
            if ($user_id = $auth->check($data['token'])) {
                if (!$roles_result_array = User::getUserRoles($user_id)) {
                    return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']], JSON_UNESCAPED_UNICODE);
                }
                $result_array = [];
                foreach ($roles_result_array as $row) {
                    $result_array[] = [$row['name'], $row['t_name']];
                }
                return json_encode(['status' => 'ok', 'roles' => $result_array],JSON_UNESCAPED_UNICODE);
            } else {
                return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']], JSON_UNESCAPED_UNICODE);
        }
    }

    function allUsers() {
        // TODO: Authorization
        $validator = Validator::getInstance();
        $data = $validator->ValidateAllByMask($_GET, 'usersMask');
        if ($data === FALSE) {
            return USERS_FILTER_VALIDATION_ERROR['text'];
        }
        if (isset($_GET['start'])){
            $start = (int)$_GET['start'];
        } else {
            $start = 0;
        }
        $offset = PAGES_OFFSET;
        $user_data = User::getAllWithFilters($data,$start,$offset);
        include ROOTDIR.'/app/views/all_users.html.php';
    }

    function showUser() {
        // TODO: Authorization
        if ($_GET['id']) {
            $ref_handler = ReferenceHandler::getInstance();
            $user_data = User::find($_GET['id']);
            $roles = explode(", ",$user_data['roles']);
            $converter = DataConverter::getInstance();
            $user_props = [];
            foreach ($roles as $role){
                $role_data = User::readProps($_GET['id'],User::getPropsTableName($role));
                $country_iso = '';
                if ($role_data) {
                    foreach ($role_data as $column_name => $value) {
                        if (ReferenceHandler::getRefModule($role.'_properties', $column_name) == 'address_country')
                            $country_iso = $value;
                        $props = ['userfiles' => ['object_props' => [], 'method_props' => [$value, true]],
                            'address_object' => ['object_props' => [],
                                'method_props' => [$country_iso,$value, true]],
                            'address_country' => ['object_props' => [$value], 'method_props' => []],
                            'full_address_object' => ['object_props' => [],
                                'method_props' => [$country_iso,$value, true]]];
                        $instance = $ref_handler->build($role.'_properties', $column_name, 'read', $props);
                        if ($instance) {
                            $role_data[$column_name] = $instance->getData();
                        }
                    }
                }
                $file = new File(ROOTDIR."/app/lib/users/xml/generated_roles_schemas/$role.xml");
                $xml_array = $converter->XMLToArray($file->getContent());
                $result_xml_array = [];
                if (isset($xml_array['item'][0])) {
                    foreach ($xml_array['item'] as $column) {
                        $result_xml_array[] = [
                            'column_name' => $column['t_column_name'],
                            'column_value' => $role_data[$column['column_name']],
                            'column_type' => $column['column_type']['name']
                        ];
                    }
                } else {
                    $result_xml_array[] = [
                        'column_name' => $xml_array['item']['t_column_name'],
                        'column_value' => $role_data[$xml_array['item']['column_name']],
                        'column_type' => $xml_array['item']['column_type']['name']
                    ];
                }
                $user_props = array_merge($user_props, $result_xml_array);
            }
            $user_data['props'] = $user_props;
            include ROOTDIR.'/app/views/show_user.html.php';
        } else {
            return USER_PARAM_ABSENT['text'];
        }
    }

    function createUserForm() {
        // TODO: Authorization
        $roles = User::getAllRoles();
        include ROOTDIR.'/app/views/create_user_form.html.php';
    }

    function updateMenu() {
        // TODO: Authorization
        if ($_SERVER['REQUEST_METHOD'] == 'GET' and isset($_GET['id'])){
            $id = $_GET['id'];
            User::setData($id);
            $roles = User::getRoles(true);
            $roles[] = ['role'=>'user_properties'];
            include ROOTDIR.'/app/views/user_update_menu.html.php';
        }
    }

    function updateForm() {
        // TODO: Authorization
        if (isset($_GET['id'])) {
            User::setData($_GET['id']);
            $user_data = User::getInfo();
            include ROOTDIR.'/app/views/update_user_form.html.php';
        } else {
            return USER_PARAM_ABSENT['text'];
        }
    }

    function updateUser() {
        // TODO: Authorization
        $validator = Validator::getInstance();
        $data = $validator->ValidateAllByMask($_POST,'updateMask');
        if (!$data)
            return UPDATE_VALIDATION_ERROR['text'];
        $data['roles'] = $_POST['roles'];
        $id = $_POST['user_id'];
        unset($data['user_id']);
        if (User::update($id, $data))
            return UPDATE_SUCCESS['text'];
        else
            return UPDATE_ERROR['text'];
    }

}