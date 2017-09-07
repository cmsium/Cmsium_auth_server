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

    // Basic auth methods

    /**
     * Renders an auth form
     *
     * @return string HTML page
     */
    function loginForm() {
        $uri = false;
        if (isset($_GET['redirect_uri'])) {
            $validator = Validator::getInstance();
            $uri = $validator->Check('URL',$_GET['redirect_uri'],[]);
        }
        include ROOTDIR.'/app/views/login_form.html.php';
    }

    /**
     * Login user using data from POST
     *
     * @return string Plain HTML message
     */
    function loginUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $auth = UserAuth::getInstance();
            if (isset($_POST['redirect_uri'])) {
                $validator = Validator::getInstance();
                $uri = $validator->Check('URL',$_POST['redirect_uri'],[]);
                if ($uri) {
                    unset($_POST['redirect_uri']);
                } else {
                    return DATA_FORMAT_ERROR['text'];
                }
            }
            if ($auth->login()) {
                if (isset($uri)) {
                    $headers = HeadersController::getInstance();
                    $headers->respondLocation(['value' => $uri]);
                } else {
                    return 'Пользователь успешно авторизован!';
                }
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

    /**
     * POST: [user_token, service_name, action]
     *
     * @return string
     */
    function checkPermission() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $data = $validator->ValidateAllByMask($_POST, 'checkPermissionsValidation');
            if (!$data) {
                return json_encode(['status' => 'error', 'message' => DATA_FORMAT_ERROR['text']],JSON_UNESCAPED_UNICODE);
            }
            $auth = UserAuth::getInstance();
            if ($user_id = $auth->check($data['token'])) {
                // Check permissions
                if ($auth->checkActionPermissions($data['action'][0], $data['service_name'], $user_id)) {
                    return json_encode(['status' => 'ok']);
                } else {
                    return json_encode(['status' => 'error', 'message' => PERMISSIONS_ERROR['text']],JSON_UNESCAPED_UNICODE);
                }
            } else {
                return json_encode(['status' => 'error', 'message' => AUTH_ERROR['text']],JSON_UNESCAPED_UNICODE);
            }
        } else {
            return json_encode(['status' => 'error', 'message' => POST_DATA_ABSENT['text']],JSON_UNESCAPED_UNICODE);
        }
    }

    function checkMailer() {
        require ROOTDIR.'/vendor/autoload.php';
        echo 'Hello';
    }

    // User control web interface

    function allUsers() {
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
        $roles = User::getAllRoles();
        include ROOTDIR.'/app/views/create_user_form.html.php';
    }

    function updateMenu() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET' and isset($_GET['id'])){
            $id = $_GET['id'];
            User::setData($id);
            $roles = User::getRoles(true);
            $roles[] = ['role'=>'user_properties'];
            include ROOTDIR.'/app/views/user_update_menu.html.php';
        }
    }

    function updateForm() {
        if (isset($_GET['id'])) {
            User::setData($_GET['id']);
            $user_data = User::getInfo();
            include ROOTDIR.'/app/views/update_user_form.html.php';
        } else {
            return USER_PARAM_ABSENT['text'];
        }
    }

    function updateUser() {
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

    function updatePropsForm() {
        if (isset($_GET['role']) && isset($_GET['id'])){
            $validator = Validator::getInstance();
            $role = $validator->Check('CirrLatName',$_GET['role'],['min' => 3, 'max' => 64]);
            if (!$role)
                return DATA_FORMAT_ERROR['text'];
            $id = $validator->Check('Md5Type',$_GET['id'],[]);
            if (!$id)
                return DATA_FORMAT_ERROR['text'];
            $form = UsersXMLGenerator::buildUpdateForm($role, $id);
            include ROOTDIR.'/app/views/update_prop_form.html.php';
        } else {
            return GET_DATA_ABSENT['text'];
        }
    }

    function updateUserProps() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $role = $validator->Check('CirrLatName',$_POST['role'],['min' => 3, 'max' => 64]);
            if (!$role) {
                return UNSUPPORTED_DATA_TYPE['text'];
            }
            $data = $_POST;
            unset ($data['role']);
            $ref_mask = ReferenceHandler::buildRefMask($role.'_properties');
            $constructor = RoleConstructor::getInstance(['role_name'=>$role]);
            $data = $validator->ValidateByDynamicMask($data,
                $constructor->getRoleSchema(), [], $ref_mask);
            if (!$data) {
                return UPDATE_PROPS_VALIDATION_ERROR['text'];
            }
            $id = $_POST['user_id'];
            unset($data['user_id']);
            $data = $data + $_FILES;
            $ref_handler = ReferenceHandler::getInstance();
            $country = [];
            foreach ($data as $column_name => $value) {
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_country')
                    $country['country'] = $value;
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_object') {
                    $value = array_combine(explode(',', $value['mask']), explode(', ', $value['value']));
                    if (!$value)
                        return ADDRESS_INPUT_ERROR['text'];
                    if (is_array($value)) $value = $value + $country;
                }
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'full_address_object') {
                    $value_array = explode(', ', $value['value']['city']);
                    $value_array[] = $value['value']['street'];
                    $value_array[] = $value['value']['user'];
                    $value = @array_combine(explode(',', $value['mask']), $value_array);
                    if (!$value)
                        return ADDRESS_INPUT_ERROR['text'];
                    if (is_array($value)) $value = $value + $country;
                }
                $props = ['userfiles' => ['object_props' => [], 'method_props' => [$id,$column_name,$role]],
                    'address_object' => ['object_props' => [$value], 'method_props' => []],
                    'full_address_object' => ['object_props' => [$value], 'method_props' => []],
                ];
                $instance = $ref_handler->build($role."_properties", $column_name, 'update', $props);
                if ($instance) {
                    $result = $instance->getData();
                    if ($result !== false)
                        $data[$column_name] = $result;
                    if ($result === NULL)
                        unset($data[$column_name]);
                }
            }
            if (User::updateProps($id, $role, $data))
                return UPDATE_SUCCESS['text'];
            else {
                return UPDATE_ERROR['text'];
            }
        }
        return NO_DATA['text'];
    }

    function newRoleForm() {
        include ROOTDIR.'/app/views/new_role_form.html.php';
    }

    function createRole() {
        if (isset($_GET['name']) && isset($_GET['model'])) {
            $validator = Validator::getInstance();
            $data_array = $validator->ValidateAllByMask($_GET,'createTypeMask');
            if (!$data_array)
                return CREATE_ROLE_VALIDATION_ERROR['text'];
            // Delete unwanted characters from foreign key and reference flags
            $data_array['model'] = array_map(function($i){
                if (isset($i[5]))
                    $i[5]=substr($i[5],12);
                if (isset($i[6]))
                    $i[6]=substr($i[6],10);
                return$i;
            },$data_array['model']);
            $table_name = RoleConstructor::transliterate($data_array['name']);
            $role_constructor = RoleConstructor::getInstance(['role_name'=>$table_name]);
            $role_constructor->createRole($data_array);
            return CREATE_ROLE_SUCCESS['text'];
        } else {
            return GET_DATA_ABSENT['text'];
        }
    }

    function deleteUserConfirm() {
        if ($_GET['id']) {
            if (self::$user_id === $_GET['id'])
                return SELF_DELETE['text'];
            $id = $_GET['id'];
            User::confirm_destroy($id);
            include ROOTDIR.'/app/views/user_delete_confirmation.html.php';
        } else {
            return USER_PARAM_ABSENT['text'];
        }
    }

    function deleteUser() {
        if (isset($_GET['id'])) {
            if (self::$user_id === $_GET['id'])
                return SELF_DELETE['text'];
            if (User::check_confirmation($_GET['id'])) {
                User::setData($_GET['id']);
                if (!User::destroy($_GET['id']))
                    return DELETE_ERROR['text'];
                return DELETE_CONFIRM['text'];
            } else {
                return EXECUTE_ERROR['text'];
            }
        } else {
            return USER_PARAM_ABSENT['text'];
        }
    }

    function deleteRoleForm() {
        include ROOTDIR.'/app/views/delete_role_form.html.php';
    }

    function deleteRole() {
        if (isset($_GET['role'])) {
            $validator = Validator::getInstance();
            $role_id = $validator->Check('StrNumbers',$_GET['role'],['min'=>1,'max'=>11]);
            if (!$role_id)
                return DATA_FORMAT_ERROR['text'];
            $type_constructor = RoleConstructor::getInstance(['role_id'=>$role_id]);
            if (!$type_constructor->destroyRole($role_id)) {
                return DELETE_ROLE_ERROR['text'];
            }
            return DELETE_ROLE_SUCCESS['text'];
        } else {
            return GET_DATA_ABSENT['text'];
        }
    }

    // Personal dashboard

    function dashboardMenu() {
        include ROOTDIR.'/app/views/user_dashboard_menu.html.php';
    }

    function showSelfUser() {
        $ref_handler = ReferenceHandler::getInstance();
        $id = Controller::$user_id;
        $user_data = User::find($id);
        $roles = explode(", ",$user_data['roles']);
        $converter = DataConverter::getInstance();
        $user_props = [];
        foreach ($roles as $role){
            $role_data = User::readProps($id,User::getPropsTableName($role));
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
        include ROOTDIR.'/app/views/show_self_user.html.php';
    }

    function updateSelfMenu() {
        $id = Controller::$user_id;
        User::setData($id);
        $roles = User::getRoles(true);
        $roles[] = ['role'=>'user_properties'];
        include ROOTDIR.'/app/views/user_self_update_menu.html.php';
    }

    function updateSelfUserForm() {
        $id = Controller::$user_id;
        User::setData($id);
        $user_data = User::getInfo();
        include ROOTDIR.'/app/views/update_self_user_form.html.php';
    }

    function updateSelfUser() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $data = $validator->ValidateAllByMask($_POST,'updateMask');
            if (!$data)
                return SELF_UPDATE_VALIDATION_ERROR['text'];
            $data['roles'] = $_POST['roles'];
            $id = Controller::$user_id;
            unset($data['user_id']);
            if (User::update($id, $data))
                return UPDATE_SUCCESS['text'];
            else
                return UPDATE_ERROR['text'];
        }
        return NO_DATA['text'];
    }

    function updateSelfUserPropsForm() {
        if (isset($_GET['role'])){
            $validator = Validator::getInstance();
            $role = $validator->Check('CirrLatName',$_GET['role'],['min' => 3, 'max' => 64]);
            if (!$role)
                return DATA_FORMAT_ERROR['text'];
            $id = Controller::$user_id;
            $form = UsersXMLGenerator::buildSelfUpdateForm($role, $id);
            include ROOTDIR.'/app/views/update_self_prop_form.html.php';
        } else {
            return GET_DATA_ABSENT['text'];
        }
    }

    function updateSelfUserProps() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $role = $validator->Check('CirrLatName',$_POST['role'],['min' => 3, 'max' => 64]);
            if (!$role) {
                return UNSUPPORTED_DATA_TYPE['text'];
            }
            $data = $_POST;
            unset ($data['role']);
            $ref_mask = ReferenceHandler::buildRefMask($role.'_properties');
            $constructor = RoleConstructor::getInstance(['role_name'=>$role]);
            $data = $validator->ValidateByDynamicMask($data,
                $constructor->getRoleSchema($role), ['user_id'], $ref_mask);
            if (!$data) {
                return SELF_PROPS_UPDATE_VALIDATION_ERROR['text'];
            }
            $id = Controller::$user_id;
            unset($data['user_id']);
            $data = $data + $_FILES;
            $ref_handler = ReferenceHandler::getInstance();
            $country = [];
            foreach ($data as $column_name => $value) {
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_country')
                    $country['country'] = $value;
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_object') {
                    $value = array_combine(explode(',', $value['mask']), explode(', ', $value['value']));
                    if (!$value)
                        return ADDRESS_INPUT_ERROR['text'];
                    if (is_array($value)) $value = $value + $country;
                }
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'full_address_object') {
                    $value_array = explode(', ', $value['value']['city']);
                    $value_array[] = $value['value']['street'];
                    $value_array[] = $value['value']['user'];
                    $value = @array_combine(explode(',', $value['mask']), $value_array);
                    if (!$value)
                        return ADDRESS_INPUT_ERROR['text'];
                    if (is_array($value)) $value = $value + $country;
                }
                $props = ['userfiles' => ['object_props' => [], 'method_props' => [$id,$column_name,$role]],
                    'address_object' => ['object_props' => [$value], 'method_props' => []],
                    'full_address_object' => ['object_props' => [$value], 'method_props' => []],
                ];
                $instance = $ref_handler->build($role."_properties", $column_name, 'update', $props);
                if ($instance) {
                    $result = $instance->getData();
                    if ($result !== false)
                        $data[$column_name] = $result;
                    if ($result === NULL)
                        unset($data[$column_name]);
                }
            }
            if (User::updateProps($id, $role, $data))
                return UPDATE_SUCCESS['text'];
            else {
                return UPDATE_ERROR['text'];
            }
        }
        return NO_DATA['text'];
    }

    function updatePasswordForm() {
        include ROOTDIR.'/app/views/user_password_form.html.php';
    }

    function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $validator = Validator::getInstance();
            $data = $validator->ValidateAllByMask($_POST, 'passwordMask');
            if (!$data)
                return PASSWORD_VALIDATION_ERROR['text'];
            if ($_POST['password'] != $_POST['password_repeat'])
                return PASSWORD_CHECK_ERROR['text'];
            if (User::updatePassword(Controller::$user_id, $data)) {
                return PASSWORD_UPDATE_SUCCESS['text'];
            } else {
                return PASSWORD_UPDATE_FAIL['text'];
            }
        }
        return POST_DATA_ABSENT['text'];
    }

    // Permissions



    // Transformers

    /**
     * Transform xml file to string using xsl
     *
     * @param string $xml_path Path to xml model
     * @param string $xsl_path Path to xml model
     * @return string Output
     */
    public static function xmlFileTransform($xml_path, $xsl_path){
        $xml = new DOMDocument;
        $xml->load("app/lib/".$xml_path);
        //if (!$xml->schemaValidate($xsd_path))
        //    return false;
        $xsl = new DOMDocument;
        $xsl->load("app/lib/".$xsl_path);

        $proc = new XSLTProcessor;

        $proc->registerPHPFunctions();
        $proc->importStyleSheet($xsl);

        return $proc->transformToXML($xml);

    }

    /**
     * Transform xml string to another string using xsl
     *
     * @param string $xml_str String that contains XML document
     * @param string $xsl_path Path to xml model
     * @return string Output
     */
    public static function xmlStrTransform($xml_str, $xsl_path){
        //if (!$xml->schemaValidate($xsd_path))
        //    return false;
        $xml = new DOMDocument;
        $xml->loadXML($xml_str);

        $xsl = new DOMDocument;
        $xsl->load("app/lib/".$xsl_path);

        $proc = new XSLTProcessor;

        $proc->registerPHPFunctions();
        $proc->importStyleSheet($xsl);

        return $proc->transformToXML($xml);
    }

}