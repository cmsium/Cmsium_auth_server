<?php
class RoleConstructor extends Constructor {

    protected static $instance;
    public $type_name;
    public $table_prefix = 'properties';
    public $types_table = 'roles';
    public $basic_columns = ['user_id'=>['type'=>'varchar(32)','flags'=>'NOT NULL'],];
    public $primary='user_id';
    public static $blacklist = ['user_id'];
    public $errors = ['create_table_error'=>CREATE_ROLE_ERROR];
    public static $procedures = ['get_schema'=>'getRoleTableColumns'];
    public static $schema_class = 'RolesSchema';
    public static $entity_class = 'User';
    public static $schema_pattern = ROLES_SCHEMA_PATTERN;
    public static $schema_class_module = "users";
    public static $schema_path_module = "users";
    public static $schemas_path = 'generated_roles_schemas';
    public $xml_basic_columns = [];
    public $role_id;


    protected function __construct($init_array){
        if (isset($init_array['role_name']))
            $this->type_name = $init_array['role_name'];
        if (isset($init_array['role_id'])) {
            $this->role_id = $init_array['role_id'];
            $this->type_name = $this->getRoleData($this->role_id)['name'];
        }
    }

    /**
     * Get bd table name for current type
     *
     * @param string $name Type name
     * @return string Table name
     */
    public function getTableName($name){
        return $name."_".$this->table_prefix;
    }



    /**
     * Creating new event type in system
     * @param array $columns Type model (properties)
     */
    public function createRole(array $columns) {
        $this->createType($columns);
    }

    /**
     * Delete role from system
     */
    public function destroyRole($role_id) {
        $conn = DBConnection::getInstance();
        $role = $this->getRoleData($role_id)['name'];
        if ($role == 'staff') {
            return false;
        }
        $conn->startTransaction();
        if (!$this->DeleteRole($role_id)){
            $conn->rollback();
            return false;
        }
        if (!$this->deleteRoleProp($role)){
            $conn->rollback();
            return false;
        }
        $this->destroyXML($role);
        if (!$this->destroySchema($role)){
            $conn->rollback();
            return false;
        }
        $conn->commit();
        return $role;
    }

    /**
     * Delete current role from database
     *
     * @param array $columns Type model (properties)
     * @return mixed Deleting status
     */
    public function deleteRole($role_id){
        $conn = DBConnection::getInstance();
        $query = "CALL deleteRole('{$role_id}');";
        $result = $conn->performQuery($query);
        if (!$result)
            return false;
        return $result;
    }

    /**
     * Delete current role properties table from database
     *
     * @param string $role Current role
     * @return bool Deleting status
     */
    public function deleteRoleProp($role){
        $conn = DBConnection::getInstance();
        $query = "CALL deleteRoleProp('{$role}');";
        $result = $conn->performQuery($query);
        if (!$result)
            return false;
        $query = "DELETE FROM system_references WHERE table_name = '{$role}_properties'";
        $result = $conn->performQuery($query);
        if (!$result)
            return false;
        return $result;
    }

    /**
     * Get current role schema (data model)
     *
     * @return mixed false|Event type schema array
     */
    public function getRoleSchema() {
        return $this->getTypeSchema();
    }

    /**
     * Get current role data
     *
     * @param string $role_id Role id
     * @return mixed Role data array
     */
    public function getRoleData($role_id){
        $conn = DBConnection::getInstance();
        $query = "CALL getRoleData('{$role_id}');";
        return $conn->performQueryFetch($query);

    }

    public function createXML($data_array,$result_array = false){
        $result_array['t_role_name'] = $data_array['name'];
        $result_array['role_name'] = static::transliterate($data_array['name']);
        parent::createXML($data_array,$result_array);
    }

    /**
     * Get all roles in system list
     *
     * @return array|bool All roles array
     */
    public static function getRoles(){
        $conn = DBConnection::getInstance();
        $query = "CALL getRoles();";
        $result = $conn->performQueryFetchAll($query);
        if (!$result)
            return false;
        foreach ($result as $value) {
            $result_array[] = $value['name'];
        }
        return $result_array;
    }


}