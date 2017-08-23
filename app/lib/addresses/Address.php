<?php
class Address {

    /**
     * @var array Data should be provided like this: ['type_name' => 'address_object_name', ... ]
     * In the hierarchical order!
     */
    public $data;

    public function __construct($data = []) {
        $this->data = $data;
    }

    /**
     * Saves address data to the DB
     *
     * @return int Initial id of the address
     */
    public function save() {
        // ['country' => 'iso_code', 'city' => ...]
        $conn = DBConnection::getInstance();
        $data = $this->data;
        $country_iso = $data['country'];
        unset($data['country']);
        $table_name = $this->getCountryTableName($country_iso)['table_name'];
        $types = $this->getTypes();
        $last_item_id = -1;
        foreach ($data as $type_name => $object_name) {
            $type_id = array_search($type_name, $types);
            if (!$type_id) {
                return false;
            }
            $check_query = "CALL checkAddressObjectPresence('$table_name','$object_name', $last_item_id, $type_id);";
            $result_check = $conn->performQueryFetch($check_query);
            if ($result_check) {
                $last_item_id = $result_check['id'];
            } else {
                $query = "CALL writeAddressObject('$table_name', '$object_name', $last_item_id, $type_id);";
                $result = $conn->performQueryFetch($query);
                $last_item_id = $result['id'];
            }
        }
        return $last_item_id;
    }

    /**
     * Builds address data array from the initial id of address object
     *
     * @param $init_id int|string ID of the first address object in the hierarchy
     * @param $concat bool Return concatenated address if true
     * @return array|string Address array: ['type_name' => 'address_object_name', ... ]
     */
    public function read($country_iso, $object_id, $concat = false) {
        $types = $this->getTypes();
        $country_info = $this->getCountryTableName($country_iso);
        $table_name = $country_info['table_name'];
        $conn = DBConnection::getInstance();
        $last_item_id = $object_id;
        $result_table = [];
        while ($last_item_id !== '-1') {
            $query = "SELECT * FROM $table_name WHERE id = $last_item_id";
            $result_row = $conn->performQueryFetch($query);
            $last_item_id = $result_row['parent_id'];
            $result_table[] = $result_row;
        }
        if (!$result_table)
            return [];
        $result_table = array_reverse($result_table);
        foreach ($result_table as $value) {
            $result[$types[$value['type_id']]] = $value['name'];
        }
        if ($concat) {
            return implode(', ', $result);
        } else {
            return $result;
        }
    }

//    /**
//     * Destroys whole hierarchy of the address from the initial id of object
//     *
//     * @param $init_id string|integer ID of the first address object in the hierarchy
//     * @return bool True if no exception was caught
//     */
//    public function destroy($init_id) {
//        $conn = DBConnection::getInstance();
//        $query = "CALL buildAddress($init_id);";
//        $result_table = $conn->performQueryFetchAll($query);
//        if (!$result_table)
//            ErrorHandler::throwException(ADDRESS_DESTROY_ERROR, 'page');
//        foreach ($result_table as $object) {
//            $check_query = "SELECT id FROM address_object WHERE parent_id = {$object['id']};";
//            $result_check = $conn->performQueryFetchAll($check_query);
//            if ($result_check) {
//                break;
//            } else {
//                $query_destroy = "DELETE FROM address_object WHERE id = {$object['id']};";
//                $result = $conn->performQuery($query_destroy);
//                if (!$result)
//                    ErrorHandler::throwException(ADDRESS_DESTROY_ERROR, 'page');
//            }
//        }
//        return true;
//    }

    /**
     * Gets all the address object types from the DB
     *
     * @return array Array of address types: ['type_id' => 'type_name', ... ]
     */
    private function getTypes() {
        $conn = DBConnection::getInstance();
        $query = "CALL getAddressTypes();";
        $result = $conn->performQueryFetchAll($query);
        $result_array = [];
        foreach ($result as $row) {
            $result_array[$row['id']] = $row['name'];
        }
        return $result_array;
    }

    /**
     * Gets table name of country by iso code. Creates country table if needed.
     *
     * @param $country_iso string|int Country ISO code
     * @return array Table name and name of the country
     */
    private function getCountryTableName($country_iso) {
        $conn = DBConnection::getInstance();
        $query = "CALL getCountryByISO($country_iso);";
        $result = $conn->performQueryFetch($query);
        if (!$result)
            return false;
        $table_name_check = 'address_'.strtolower($result['alpha2']);
        $query = "CALL checkAddressTablePresence('$table_name_check')";
        $result_table = $conn->performQueryFetch($query);
        if ($result_table) {
            return ['table_name' => $result_table['table_name'], 'name' => $result['t_name']];
        } else {
            $constructor = new CountryConstructor($result['alpha2']);
            return ['table_name' => $constructor->build(), 'name' => $result['t_name']];
        }
    }

    function __destruct() {
        $this->data = null;
    }

}