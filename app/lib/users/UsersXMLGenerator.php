<?php
class UsersXMLGenerator {

    private static $instance;

    /**
     * Get  Instance of PortfolioRenderer
     *
     * @return object PortfolioRenderer New instance or self
     */
    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct(){}
    private function __clone(){}


    public static function toXML(array $data, $root,$keys = true) {
        $converter = DataConverter::getInstance();
        $result = $converter->arrayToXML($data, $root, $keys);
        if (!$result) {
            ErrorHandler::throwException(ARRAY_TO_XML_CONVERT_ERROR);
        }
        return $result;
    }

    /**
     * Render event show info block
     *
     * @param array $data Data to be shown
     * @return mixed Rendered block
     */
    public static function buildShowTable(array $data) {
        $engine = Engine::getInstance();
        $file_action = FileActions::getInstance();
        foreach ($data as $key => $value){
            if (strpos($key,'scan_id')){
                if (!empty($value))
                    $data[$key] = $file_action->get($value,true);
            }
        }
        $xml = self::toXML($data, false);
        $result_string = $engine->xmlStrTransform($xml, 'portfolio/xsl/show_view_builder.xsl');
        return $result_string;
    }


    /**
     * Render dynamically generated event creation form
     *
     * @param string $type_name Current event type
     * @return mixed Rendered form
     */
    public static function buildCreateForm($type_name) {
        $schema = EventSchema::$data_model[$type_name];
        unset($schema[0], $schema[1], $schema[2]);
        $engine = Engine::getInstance();
        $new_schema = self::schemaToFormArray($schema);
        $function = function($column){
            if (strpos($column['column_name'],'scan_id')){
                $column['column_type']['name'] = 'file';
            }
            return $column;
        };
        $new_schema = array_map($function,$new_schema);
        $new_schema['type_name'] = $type_name;
        $xml = Event::toXML($new_schema, false);
        $result_string = $engine->xmlStrTransform($xml, 'portfolio/xsl/form_builder.xsl');
        return $result_string;
    }


    public static function buildCreateFormFromPOST($type_name) {
        $schema = EventSchema::$data_model[$type_name];
        unset($schema[0], $schema[1], $schema[2]);
        $engine = Engine::getInstance();
        $new_schema = self::schemaToFormArray($schema);
        $function = function($column){
            if (strpos($column['column_name'],'scan_id')){
                $column['column_type']['name'] = 'file';
            }
            return $column;
        };
        $new_schema = array_map($function,$new_schema);
        $new_schema['type_name'] = $type_name;
        $xml = Event::toXML($new_schema, false);
        $result_string = $engine->xmlStrTransform($xml, 'portfolio/xsl/post_form_builder.xsl');
        return $result_string;
    }





    /**
     * Render dynamically generated user props update form
     *
     * @param string $type_name Current event type
     * @param string $event)id Current event id
     * @return mixed Rendered form
     */
    public static function buildUpdateForm($role, $id) {
        $user_data = User::readProps($id, User::getPropsTableName($role));
        $ref_handler = ReferenceHandler::getInstance();
        $country_iso = '';
        $address_column_name = '';
        $full_address_column_name = '';
        if ($user_data){
            foreach ($user_data as $column_name => $value) {
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_country')
                    $country_iso = $value;
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_object')
                    $address_column_name = $column_name;
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'full_address_object')
                    $full_address_column_name = $column_name;
                $props = [
                    'address_object' => ['object_props' => [], 'method_props' => [$country_iso,$value]],
                    'full_address_object' => ['object_props' => [], 'method_props' => [$country_iso,$value]],
                ];
                $instance = $ref_handler->build($role."_properties", $column_name, 'read', $props);
                if ($instance) {
                    $user_data[$column_name] = $instance->getData();
                }
            }
            if ($address_column_name !== '') {
                $user_data[$address_column_name] = [
                    'value' => implode(', ',$user_data[$address_column_name]),
                    'mask' => implode(',',array_keys($user_data[$address_column_name]))
                ];
            }
            if ($full_address_column_name !== '') {
                $user_data[$full_address_column_name] = [
                    'mask' => implode(',',array_keys($user_data[$full_address_column_name])),
                    'value' => [
                        'city' => implode(', ',array_slice($user_data[$full_address_column_name], 0, -2)),
                        'user' => end($user_data[$full_address_column_name]),
                        'street' => prev($user_data[$full_address_column_name]),
                    ]
                ];
            }
        }
        User::$props_data = $user_data;
        $engine = Engine::getInstance();
        $document = simplexml_load_file(ROOTDIR."/app/modules/users/xml/generated_roles_schemas/$role.xml");
        $document->addChild('user_id', $id);
        $xml = $document->asXML();
        $result_string = $engine->xmlStrTransform($xml, 'users/xsl/builders/update_form_builder.xsl');
        return $result_string;
    }


    /**
     * Render dynamically generated self props update form
     *
     * @param string $type_name Current event type
     * @param string $event)id Current event id
     * @return mixed Rendered form
     */
    public static function buildSelfUpdateForm($role, $id) {
        $user_data = User::readProps($id, User::getPropsTableName($role));
        $ref_handler = ReferenceHandler::getInstance();
        $country_iso = '';
        $address_column_name = '';
        $full_address_column_name = '';
        if ($user_data){
            foreach ($user_data as $column_name => $value) {
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_country')
                    $country_iso = $value;
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'address_object')
                    $address_column_name = $column_name;
                if (ReferenceHandler::getRefModule($role."_properties", $column_name) == 'full_address_object')
                    $full_address_column_name = $column_name;
                $props = [
                    'address_object' => ['object_props' => [], 'method_props' => [$country_iso,$value]],
                    'full_address_object' => ['object_props' => [], 'method_props' => [$country_iso,$value]],
                ];
                $instance = $ref_handler->build($role."_properties", $column_name, 'read', $props);
                if ($instance) {
                    $user_data[$column_name] = $instance->getData();
                }
            }
            if ($address_column_name !== '') {
                $user_data[$address_column_name] = [
                    'value' => implode(', ',$user_data[$address_column_name]),
                    'mask' => implode(',',array_keys($user_data[$address_column_name]))
                ];
            }
            if ($full_address_column_name !== '') {
                $user_data[$full_address_column_name] = [
                    'mask' => implode(',',array_keys($user_data[$full_address_column_name])),
                    'value' => [
                        'city' => implode(', ',array_slice($user_data[$full_address_column_name], 0, -2)),
                        'user' => end($user_data[$full_address_column_name]),
                        'street' => prev($user_data[$full_address_column_name]),
                    ]
                ];
            }
        }
        User::$props_data = $user_data;
        $engine = Engine::getInstance();
        $document = simplexml_load_file(ROOTDIR."/app/modules/users/xml/generated_roles_schemas/$role.xml");
        $document->addChild('user_id', $id);
        $xml = $document->asXML();
        $result_string = $engine->xmlStrTransform($xml, 'users/xsl/builders/self_update_form_builder.xsl');
        return $result_string;
    }



    /**
     * Render dynamically generated self props update form without event data
     *
     * @param string $role Role name
     * @return mixed Rendered form
     */
    public static function buildUpdateFormWithoutData($role, $action) {
        $engine = Engine::getInstance();
        $xml = "users/xml/generated_roles_schemas/$role.xml";
        if ($action == 'self') {
            $path = 'users/xsl/builders/self_post_form_builder.xsl';
        } else {
            $path = 'users/xsl/builders/post_form_builder.xsl';
        }
        $result_string = $engine->xmlFileTransform($xml, $path);
        return $result_string;
    }


    /**
     * Return html form type according to given sql type
     *
     * @param $sql_type
     * @return mixed
     */
    public static function schemaToFormArray($schema){
        $final_array = [];
        foreach ($schema as $value){
            $column_type_data = self::getColumnData($value['column_type']);
            $final_array[] = [
            'column_name' => $value['column_name'],
            'column_type' => ['name'=>$column_type_data['data_type'],
                              'props'=>$column_type_data['props']]
            ];
        }
        return $final_array;
    }

    public static function getColumnData ($column_data){
        $column_array = explode('(',$column_data);
        $data_type = $column_array[0];
        if (isset($column_array[1])) {
            $data_props = str_replace(array("'",'"',")"),'',$column_array[1]);
            if (is_numeric($data_props))
                $props = (INT)$data_props;
            else {
                $props = explode(',',$data_props);
            }
        }
        else
            $props = NULL;
        return ['data_type'=>$data_type, 'props'=>$props];
    }

    /**
     * Get current value of user data from post
     *
     * @param string $name Current value name
     * @return mixed Current value
     */
    public static function getPOSTValue($name, $subname = false){
        if (isset($_POST[$name])) {
            if ($subname) {
                return $_POST[$name][$subname];
            } else {
                return $_POST[$name];
            }
        }
    }

    public static function checkPOSTValue($name,$value){
        return ($_POST[$name] == $value);
    }


    public static function checkSetPOSTValue($name,$value){
        if (isset($_POST[$name]))
            return (in_array($value, $_POST[$name]));
    }

    /**
     * Get current value of user data from get
     *
     * @param string $name Current value name
     * @return mixed Current value
     */
    public static function getGetValue($name){
        if (isset($_GET[$name]))
            return $_GET[$name];
    }

    public static function getAllRolesNodeSet(){
        $xml = "<roles>";
        foreach (User::getAllRoles() as $role) {
            $xml .= "<role><role_id>{$role['id']}</role_id><role_name>{$role['t_name']}</role_name></role>";
        }
        $xml .= "</roles>";
        $doc = new DOMDocument;
        $doc->loadXml($xml);
        return $doc;
    }


    public static function getRolesNodeSet(){
        $xml = "<roles>";
        foreach (User::getRoles() as $role) {
            $xml .= "<role>$role</role>";
        }
        $xml .= "</roles>";
        $doc = new DOMDocument;
        $doc->loadXml($xml);
        return $doc;
    }

    public static function getTypesNodeSet(){
        $xml = "<types>";
        foreach (EVENT_ALLOWED_TYPES as $type) {
            $xml .= "<type>$type</type>";
        }
        $xml .= "</types>";
        $doc = new DOMDocument;
        $doc->loadXml($xml);
        return $doc;
    }

    public static function getCountriesNodeSet($column_name = false){
        $conn = DBConnection::getInstance();
        $query = "CALL getAllCountries()";
        $result = $conn->performQueryFetchAll($query);
        $xml = "<countries>";
        foreach ($result as $country) {
            if ($column_name) {
                $xml .= "<country>
                            <country_column_name>$column_name</country_column_name>
                            <name>{$country['t_name']}</name>
                            <iso>{$country['iso']}</iso>
                        </country>";
            } else {
                $xml .= "<country>
                            <name>{$country['t_name']}</name>
                            <iso>{$country['iso']}</iso>
                        </country>";
            }
        }
        $xml .= "</countries>";
        $doc = new DOMDocument;
        $doc->loadXml($xml);
        return $doc;
    }

    public static function getUsersNodeSet($column_name = false) {
        $conn = DBConnection::getInstance();
        $query = "CALL getAllUsers(0,2147483647);";
        $result = $conn->performQueryFetchAll($query);
        $xml = "<users>";
        foreach ($result as $user) {
            if ($column_name) {
                $xml .= "<user>
                            <user_column_name>$column_name</user_column_name>
                            <id>{$user['id_user']}</id>
                            <username>{$user['username']}</username>
                            <full_name>{$user['lastname']} {$user['firstname']} {$user['middlename']}</full_name>
                        </user>";
            } else {
                $xml .= "<user>
                            <id>{$user['id_user']}</id>
                            <username>{$user['username']}</username>
                            <full_name>{$user['lastname']} {$user['firstname']} {$user['middlename']}</full_name>
                        </user>";
            }
        }
        $xml .= "</users>";
        $doc = new DOMDocument;
        $doc->loadXml($xml);
        return $doc;
    }

}