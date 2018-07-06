<?php
class ReferenceHandler {

    private static $instance;
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
    private function __clone(){}
    public function __destruct(){}

    /**
     * Builds a reference object based on given params
     *
     * @param $table_name string Name of given table
     * @param $column_name string Name of given column
     * @param $action string CRUD action (create, read, update, delete)
     * @param array $props Properties for object creation
     * @return bool|Reference False, if something went wrong
     */
    public function build($table_name, $column_name, $action, $props = []) {
        $conn = DBConnection::getInstance();
        $query = "CALL getTableReference('$table_name');";
        $result = $conn->performQueryFetchAll($query);
        if ($result) {
            foreach ($result as $row) {
                if ($row['column_name'] == $column_name) {
                    if (isset($props[$row['module_name']])) {
                        return new Reference($row['module_name'],$action,$props);
                    } else {
                        return false;
                    }
                }
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * Returns a reference module name by given table name and column name
     *
     * @param $table_name string
     * @param $column_name string
     * @return bool|string Module name
     */
    public static function getRefModule($table_name, $column_name) {
        $conn = DBConnection::getInstance();
        $query = "CALL getTableReference('$table_name');";
        $result = $conn->performQueryFetchAll($query);
        if ($result) {
            foreach ($result as $row) {
                if ($row['column_name'] == $column_name) {
                    return $row['module_name'];
                }
            }
            return false;
        } else {
            return false;
        }
    }

    /**
     * Returns an array of all references of given table
     *
     * @param $table_name string Name of the table
     * @return array|bool Array of references
     */
    public static function getAllRefs($table_name) {
        $conn = DBConnection::getInstance();
        $query = "CALL getTableReference('$table_name');";
        $result = $conn->performQueryFetchAll($query);
        if ($result) {
            $output = [];
            foreach ($result as $row) {
                $output[$row['column_name']] = $row['module_name'];
            }
            return $output;
        } else {
            return false;
        }
    }

    /**
     * Builds the reference validation mask
     *
     * @param string $table_name Name of the table
     * @return array Validation mask
     */
    public static function buildRefMask($table_name) {
        $refs = self::getAllRefs($table_name);
        $result = [];
        if ($refs) {
            foreach ($refs as $column_name => $module_name) {
                $ref_mask = Masks::getRefMask($module_name);
                if ($ref_mask) {
                    $result[$column_name] = $ref_mask;
                }
            }
        }
        return $result;
    }

}