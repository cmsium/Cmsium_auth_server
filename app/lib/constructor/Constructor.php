<?php
class Constructor {

    protected static $instance;
    public $type_name;
    public $table_prefix;
    public $types_table;
    public $basic_columns = [];
    public $primary;
    public static $blacklist = [];
    public $errors = [];
    public static $procedures = [];
    public static $schema_class_module;
    public static $schema_class;
    public static $entity_class;
    public static $schema_pattern;
    public static $schema_path_module;
    public static $schemas_path;
    public $xml_basic_columns = [];

    /**
     * Get Instance of Constructor
     *
     * @return object Constructor New instance or static
     */
    public static function getInstance($type_name)
    {
        if (!(static::$instance instanceof self)) {
            static::$instance = new static($type_name);
        }
        return static::$instance;
    }

    protected function __construct($type_name){
        $this->type_name = $type_name;
    }
    protected function __clone(){}



    /**
     * Creating new event type in system
     * @param array $data_array Type model (properties)
     */
    public function createType(array $data_array, $multiple = false) {
        $this->createTable($data_array);
        $this->createXML($data_array);
        if ($multiple == false) {
            $this->createSchema();
        }
    }

    /**
     * Delete event type from system
     */
    public function destroyType() {
        $this->deleteTable();
        $this->destroyXML();
        $this->destroySchema();
    }

    /**
     * Add new columns to  event type in system
     * @param array $data_array Type model (properties)
     */
    public function UpdateTypeAdd(array $data_array) {
        $this->addColumnsToTable($data_array);
        $this->updateXML($data_array);
        $this->createSchema();
    }
    /**
     * Add new columns to  event type in system
     * @param array $columns Type model (properties)
     */
    public function UpdateTypeDelete(array $columns) {
        $this->deleteColumnsFromTable($columns);
        $this->updateXMLdelete($columns);
        $this->createSchema();
    }


    /**
     * Get db table name for current type
     *
     * @param string $name Type name
     * @return string Table name
     */
    public function getTableName($name){
        return $this->table_prefix."_".$name;
    }


    public function deleteColumnsFromTable($columns){
        $type_name = $this->type_name;
        $schema = $this->getTypeSchema();
        $foreign_k_query = "";
        foreach ($columns as $column){
            foreach ($schema as $key => $value){
                if ($value['column_name'] == $column){
                    if ($value['column_key'] == "MUL"){
                        $foreign_k_query .= "ALTER TABLE {$this->getTableName($type_name)} DROP FOREIGN KEY fk_$column;";
                    }
                }
            }
        }
        $column_query = "";
        foreach ($columns as $column_name){
            $column_query.= "DROP COLUMN $column_name;";
        }
        $conn = DBConnection::getInstance();
        $query = "$foreign_k_query
        ALTER TABLE {$this->getTableName($type_name)} $column_query";
        $result = $conn->performMultiQuery($query);
        if (!$result) {
            ErrorHandler::throwException($this->errors['update_table_error'], 'page');
        }

    }

    /**
     * Add columns to table
     *
     * @param array $data_array New columns description array
     * @return mixed Add status
     */
    public function addColumnsToTable($data_array){
        $type_name = $this->type_name;
        foreach ($data_array['model'] as $value) {
            $columns[static::transliterate($value[1])] = implode(' ', array_slice($value, 2, 3));
            if (isset($value[5]) && $value[5]) {
                $foreign_keys[] = [static::transliterate($value[1]),static::transliterate($value[5])];
            }
            if (isset($value[6]) && $value[6]) {
                $references[] = [static::transliterate($value[1]),static::transliterate($value[6])];
            }
        }
        $references_query = '';
        if (!empty($references)) {
            $trans_name = static::transliterate($data_array['type_name']);
            foreach ($references as $ref) {
                $references_query .= "INSERT INTO system_references VALUES('{$this->getTableName($trans_name)}', '{$ref[0]}', '{$ref[1]}');";
                if ($ref{1} == 'multiple_files'){
                    $references_query.="CREATE TABLE files_in_{$this->getTableName($type_name)} (event_id VARCHAR(32) NOT NULL,
                                                                                              file_id VARCHAR(32) NOT NULL)";
                }
            }
        }
        $type_name = $this->type_name;
        $foreign_k_query = [];
        if (!empty($foreign_keys)) {
            foreach ($foreign_keys as $f_key) {
                $foreign_k_query[] = "ALTER TABLE {$this->getTableName($type_name)} ADD CONSTRAINT fk_{$f_key[0]} FOREIGN KEY ({$f_key[0]}) REFERENCES {$f_key[1]} ON UPDATE SET NULL ON DELETE RESTRICT ";
            }
        }
        $foreign_k_query_string = implode(';',$foreign_k_query);
        $column_query = [];
        foreach ($columns as $column_name => $type){
            $column_query[] = "ADD COLUMN $column_name $type";
        }
        $conn = DBConnection::getInstance();
        $column_query_string = implode(',',$column_query);
        $query = "
        ALTER TABLE {$this->getTableName($type_name)} $column_query_string; $foreign_k_query_string;
        {$references_query}";
        $result = $conn->performMultiQuery($query);
        if (!$result) {
            ErrorHandler::throwException($this->errors['update_table_error'], 'page');
        }
        return $result;
    }


    /**
     * Create new document type table in database
     *
     * @param array $data_array Type model (properties)
     * @return mixed Creating status
     */
    public function createTable($data_array){
        $type_name = $this->type_name;
        foreach ($data_array['model'] as $value) {
            $columns[static::transliterate($value[1])] = implode(' ', array_slice($value, 2, 3));
            if (isset($value[5]) && $value[5]) {
                $foreign_keys[] = [static::transliterate($value[1]),static::transliterate($value[5])];
            }
            if (isset($value[6]) && $value[6]) {
                $references[] = [static::transliterate($value[1]),static::transliterate($value[6])];
            }
        }
        $references_query = '';
        if (!empty($references)) {
            $trans_name = static::transliterate($data_array['name']);
            foreach ($references as $ref) {
                $references_query .= "INSERT INTO system_references VALUES('{$this->getTableName($trans_name)}', '{$ref[0]}', '{$ref[1]}');";
                if ($ref{1} == 'multiple_files'){
                    $references_query.="CREATE TABLE IF NOT EXISTS files_in_{$this->getTableName($type_name)} 
                (event_id VARCHAR(32) NOT NULL,
                event_column VARCHAR(255) NOT NULL,
                file_id VARCHAR(32) NOT NULL);";
                }
            }
        }
        $foreign_k_query = '';
        if (!empty($foreign_keys)) {
            foreach ($foreign_keys as $f_key) {
                $foreign_k_query .= ", CONSTRAINT fk_{$type_name}_{$f_key[0]}  FOREIGN KEY ({$f_key[0]}) REFERENCES {$f_key[1]} ON UPDATE SET NULL ON DELETE RESTRICT";
            }
        }
        $column_query = "";
        foreach ($columns as $column_name => $type){
            $column_query .= "$column_name $type,";
        }
        $conn = DBConnection::getInstance();
        $query = "
        CREATE TABLE {$this->getTableName($type_name)} ({$this->getBasicColumnsQuery()} $column_query PRIMARY KEY ({$this->primary})$foreign_k_query) ENGINE=InnoDB;
        INSERT INTO {$this->types_table}(name, t_name) VALUES ('$type_name', '{$data_array['name']}');
        {$references_query}";
        $result = $conn->performMultiQuery($query);
        if (!$result) {
            ErrorHandler::throwException($this->errors['create_table_error'], 'page');
        }
        return $result;
    }


    /**
     * Return basic columns create query for db
     *
     * @return string Basic columns create query
     */
    public function getBasicColumnsQuery(){
        $basic_column_query = "";
        foreach ($this->basic_columns as $basic_column_name => $description){
            $basic_column_query .= "$basic_column_name {$description['type']} {$description['flags']},";
        }
        return $basic_column_query;
    }


    /**
     * Delete current document type table in database
     *
     * @return mixed Deleting status
     */
    public function deleteTable(){
        $type_name = $this->type_name;
        $conn = DBConnection::getInstance();
        $query = "CALL ".static::$procedures['delete_table']."('{$type_name}');";
        $result = $conn->performQuery($query);
        if (!$result)
            return false;
        $query = "DELETE FROM system_references WHERE table_name = '{$this->getTableName($type_name)}'";
        $result = $conn->performQuery($query);
        if (!$result)
            return false;
        $query = "DROP TABLE IF EXISTS files_in_{$this->getTableName($type_name)}";
        $conn->performQuery($query);
        return true;
    }

    /**
     * Create new type subclass declaration with document schema
     * in documents/subclasses directory
     *
     */
    public function createSchema() {
        $type_schema = $this->getTypeSchema();
        $type_name = $this->type_name;
        if (class_exists(static::$schema_class)) {
            $class = static::$schema_class;
            $schema_array = $class::$data_model;
        } else {
            $schema_array = [];
        }
        $schema_array[$type_name] = $type_schema;
        $string_array = $this->buildSchemaArray($schema_array);
        $result_content = sprintf(static::$schema_pattern, $string_array);
        $file = new File(ROOTDIR."/app/lib/".static::$schema_class_module."/".static::$schema_class.".php");
        return $file->write($result_content);
    }


    public static function createMultSchema($types) {
        if (class_exists(static::$schema_class)) {
            $class = static::$schema_class;
            $schema_array = $class::$data_model;
        } else {
            $schema_array = [];
        }
        foreach ($types as $type_name) {
            $type_schema = static::getTypeSchemaEx($type_name);
            $schema_array[$type_name] = $type_schema;
        }
        $string_array = static::buildSchemaArray($schema_array);
        $result_content = sprintf(static::$schema_pattern, $string_array);
        $file = new File(ROOTDIR."/app/lib/".static::$schema_class_module."/".static::$schema_class.".php");
        return $file->write($result_content);
    }

    /**
     *Delete current type subclass declaration out of documents/subclasses directory
     */
    public function destroySchema() {
        $type_name = $this->type_name;
        if (!class_exists(static::$schema_class)) {
            ErrorHandler::throwException(WRONG_MODULE, 'page');
        }
        $class = static::$schema_class;
        $schema_array = $class::$data_model;
        if (!array_key_exists($type_name, $schema_array)) {
            ErrorHandler::throwException(ARRAY_KEY_NOT_FOUND, 'page');
        }
        unset($schema_array[$type_name]);
        $string_array = $this->buildSchemaArray($schema_array);
        $result_content = sprintf(static::$schema_pattern, $string_array);
        $file = new File(ROOTDIR."/app/lib/".static::$schema_class_module."/".static::$schema_class.".php");
        $file->write($result_content);
        return true;
    }

    /**
     * Builds a proper ready-to-write string array from the PHP array of columns
     *
     * @param $schema_array Document type schema
     * @return string String array
     */
    protected static function buildSchemaArray($schema_array) {
        $string_array = "";
        foreach ($schema_array as $type => $columns) {
            $string_array .= "'$type' => [";
            foreach ($columns as $column) {
                $string_array .= '[';
                foreach ($column as $key => $value) {
                    $string_array .= "\"$key\" => \"$value\",";
                }
                $string_array .= '],'.PHP_EOL;
            }
            $string_array .= "],";
        }
        return $string_array;
    }

    public function destroyXML() {
        $type_name = $this->type_name;
        $file = new File(ROOTDIR."/app/lib/".static::$schema_path_module."/xml/".static::$schemas_path."/{$type_name}.xml");
        $file->delete();
    }

    public function createXML($data_array,$dynamic_basic_columns = false) {
        $result_array['t_type_name'] = $data_array['name'];
        $result_array['type_name'] = static::transliterate($data_array['name']);
        if ($dynamic_basic_columns){
            foreach ($dynamic_basic_columns as $key => $value) {
                $result_array[$key] = $value;
            }
        }
        foreach ($this->xml_basic_columns as $value) {
            $result_array[] = $value;
        }
        foreach ($data_array['model'] as $column) {
            $raw_array['t_column_name'] = $column[1];
            $raw_array['column_name'] = static::transliterate($column[1]);
            $column_type_data = static::getColumnData($column[2]);
            $constraints = array_filter(array_slice($column, 3, 2),'strlen');
            $raw_array['column_type'] = ['t_name'=>$column_type_data['data_type'],
                                         'name'=>static::transliterate($column_type_data['data_type']),
                                         'props'=>$column_type_data['props'],
                                         'constraints'=>$constraints
            ];
            if (!empty($column[5])) {
                $raw_array['column_type']['foreign_key'] = static::transliterate($column[5]);
                $raw_array['column_type']['t_foreign_key'] = $column[5];
            }
            if (!empty($column[6])) {
                $raw_array['column_type']['reference'] = $column[6];
            }
            $result_array[] = $raw_array;
        }
        $class = static::$entity_class;
        $xml = $class::toXML($result_array, false);
        $file = new File(ROOTDIR."/app/lib/".static::$schema_path_module."/xml/".static::$schemas_path."/{$result_array['type_name']}.xml");
        $file->write($xml);
        return true;
    }


    public function updateXML($data_array) {
        $converter = DataConverter::getInstance();
        $file = new File(ROOTDIR."/app/lib/".static::$schema_path_module."/xml/".static::$schemas_path."/{$this->type_name}.xml");
        $xml_array = $converter->XMLToArray($file->getContent());
        foreach ($data_array['model'] as $column) {
            $raw_array['t_column_name'] = $column[1];
            $raw_array['column_name'] = static::transliterate($column[1]);
            $column_type_data = static::getColumnData($column[2]);
            $constraints = array_filter(array_slice($column, 3, 2),'strlen');
            $raw_array['column_type'] = ['t_name'=>$column_type_data['data_type'],
                'name'=>static::transliterate($column_type_data['data_type']),
                'props'=>$column_type_data['props'],
                'constraints'=>$constraints
            ];
            if (!empty($column[5])) {
                $raw_array['column_type']['foreign_key'] = static::transliterate($column[5]);
                $raw_array['column_type']['t_foreign_key'] = $column[5];
            }
            if (!empty($column[6])) {
                $raw_array['column_type']['reference'] = $column[6];
            }
            $xml_array['item'][] = $raw_array;
        }
        $xml_array = array_merge($xml_array,$xml_array['item']);
        unset($xml_array['item']);
        $class = static::$entity_class;
        $xml = $class::toXML($xml_array, false);
        $file = new File(ROOTDIR."/app/lib/".static::$schema_path_module."/xml/".static::$schemas_path."/{$this->type_name}.xml");
        $file->write($xml);
        return true;
    }

    public function updateXMLdelete($columns) {
        $converter = DataConverter::getInstance();
        $file = new File(ROOTDIR."/app/lib/".static::$schema_path_module."/xml/".static::$schemas_path."/{$this->type_name}.xml");
        $xml_array = $converter->XMLToArray($file->getContent());
        foreach ($columns as $column) {
            foreach ($xml_array['item'] as $key => $value){
                if ($column == $value['column_name']) {
                    unset($xml_array['item'][$key]);
                }
            }
        }
        $xml_array = array_merge($xml_array,$xml_array['item']);
        unset($xml_array['item']);
        $class = static::$entity_class;
        $xml = $class::toXML($xml_array, false);
        $file = new File(ROOTDIR."/app/lib/".static::$schema_path_module."/xml/".static::$schemas_path."/{$this->type_name}.xml");
        $file->write($xml);
        return true;
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

    public static function transliterate($string) {
        $string = mb_strtolower($string);
        $mask = TRANSLIT_MASK;
        $result = '';
        $string_arr = preg_split('//u', $string, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($string_arr as $char) {
            if (isset($mask[$char])) {
                $result .= $mask[$char];
            } else {
                $result .= $char;
            }
        }
        return $result;
    }

    /**
     * Get document subclass name based on type
     *
     * @return string Subclass name
     */
    public function getDocumentClass(){
        $document_type = $this->type_name;
        $name = explode('_',$document_type);
        $name = array_map('ucfirst',$name);
        $name = implode("", $name);
        return "Document".$name;
    }

    /**
     * Get current document type schema (data model)
     *
     * @return mixed false|Document type schema array
     */
    public function getTypeSchema() {
        $type_name = $this->type_name;
        $conn = DBConnection::getInstance();
        $query = "CALL ".static::$procedures['get_schema']."('{$type_name}');";
        $result = $conn->performQueryFetchAll($query);
        if (!$result) {
            ErrorHandler::throwException(PERFORM_QUERY_ERROR, 'page');
        }
        return $result;
    }

    public static function getTypeSchemaEx($type) {
        $type_name = $type;
        $conn = DBConnection::getInstance();
        $query = "CALL ".static::$procedures['get_schema']."('{$type_name}');";
        $result = $conn->performQueryFetchAll($query);
        if (!$result) {
            ErrorHandler::throwException(PERFORM_QUERY_ERROR, 'page');
        }
        return $result;
    }

    public static function clearSchemas() {
        $xmls = glob(ROOTDIR."/app/lib/".static::$schema_path_module."/xml/".static::$schemas_path."/*.xml"); // get all file names
        foreach($xmls as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }
        $document_schema = ROOTDIR."/app/lib/".static::$schema_class_module."/classes/".static::$schema_class.".php";
        if(is_file($document_schema))
            unlink($document_schema);
    }

    public function destroyInstance() {
        static::$instance = null;
    }

}