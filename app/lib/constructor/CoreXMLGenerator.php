<?php
class CoreXMLGenerator {

    private static $instance;
    protected static $entity_data = [];
    protected static $schema_path_module='';
    protected static $schema_path='';
    protected static $entity_class='';
    protected static $view_builder='';
    protected static $form_builder='';
    protected static $admin_form_builder='';
    protected static $update_form_builder='';
    protected static $post_form_builder='';
    protected static $admin_post_form_builder='';
    protected static $post_update_form_builder='';
    protected static $entity_handler='';
    protected static $entity_prefix ='';


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


    /**
     * Render show info block
     *
     * @param array $data Data to be shown
     * @return mixed Rendered block
     */
    public static function buildShowTable(array $data) {
        $engine = Engine::getInstance();
        $converter = DataConverter::getInstance();
        $file = new File(ROOTDIR."/app/modules/".static::$schema_path_module."/xml/".static::$schema_path."/{$data['type_name']}.xml");
        $xml_array = $converter->XMLToArray($file->getContent());
        $result_xml_array = [
            'type_name' => $data['type_name'],
            static::$entity_prefix.'_id' => $data[static::$entity_prefix.'_id'],
        ];
        foreach ($xml_array['item'] as $column) {
            $arr = [
                'column_name' => $column['t_column_name'],
                'column_value' => $data[$column['column_name']],
                'column_type' => $column['column_type']['name']
            ];
            if (isset($column['column_type']['reference']))
                $arr['reference'] = $column['column_type']['reference'];
            $result_xml_array[] = $arr;
        }
        $class = static::$entity_class;
        $result_xml = $class::toXML($result_xml_array, false);
        $result_string = $engine->xmlStrTransform($result_xml, static::$schema_path_module.'/xsl/'.static::$view_builder);
        return $result_string;
    }


    public static function getTypeList($type) {
        $converter = DataConverter::getInstance();
        $file = new File(ROOTDIR."/app/modules/".static::$schema_path_module."/xml/".static::$schema_path."/$type.xml");
        $xml_array = $converter->XMLToArray($file->getContent());
        $result_xml_array = [];
        $constructor = TypeConstructor::getInstance($type);
        foreach ($xml_array['item'] as $column) {
            if (!array_key_exists($column['column_name'],$constructor->basic_columns)) {
                $result_xml_array[] = [
                    't_column_name' => $column['t_column_name'],
                    'column_name' => $column['column_name'],
                    'column_type' => $column['column_type']['name']
                ];
            }
        }
        return $result_xml_array;
    }

    /**
     * Render dynamically generated creation form
     *
     * @param string $type_name Current event type
     * @return mixed Rendered form
     */
    public static function buildCreateForm($type_name) {
        $engine = Engine::getInstance();
        $xml = static::$schema_path_module."/xml/".static::$schema_path."/$type_name.xml";
        $result_string = $engine->xmlFileTransform($xml,static::$schema_path_module.'/xsl/'.static::$form_builder);
        return $result_string;
    }

    /**
     * Render dynamically generated creation form for admin
     *
     * @param string $type_name Current event type
     * @return mixed Rendered form
     */
    public static function buildCreateFormAdmin($type_name) {
        $engine = Engine::getInstance();
        $xml = simplexml_load_file(ROOTDIR.'/app/modules/'.static::$schema_path_module."/xml/".static::$schema_path."/$type_name.xml");
        $users = $xml->addChild('item');
        $users->addChild('t_column_name','пользователь');
        $users->addChild('column_name','users');
        $users->addChild('column_type');
        $users->column_type->addChild('name','users');
        $xml = $xml->asXML();
        $result_string = $engine->xmlStrTransform($xml,static::$schema_path_module.'/xsl/'.static::$admin_form_builder);
        return $result_string;
    }

    public static function getUsersNodeSet(){
        $users = User::getAll();
        $users_str="<users>";
        foreach ($users as $user){
            $users_str .= "<user value='{$user['id_user']}'>{$user['username']}:  {$user['firstname']},{$user['lastname']},{$user['middlename']} - {$user['roles']}</user>";
        }
        $users_str.="</users>";
        $doc = new DOMDocument;
        $doc->loadXml($users_str);
        return $doc;
    }

    /**
     * Render dynamically generated creation form for admin
     *
     * @param string $type_name Current event type
     * @return mixed Rendered form
     */
    public static function buildCreateFormAdminFromPost($type_name) {
        $engine = Engine::getInstance();
        $xml = simplexml_load_file(ROOTDIR.'/app/modules/'.static::$schema_path_module."/xml/".static::$schema_path."/$type_name.xml");
        $users = $xml->addChild('item');
        $users->addChild('t_column_name','пользователь');
        $users->addChild('column_name','users');
        $users->addChild('column_type');
        $users->column_type->addChild('name','users');
        $xml = $xml->asXML();
        $result_string = $engine->xmlStrTransform($xml,static::$schema_path_module.'/xsl/'.static::$admin_post_form_builder);
        return $result_string;
    }

    /**
     * Render dynamically generated creation form with default
     * values from POST
     *
     * @param string $type_name Current event type
     * @return mixed
     */
    public static function buildCreateFormFromPOST($type_name) {
        $engine = Engine::getInstance();
        $xml = static::$schema_path_module."/xml/".static::$schema_path."/$type_name.xml";
        $result_string = $engine->xmlFileTransform($xml, static::$schema_path_module.'/xsl/'.static::$post_form_builder);
        return $result_string;
    }




    /**
     * Render dynamically generated update form
     *
     * @param string $type_name Current event type
     * @param string $entity_id Current event id
     * @param string $page_form_action_url Action form URL
     * @return mixed Rendered form
     */
    public static function buildUpdateForm($type_name, $entity_id,$page_form_action_url) {
        $class = static::$entity_handler;
        $handler = $class::getInstance();
        $entity = $handler->build($type_name);
        $entity_data = $entity->read($entity_id);
        $ref_handler = ReferenceHandler::getInstance();
        $country_iso = '';
        $address_column_name = '';
        foreach ($entity_data as $column_name => $value) {
            if (ReferenceHandler::getRefModule(static::$entity_prefix.'_'.$type_name, $column_name) == 'address_country')
                $country_iso = $value;
            if (ReferenceHandler::getRefModule(static::$entity_prefix.'_'.$type_name, $column_name) == 'address_object')
                $address_column_name = $column_name;
            if (ReferenceHandler::getRefModule(static::$entity_prefix.'_'.$type_name, $column_name) == 'multiple_files') {
                $column = $column_name;
            }
            $props = ['address_object' => ['object_props' => [], 'method_props' => [$country_iso,$value]]];
            if (isset($column))
                $props['multiple_files'] = ['object_props' => [$type_name], 'method_props' => [$entity,$column]];
            $instance = $ref_handler->build(static::$entity_prefix.'_'.$type_name, $column_name, 'update_form', $props);
            if ($instance) {
                $entity_data[$column_name] = $instance->getData();
            }
        }
        if ($address_column_name !== '') {
            $entity_data[$address_column_name] = [
                'value' => implode(', ',$entity_data[$address_column_name]),
                'mask' => implode(',',array_keys($entity_data[$address_column_name]))
            ];
        }
        self::$entity_data = $entity_data;
        $engine = Engine::getInstance();
        $document = simplexml_load_file(ROOTDIR."/app/modules/".static::$schema_path_module."/xml/".static::$schema_path."/$type_name.xml");
        $document->addChild(static::$entity_prefix.'_id', $entity_id);
        $document->addChild('page_form_action_url', $page_form_action_url);
        $xml = $document->asXML();
        $result_string = $engine->xmlStrTransform($xml, static::$schema_path_module.'/xsl/'.static::$update_form_builder);
        return $result_string;
    }

    /**
     * Render dynamically generated update form without event data
     *
     * @param string $type_name Current type
     * @param string $entity_id Current entity id
     * @param string $page_form_action_url Action form URL
     * @return mixed Rendered form
     */
    public static function buildUpdateFormWithoutData($type_name,$entity_id,$page_form_action_url) {
        $engine = Engine::getInstance();
        $document = simplexml_load_file(ROOTDIR."/app/modules/".static::$schema_path_module."/xml/".static::$schema_path."/$type_name.xml");
        $document->addChild('entity_id', $entity_id);
        $document->addChild('page_form_action_url', $page_form_action_url);
        $xml = $document->asXML();
        $result_string = $engine->xmlStrTransform($xml, static::$schema_path_module.'/xsl/'.static::$post_update_form_builder);
        return $result_string;
    }


    /**
     * Return html form type according to given sql type
     *
     * @param $schema
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

    /**
     * Parse column type from column data from schema
     *
     * @param array $column_data Column data from schema
     * @return array Type and type properties
     */
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
    public static function getPOSTValue($name, $subname = false, $subsubname= false){
        if (isset($_POST[$name])) {
            if ($subname) {
                if ($subsubname) {
                    return $_POST[$name][$subname][$subsubname];
                } else {
                    return $_POST[$name][$subname];
                }
            } else {
                return $_POST[$name];
            }
        }
    }

    /**
     * Check value existance in POST
     *
     * @param string $name Column name
     * @param string $value Column value
     * @param bool $subname Column subname
     * @return bool Existance status
     */
    public static function checkPOSTValue($name, $value, $subname = false){
        if ($subname) {
            return ($_POST[$name][$subname] == $value);
        } else {
            return ($_POST[$name] == $value);
        }
    }


    /**
     * Check value existance in some array in POST
     *
     * @param string $name Column name
     * @param string $value Column value
     * @return bool Existance status
     */
    public static function checkSetPOSTValue($name, $value){
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

    public static function getDictionaryValues($dictionary,$column_name,$ref_id = false){
        $dhandler = DictionaryHandler::getInstance();
        $dictionary = $dhandler->build($dictionary);
        return $dictionary->getXML($column_name, $ref_id);
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
}