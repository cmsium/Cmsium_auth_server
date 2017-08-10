<?php
class User {

    private static $instance;
    private static $result_debug = ['proceeded' => 0, 'added' => 0, 'failed' => 0, 'errors' => []];
    private static $data;
    private static $data_mask = ['username', 'password', 'email', 'phone', 'firstname', 'lastname', 'middlename', 'birth_date', 'birthplace'];
    public static $props_data = [];
    public static $blacklist = ['user_id'];
    /**
     * Get  Instance of Engine
     *
     * @return object Engine New instance or self
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    protected function __construct(){
    }
    private function __clone(){}


    public static function getPropsData($name){
            return self::$props_data;
    }

    public static function getPropsValue($name, $subname = false, $subsubname = false){
        if (isset(self::$props_data[$name])) {
            if ($subname) {
                if ($subsubname) {
                    return self::$props_data[$name][$subname][$subsubname];
                } else {
                    return self::$props_data[$name][$subname];
                }
            } else {
                return self::$props_data[$name];
            }
        }
    }

    public static function checkPropsValue($name,$value){
        if (isset(self::$props_data[$name]))
            return (self::$props_data[$name] == $value);
    }

    public static function checkSetPropsValue($name,$value){
        if (isset(self::$props_data[$name]))
            return (in_array($value, explode(',',self::$props_data[$name])));
    }

    /**
     * Removes specific user's row from the DB
     *
     * @param string|integer $id ID of the user you want to delete
     * @return bool True, if the query was done, else False
     */
    public static function destroy($id) {
        $conn = DBConnection::getInstance();
        $conn->StartTransaction();
        $query = "CALL destroyUser('$id');";
        if (!$conn->performQuery($query)){
            $conn->rollback();
            return false;
        }
        $roles = User::getRoles();
        foreach ($roles as $role) {
            if (!User::deleteProps($id, $role)) {
                $conn->rollback();
                return false;
            }
        }
        $conn->commit();
        return true;
    }

    /**
     * Adds new row with associated user to confirmation table
     *
     * @param string|integer $id ID of the user you want to delete
     */
    public static function confirm_destroy($id) {
        $conn = DBConnection::getInstance();
        $query = "CALL createDestroyConfirmation('$id');";
        $conn->performQuery($query);
    }

    /**
     * Checks if user is confirmed to destroy
     *
     * @param string|integer $id ID of the user you want to delete
     * @return array|bool True if confirmation exists, else False
     */
    public static function check_confirmation($id) {
        $conn = DBConnection::getInstance();
        $query = "CALL checkUserConfirmation('$id');";
        return $conn->performQueryFetch($query);
    }

    /**
     * Wrapper for the $_GET['id'] to use in XML docs
     *
     * @return string|bool ID of the user from _GET variable
     */
    public static function getUserId() {
        return $_GET['id'];
    }

    /**
     * Retrieves specific user's info as an array from the DB
     *
     * @param string|integer $id ID of the user you want to fetch
     * @return array|bool Associative array of user information, false if user wasn't found
     */
    public static function find($id, $string = false, $format = 'default') {
        $conn = DBConnection::getInstance();
        $query = "CALL getUser('$id');";
        $result = $conn->performQueryFetch($query);
        if ($string) {
            switch ($format) {
                case 'default':
                    return "{$result['username']} - {$result['lastname']} {$result['firstname']} {$result['middlename']}";
                    break;
                case 'document':
                    return "{$result['lastname']} {$result['firstname']} {$result['middlename']}";
                    break;
                default:
                    break;
            }
        } else {
            return $result;
        }
    }

    /**
     * Retrieves all the users's info from the DB
     *
     * @return array|bool Associative array of users's information, false if user wasn't found
     */
    public static function getAll($start=0,$limit=2147483647) {
        $conn = DBConnection::getInstance();
        return $conn->callProcedure('getAllUsers', [$start,$limit], 'fetch_all');
    }

    /**
     * Retrieves all the users's info from the DB
     *
     * @return array|bool Associative array of users's information, false if user wasn't found
     */
    public static function getAllWithFilters($data,$start=0,$limit=2147483647) {
        $conn = DBConnection::getInstance();
        $where = [];
        $order = "";
        if (!empty($data)) {
            foreach ($data as $filter => $value) {
                switch ($filter) {
                    case 'roles':
                        $having = [];
                        foreach ($value as $role) {
                            $constructor = RoleConstructor::getInstance(['role_id' => $role]);
                            $t_role_name = $constructor->getRoleData($role)['t_name'];
                            $having[] = "roles LIKE '%$t_role_name%'";
                        }
                        break;
                    case 'order':
                        $order = $value;
                        break;
                    default:
                        if ($value !== null)
                            $where[] = "props.$filter LIKE '%$value%'";
                        break;

                }
            }
        }
        if (!empty($order))
            $order_string = "ORDER BY props.$value";
        else
            $order_string = "";
        if (!empty($where))
            $where_query = " WHERE ".implode(' AND ',$where);
        else
            $where_query = "";
        if (!empty($having))
            $having_query = " HAVING ".implode(' AND ',$having);
        else
            $having_query = "";
        $query = "
    SELECT SQL_CALC_FOUND_ROWS bus.id_user, props.username, props.firstname, props.middlename, props.lastname,
    GROUP_CONCAT(DISTINCT r.t_name ORDER BY r.t_name ASC SEPARATOR ', ') AS roles
    FROM bus_tickets AS bus
      LEFT JOIN user_properties AS props ON bus.id_user = props.user_id
      LEFT JOIN roles_in_users AS riu ON bus.id_user = riu.user_id
      LEFT JOIN roles AS r ON riu.role_id = r.id
      $where_query
    GROUP BY bus.id_user $having_query $order_string LIMIT $start,$limit;";
        return $conn->performQueryFetchAll($query);
    }


    /**
     * Set user data information in data array of user object
     *
     * @param string $id Requested user id
     */
    public static function setData($id) {
        $conn = DBConnection::getInstance();
        $ref_handler = ReferenceHandler::getInstance();
        $query = "CALL setUserData('$id');";
        $data = $conn->performQueryFetch($query);
        $data['user_id'] = $id;
        $props = ['address_object' => ['object_props' => [], 'method_props' => ['643',$data['birthplace']]]];
        $instance = $ref_handler->build("user_properties", 'birthplace', 'read', $props);
        if ($instance) {
            $data['birthplace'] = $instance->getData();
        }
        $data['birthplace'] = [
            'value' => implode(', ',$data['birthplace']),
            'mask' => implode(',',array_keys($data['birthplace']))
        ];
        self::$data = $data;
    }


    /**
     * Get user id from user data
     * @return mixed
     */
    public static function getId() {
        return self::$data['user_id'];
    }

    public static function hasRole($role_id) {
        $conn = DBConnection::getInstance();
        $user_id = $_GET['id'];
        $query = "CALL checkUserRole($role_id, '$user_id');";
        return $conn->performQueryFetch($query)['role_id'];
    }

    /**
     * Get user data
     *
     * @return mixed User data array
     */
    public static function getInfo(){
        return self::$data;
    }

    /**
     * Detects file format depending on it's name
     *
     * @param $filename string Name of the file
     * @param $whitelist array List of allowed file types
     * @return bool|string Matched format, else false
     */
    public static function detectFileFormat($filename, $whitelist){
        $file_types = implode("|",$whitelist);
        $pattern = '/^.+\.('.$file_types.')$/';
        preg_match($pattern, $filename, $matches);
        return isset($matches[1]) ? $matches[1] : false;
    }

    /**
     * Checks if file contains unique watermark
     *
     * @param $object object PHPExcel file
     * @return bool True if the file is correct, else False
     */
    public static function checkWatermarkXSLX($object) {
        $value = $object->getActiveSheet()->getCell(WATERMARK_CELL)->getValue();
        if ($value == WATERMARK_HASH) {
            return true;
        } else {
            return false;
        }
    }

    public static function XLSXToArray($file) {
        require ROOTDIR.'/app/modules/users/helpers/PHPExcel.php';
        if ($objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name'])) {
            if (self::checkWatermarkXSLX($objPHPExcel)) {
                $data_mask = self::$data_mask;
                $sheetData = $objPHPExcel->getActiveSheet()->rangeToArray('B2:B11', null, true, false, false);
                $sheetData[7][0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheetData[7][0]));
                foreach ($sheetData as $key => $value) {
                    if ($key == 9) {
                        switch ($value[0]) {
                            case '1': $data['roles'] = ['1']; break;
                            case '2': $data['roles'] = ['3']; break;
                        }
                        break;
                    }
                    $data[$data_mask[$key]] = (string) $value[0];
                }
                return $data;
            } else {
                ErrorHandler::throwException(CREATE_ERROR);
            }
        } else {
            ErrorHandler::throwException(DATA_FORMAT_ERROR);
        }
    }

    public static function XLSToArray($file) {
        require ROOTDIR.'/app/modules/users/helpers/PHPExcel.php';
        if ($objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name'])) {
            $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, false, false);
            array_walk_recursive($sheetData, function (&$value) {$value = (string) $value;});
            $mask = array_shift($sheetData);
            foreach ($sheetData as $row) {
                foreach ($row as $index=>$value) {
                    $resulted_row[$mask[$index]] = $value;
                }
                $result[] = $resulted_row;
            }
            return $result;
        } else {
            ErrorHandler::throwException(DATA_FORMAT_ERROR);
        }
    }

    /**
     * Imports data from CSV file to the db
     *
     * @param $file array Associative array containing data of the uploaded file
     * @return bool True, if runs without exceptions
     */
    public static function importFromCSV($file){
        $converter = DataConverter::getInstance();
        $file_contents = file_get_contents($file['tmp_name']);
        $csv_array = $converter->CSVToArray($file_contents, $delimiter = ";");
        $validator = Validator::getInstance();
        foreach ($csv_array as $row) {
            self::$result_debug['proceeded'] += 1;
            $data = $validator->ValidateByMask($row, 'registFromFileMask');
            if ($data) {
                $data['roles'] = explode(', ', $row['roles']);
                if (self::create($data)) {
                    self::$result_debug['added'] += 1;
                } else {
                    self::$result_debug['failed'] += 1;
                    self::$result_debug['errors'][] = self::$result_debug['proceeded'].'::'.CREATE_ERROR['text'];
                }
            } else {
                self::$result_debug['failed'] += 1;
                self::$result_debug['errors'][] = self::$result_debug['proceeded'].'::'.DATA_FORMAT_ERROR['text'];
            }
        }
        return true;
    }

    /**
     * Formats proper HTML output for file upload debug
     *
     * @return string Proper HTML
     */
    public static function formatDebugOutput() {
        $proceeded = self::$result_debug['proceeded'];
        $added = self::$result_debug['added'];
        $failed = self::$result_debug['failed'];
        $errors = self::$result_debug['errors'];
        $result = "Обработано: $proceeded <br />
                   Добавлено: $added <br />
                   Ошибок: $failed <br /><br />";
        foreach ($errors as $error) {
            $result = $result.$error."<br />";
        }
        return $result;
    }

    public static function importFromXLSX($file) {
        $validator = Validator::getInstance();
        $raw_data = self::XLSXToArray($file);
        $data = $validator->ValidateByMask($raw_data, 'registFromFileMask');
        if ($data) {
            $data['roles'] = $raw_data['roles'];
            self::$result_debug['proceeded'] += 1;
            if (self::create($data)) {
                self::$result_debug['added'] += 1;
            } else {
                self::$result_debug['failed'] += 1;
                self::$result_debug['errors'][] = self::$result_debug['proceeded'].'::'.CREATE_ERROR['text'];
            }
        } else {
            self::$result_debug['failed'] += 1;
            self::$result_debug['errors'][] = self::$result_debug['proceeded'].'::'.DATA_FORMAT_ERROR['text'];
        }
        return true;
    }


    public static function importFromXLS($file) {
        $raw_data = self::XLSToArray($file);
        $validator = Validator::getInstance();
        foreach ($raw_data as $row) {
            self::$result_debug['proceeded'] += 1;
            $data = $validator->ValidateByMask($row, 'registFromFileMask');
            if ($data) {
                $data['roles'] = explode(', ', $row['roles']);
                if (self::create($data)) {
                    self::$result_debug['added'] += 1;
                } else {
                    self::$result_debug['failed'] += 1;
                    self::$result_debug['errors'][] = self::$result_debug['proceeded'].'::'.CREATE_ERROR['text'];
                }
            } else {
                self::$result_debug['failed'] += 1;
                self::$result_debug['errors'][] = self::$result_debug['proceeded'].'::'.DATA_FORMAT_ERROR['text'];
            }
        }
        return true;
    }

    /**
     * Calls import methods for certain file formats
     *
     * @param mixed $file File from post
     * @param string $fileformat Format of the file
     */
    public static function importFromFile($file, $fileformat){
        switch ($fileformat) {
            case 'csv':
                self::importFromCSV($file);
                break;
            case 'xls':
                self::importFromXLS($file);
                break;
            case 'xlsx':
                self::importFromXLSX($file);
                break;
        }
    }

    /**
     * Get current value of user data
     *
     * @param string $name Current value name
     * @return mixed Current value
     */
    public static function getValue($name, $surname = false){
            $user_data = User::getInfo();
            if (isset($user_data[$name])) {
                if ($surname) {
                    return $user_data[$name][$surname];
                } else {
                    return $user_data[$name];
                }
            }
    }


    /**
     * Get current value of user data from post
     *
     * @param string $name Current value name
     * @return mixed Current value
     */
    public static function getPOSTValue($name, $surname = false){
        if (isset($_POST[$name])) {
            if ($surname) {
                return $_POST[$name][$surname];
            } else {
                return $_POST[$name];
            }
        }
    }


    /**
     * Get current value of user data from get
     *
     * @param string $name Current value name
     * @return mixed Current value
     */
    public static function getGetValue($name){
        if (isset($_POST[$name]))
            return $_POST[$name];
    }


    /**
     * Получить список ролей пользователя
     * @param bool $both Вернуть все параметры ролей(id, название роли, название на русском )
     * @return array Массив ролей
     */
    public static function getRoles($both = false){
        if ($both){
            $roles = explode(', ',self::$data['roles']);
            $t_roles = explode(', ',self::$data['t_roles']);
            $roles_id = explode(', ',self::$data['roles_id']);
            $result=[];
            foreach ($roles as $key => $role){
                $result[]=['role_id'=>$roles_id[$key],'role'=>$role,'t_role'=>$t_roles[$key]];
            }
            return $result;
        }
        else
            return explode(', ',self::$data['roles']);
    }

    /**
     * Check current role existance in user roles
     *
     * @param string $role_id Current role id
     * @return bool Existance
     */
    public static function checkRole($role_id){
        if (in_array($role_id,explode(',',self::$data['roles_id'])))
            return true;
        return false;
    }

    /**
     * Check current role existance in POST
     *
     * @param string $role_id Current role id
     * @return bool Existance
     */
    public static function checkRoleFromPost($role_id){
        if (isset($_POST['roles']) && in_array($role_id,$_POST['roles']))
            return true;
        return false;
    }


    /**
     * Get all allowed roles
     * @return mixed Roles array|false
     */
    public static function getAllRoles() {
        $conn = DBConnection::getInstance();
        $query = "CALL getRoles();";
        $result = $conn->performQueryFetchAll($query);
        if ($result) {
            self::setPropsData($result);
        }
        return $result;
    }


    /**
     * Проверяет полученный id пользователя на идентичность
     * id пользователя сессии
     * @param string $req_id Запрашиваемый id пользователя
     * @return bool true|false
     */
    public static function checkSelfSession($req_id){
        $user_id = User::getSessionUserId();
        if ($req_id == $user_id)
            return true;
        return false;
    }

    /**
     * Update user information
     *
     * @param string $id Requested user id
     * @param array $data User data to be updated
     * @return bool (true|false)Update status
     */
    public static function update($id, $data) {
        if (!$id and !$data)
            return false;
        if (self::identifyUser($id)) {
            $conn = DBConnection::getInstance();
            $conn->startTransaction();
            foreach ($data as $key => $value) {
                $table_name = self::getTableName($key);
                if ($table_name == 'user_properties') {
                    $user_props[$key] = $value;
                } elseif ($table_name) {
                    $query = "UPDATE $table_name SET $key = '$value' WHERE user_id = '$id';";
                    if (!$conn->performQuery($query)) {
                        return false;
                    }
                }
            }
            if (!empty($user_props)) {
                foreach ($user_props as $key => $value) {
                    $result[] = "$key = '$value'";
                }
            }
            $delete_roles_query = "DELETE FROM roles_in_users WHERE user_id = '$id';";
            if (!$conn->performQuery($delete_roles_query)) {
                $conn->rollback();
                return false;
            }
            $query_roles = "";

            foreach ($data['roles'] as $value) {
                $query = "INSERT INTO roles_in_users(role_id, user_id) VALUES ($value, '$id');";
                $query_roles = $query_roles . $query;
            }
            if (!$conn->performMultiQuery($query_roles)) {
                $conn->rollback();
                return false;
            }
            $query_user_props = "UPDATE user_properties SET " . implode(",", $result) . " WHERE user_id = '$id';";
            if (!$conn->performQuery($query_user_props)) {
                $conn->rollback();
                return false;
            }
            $conn->commit();
            return true;
        } else
            return false;
    }


    /**
     * Обновить дополнительную информацию
     * @param string $id id чуловека
     * @param array $data Данные о чуловеке
     * @return bool Статус обновления
     */
    public static function updateProps($id, $role, $data){
        self::setData($id);
        if (self::identifyUser($id) and UserAuth::checkRole($role)) {
            $table_name =  self::getPropsTableName($role);
            $props_data = self::readProps($id,$table_name);
            $conn = DBConnection::getInstance();
            if (!$props_data){
                $query_array =  self::buildInsertQuery($table_name,$id,$data);
                $result = $conn->performPreparedQuery($query_array['DictionaryQuery'], $query_array['params']);
                return $result ? true : false;
            } else {
                $query_array = self::buildUpdateQuery($table_name, $id, $data);
                $result = $conn->performPreparedQuery($query_array['DictionaryQuery'], $query_array['params']);
                return $result ? true : false;
            }
        } else {
            return false;
        }
    }

    public function updateFile($user_id,$file_column_name,$role){
        if (empty($_FILES[$file_column_name]['name']))
            return NULL;
        $table_name =  self::getPropsTableName($role);
        $data = self::readProps($user_id,$table_name);
        $old_id = $data[$file_column_name];
        $file_actions = FileActions::getInstance();
        return $file_actions->update($old_id,$file_column_name);
    }

    /**
     * Returns valid query for writing event props to database
     *
     * @param $table_name string Name of the props table
     * @param $user_data array Validated data from POST
     * @return array
     */
    protected static function buildInsertQuery($table_name,$user_id, array $user_data) {
        foreach ($user_data as $key => $value) {
            $props_columns[] = $key;
            $props_values[] = $value;
        }
        $query = "INSERT INTO $table_name(" . implode(", ", $props_columns) . ", user_id) 
                  VALUES(" . str_repeat('?, ', count($user_data)) . "'{$user_id}');";
        return ['DictionaryQuery' => $query, 'params' => $props_values];
    }


    /**
     * Returns valid query for updating event props in database
     *
     * @param $user_data array Validated data from POST
     * @return string
     */
    protected static function buildUpdateQuery($table_name,$user_id,array $user_data) {
        foreach ($user_data as $key => $value){
            $query_array[] = "$key = ?";
            $query_array_values[] = $value;
        }
        $query = "UPDATE $table_name SET " . implode(", ", $query_array) . " WHERE user_id = '{$user_id}';";
        return ['DictionaryQuery' => $query, 'params' => $query_array_values];
    }

    /**
     * Check current props existence in DB and return data
     * @param string $event_id Id of current event
     * @return mixed Event data
     */
    public static function readProps($user_id,$table_name){
        $conn = DBConnection::getInstance();
        $query = "CALL readProps('{$table_name}','{$user_id}');";
        $result = $conn->performQueryFetch($query);
        if ($result) {
            self::setPropsData($result);
        }
        return $result;
    }

    /**
     * Set data array to user properties
     * @param array $data Current properties data array
     */
    public static function setPropsData ($data){
        self::$props_data = array_merge(self::$props_data,$data);
    }

    /**
     * Delete props
     * @param string $event_id Id of current event
     * @return bool Event delete status
     */
    public static function deleteProps($user_id,$role){
        $conn = DBConnection::getInstance();
        $table_name =  self::getPropsTableName($role);
        $query = "CALL deleteProps('{$table_name}','{$user_id}');";
        $result = $conn->performQuery($query);
        return $result ? true : false;
    }

    public static function getPropsTableName($role){
        return $role."_properties";
    }

    /**
     * Get user properties data (except data in blacklist)
     * @return array Properties data array
     */
    public static function getProps(){
        $result = [];
        foreach (self::$props_data as $key => $prop){
            if (!in_array($key,RoleConstructor::$blacklist))
                $result[$key] = $prop;
        }
        return $result;
    }

    /**
     * Updates user's password
     *
     * @param $id string User's id from DB
     * @param $data array Data from POST containing password
     * @return bool
     */
    public static function updatePassword($id, $data) {
        if (!$id and !$data)
            return false;
        if (self::identifyUser($id)) {
            $hashed_password = self::generatePassword($data['password']);
            $conn = DBConnection::getInstance();
            $query = "UPDATE bus_tickets SET ticket = '$hashed_password' WHERE id_user = '$id';";
            return $conn->performQuery($query);
        } else
            return false;
    }

    /**
     * Transforms an associative array to an XML document
     *
     * @param array $user_data User's info
     * @return string|bool XML document, else false
     */
    public static function toXML(array $user_data, $keys = true) {
        $converter = DataConverter::getInstance();
        $result = $converter->arrayToXML($user_data, 'users', $keys);
        if (!$result) {
            ErrorHandler::throwException(ARRAY_TO_XML_CONVERT_ERROR);
        }
        return $result;
    }

    /**
     * Функция генерирует хеш пароля с помощью соли (хеш строки, указанной в defaults.php)
     *
     * @param string $raw_password Сырой пароль, принятый с формы
     * @return string Хэш пароля для хранения в БД
     */
    public static function generatePassword($raw_password) {
        $salt = md5(PASSGEN_KEYWORD);
        return md5($raw_password.$salt);
    }

    /**
     * Generates hash from user data in order to become "id_user"
     *
     * @param array $params_array User data as associative array
     * @return string MD5 hash of user data
     */
    public static function generateIdHash($params_array) {
        $birth_date = $params_array['birth_date'];
        $birthplace = strtoupper($params_array['birthplace']);
        $firstname = strtoupper($params_array['firstname']);
        $middlename = strtoupper($params_array['middlename']);
        $lastname = strtoupper($params_array['lastname']);
        $concat_data = $firstname.$middlename.$lastname.$birthplace.$birth_date;
        return md5($concat_data);
    }

    /**
     * Функция проверяет наличие пользователя в БД и верность его параметров
     *
     * @param string $type Тип идентификатора, может быть username, email, phone
     * @param string $identifier Идентификатор, по которому происходит поиск пользователя в БД (имя пользователя, телефон и т.д.)
     * @param string $raw_password Пароль, введеный пользователем в форму
     * @return bool Наличие верного пользователя в БД, true при успешном нахождении, иначе false
     */
    public static function checkPresence($type, $identifier, $raw_password) {
        // Checks if user exists in the database. Returns boolean
        $conn = DBConnection::getInstance();
        $table_name = self::getTableName($type);
        $hashed_password = self::generatePassword($raw_password);
        $query = "SELECT b.id_user, b.ticket FROM bus_tickets AS b
              INNER JOIN $table_name AS e ON b.id_user = e.user_id
              WHERE e.$type = '$identifier' AND b.ticket = '$hashed_password';";
        $result = $conn->performQueryFetch($query);
        return $result ? $result : false;
    }

    /**
     * Функция однозначно идентифицирует пользователя на основе данных ввода
     *
     * @param array $hashed_id ID-хэш пользователя
     * @return bool True при идентификации пользователя, иначе false
     */
    public static function identifyUser($hashed_id) {
        $conn = DBConnection::getInstance();
        $query = "SELECT id_user FROM bus_tickets WHERE id_user = '$hashed_id';";
        $result = $conn->performQueryFetch($query);
        return $result ? true : false;
    }

    /**
     * Функция выполняет запись пользователя в БД из именованного массива с его параметрами
     *
     * @param array $params_array Массив параметров пользователя для записи
     * @return bool True при успешной записи пользователя, иначе false
     */
    public static function create($params_array) {
        $conn = DBConnection::getInstance();
        $user_id = self::generateIdHash($params_array);
        if (!self::identifyUser($user_id)) {
            // Connect to db, hash password and then write to db
            $conn->startTransaction();
            $hashed_password = self::generatePassword($params_array['password']);
            $user_id = self::generateIdHash($params_array);
            $query_bus_ticket = "INSERT INTO bus_tickets(id_user, ticket) VALUES ('$user_id','$hashed_password');";
            $conn->performQuery($query_bus_ticket);
            // Iterating through params hash
            foreach ($params_array as $key => $value) {
                $table_name = self::getTableName($key);
                if ($table_name == 'user_properties') {
                    $user_props_columns[] = $key;
                    $user_props_values[] = "'$value'";
                } elseif ($table_name) {
                    $query = "INSERT INTO $table_name($key, user_id) VALUES ('$value','$user_id');";
                    if (!$conn->performQuery($query)) {
                        $conn->rollback();
                        return false;
                    }
                }
            }
            foreach ($params_array['roles'] as $value) {
                $query = "INSERT INTO roles_in_users(role_id, user_id) VALUES ($value, '$user_id');";
                if (!$conn->performQuery($query)) {
                    $conn->rollback();
                    return false;
                }
            }
            $query_user_props = "INSERT INTO user_properties(" . implode(", ", $user_props_columns) . ", user_id) 
                             VALUES(" . implode(", ", $user_props_values) . ",'$user_id');";
            if (!$conn->performQuery($query_user_props)) {
                $conn->rollback();
                return false;
            }
            $conn->commit();
            return true;
        } else {
            return false;
        }
    }

    public static function getUserRoles($user_id) {
        $conn = DBConnection::getInstance();
        $query = "CALL getUserRoles('$user_id');";
        $result = $conn->performQueryFetchAll($query);
        return $result ?: false;
    }

    /**
     * Функция возвращает имя таблицы авторизации на основе передаваемого типа авторизации
     *
     * @param string $type Тип авторизации, может быть phone, username, email
     * @return bool|string Имя таблицы, иначе false
     */
    public static function getTableName($type) {
        switch ($type) {
            case 'phone': return 'phones'; break;
            case 'firstname':
            case 'middlename':
            case 'lastname':
            case 'birth_date':
            case 'birthplace':
            case 'username': return 'user_properties'; break;
            case 'email': return 'emails'; break;
            case 'contract_id': return 'student_properties'; break;
            case 'position': return 'staff_properties'; break;

            default: return false;
        }
    }


    /**
     * Gets current user's id
     *
     * @return string Id of the user
     */
    public static function getSessionUserId() {
        Session::getInstance();
        $user_id = Cookie::getUserId();
        return $user_id;
    }

}