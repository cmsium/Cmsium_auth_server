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
        $data = $this->data;
        $address = Config::get('address_domain');
        $request = new Request("$address/save");
        $response = $request->sendRequestJSON('POST',
            ['Content-type: application/x-www-form-urlencoded','Cookie: token='.$_COOKIE['token']],
            http_build_query($data));
        if ($response['status'] === 'ok') {
            return $response['last_id'];
        } else {
            return false;
        }
    }

    /**
     * Builds address data array from the initial id of address object
     *
     * @param $init_id int|string ID of the first address object in the hierarchy
     * @param $concat bool Return concatenated address if true
     * @return array|string Address array: ['type_name' => 'address_object_name', ... ]
     */
    public function read($country_iso, $object_id, $concat = false) {
        $address = Config::get('address_domain');
        $url = "$address/read?country_iso=$country_iso&object_id=$object_id&concat=";
        if ($concat) {
            $url .= 'true';
        } else {
            $url .= 'false';
        }
        $request = new Request($url);
        $result = $request->sendRequestJSON('GET',
            'Cookie: token='.$_COOKIE['token'],
            false);
        return $result;
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
            ErrorHandler::throwException(COUNTRY_NOT_FOUND, 'page');
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