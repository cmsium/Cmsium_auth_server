<?php
class KLADRReader {

    private static $name = 'RU';
    private static $table_name = 'address_ru';
    private static $kladr_file_name = 'KLADR.DBF';
    private static $street_file_name = 'STREET.DBF';

    private static function insertSTREETTable() {
        $kladr_table_path = TMP_PATH.'/'.self::$name.'/'.self::$street_file_name;
        $file = new DBase($kladr_table_path);
        $file->open();
        $types = self::getTypes();
        $conn = DBConnection::getInstance();
        $rows_number = $file->rows;
        // create temp table
        $query_temp_table = "CALL createTempTableStreetsKLADR();";
        $conn->performQuery($query_temp_table);
        // insert into temp table
        for ($i = 1; $i <= $rows_number; $i++) {
            $row = $file->getRow($i);
            // Truncate whitespaces in the end
            $db_row['name'] = rtrim($row[0]);

            $code_array['region'] = (int)substr($row[2],0,2);
            $code_array['area'] = (int)substr($row[2],2,3);
            $code_array['city'] = (int)substr($row[2],5,3);
            $code_array['locality'] = (int)substr($row[2],8,3);
            $code_array['street'] = (int)substr($row[2],11,4);

            $code_db_array['region'] = substr($row[2],0,2);
            $code_db_array['area'] = substr($row[2],0,5);
            $code_db_array['city'] = substr($row[2],0,8);
            $code_db_array['locality'] = substr($row[2],0,11);
            $code_db_array['street'] = substr($row[2],0,15);

            $reversed_code_array = array_reverse($code_array);
            foreach ($reversed_code_array as $type => $value) {
                if ($value !== 0) {
                    $db_row['code'] = $code_db_array[$type];
                    $db_row['type_id'] = array_search($type, $types);
                    break;
                }
            }

            $query_insert = "INSERT INTO kladr_street_temp(name, code, full_code, type_id) VALUES('{$db_row['name']}', '{$db_row['code']}', '{$row[2]}', {$db_row['type_id']});";
            $conn->performQuery($query_insert);

            if (($i % 1000) == 0) {
                echo($i), "/$rows_number added to temp table\r";
            }
        }
        echo "\n";
        self::writeFromSTREETTempTable($rows_number);
    }

    private static function insertKLADRTable() {
        $kladr_table_path = TMP_PATH.'/'.self::$name.'/'.self::$kladr_file_name;
        $file = new DBase($kladr_table_path);
        $file->open();
        $types = self::getTypes();
        $conn = DBConnection::getInstance();
        $rows_number = $file->rows;
        // create temp table
        $query_temp_table = "CALL createTempTableKLADR();";
        $conn->performQuery($query_temp_table);
        // insert into temp table
        for ($i = 1; $i <= $rows_number; $i++) {
            $row = $file->getRow($i);
            // Truncate whitespaces in the end
            $db_row['name'] = rtrim($row[0]);

            $code_array['region'] = (int)substr($row[2],0,2);
            $code_array['area'] = (int)substr($row[2],2,3);
            $code_array['city'] = (int)substr($row[2],5,3);
            $code_array['locality'] = (int)substr($row[2],8,3);

            $code_db_array['region'] = substr($row[2],0,2);
            $code_db_array['area'] = substr($row[2],0,5);
            $code_db_array['city'] = substr($row[2],0,8);
            $code_db_array['locality'] = substr($row[2],0,11);

            $reversed_code_array = array_reverse($code_array);
            foreach ($reversed_code_array as $type => $value) {
                if ($value !== 0) {
                    $db_row['code'] = $code_db_array[$type];
                    $db_row['type_id'] = array_search($type, $types);
                    break;
                }
            }

            $query_insert = "INSERT INTO kladr_temp(name, code, full_code, type_id) VALUES('{$db_row['name']}', '{$db_row['code']}', '{$row[2]}', {$db_row['type_id']});";
            $conn->performQuery($query_insert);

            if (($i % 1000) == 0) {
                echo($i), "/$rows_number added to temp table\r";
            }
        }
        echo "\n";
        self::writeFromKLADRTempTable($rows_number);
    }

    public static function update() {
        self::insertKLADRTable();
        self::insertSTREETTable();
    }

    public static function writeFromKLADRTempTable($rows_number) {
        $conn = DBConnection::getInstance();
        $types = self::getTypes();
        for ($i = 1; $i <= $rows_number; $i++) {
            $query_get = "CALL getRowFromKLADRTemp($i);";
            $result = $conn->performQueryFetch($query_get);

            $code_array['region'] = (int)substr($result['full_code'],0,2);
            $code_array['area'] = (int)substr($result['full_code'],2,3);
            $code_array['city'] = (int)substr($result['full_code'],5,3);
            $code_array['locality'] = (int)substr($result['full_code'],8,3);

            $code_db_array['region'] = substr($result['full_code'],0,2);
            $code_db_array['area'] = substr($result['full_code'],0,5);
            $code_db_array['city'] = substr($result['full_code'],0,8);
            $code_db_array['locality'] = substr($result['full_code'],0,11);

            $result_array = [];
            foreach ($code_array as $type => $value) {
                if ($value !== 0) {
                    $type_id = array_search($type, $types);
                    //$query_name = "CALL getNameFromKLADRTempByCode($value, $type_id);";
                    $query_name = "CALL getNameFromKLADRTempByCode('{$code_db_array[$type]}', $type_id);";
                    $result_chain = $conn->performQueryFetch($query_name);
                    $result_array[$type] = $result_chain['name'];
                }
            }
            $obj_id = self::saveAddressObject($result_array, $types);
            $query_obj_id = "CALL addObjectIDToKLADRTemp($obj_id,$i);";
            $conn->performQuery($query_obj_id);

            if (($i % 100) == 0) {
                echo($i), "/$rows_number added to address object\r";
            }
        }
        echo "\n";
    }

    public static function writeFromSTREETTempTable($rows_number) {
        $conn = DBConnection::getInstance();
        $types = self::getTypes();
        for ($i = 1; $i <= $rows_number; $i++) {
            $query_get = "CALL getRowFromStreetsKLADRTemp($i);";
            $result = $conn->performQueryFetch($query_get);

            $code_array['region'] = (int)substr($result['full_code'],0,2);
            $code_array['area'] = (int)substr($result['full_code'],2,3);
            $code_array['city'] = (int)substr($result['full_code'],5,3);
            $code_array['locality'] = (int)substr($result['full_code'],8,3);
            $code_array['street'] = (int)substr($result['full_code'],11,4);

            $code_db_array['region'] = substr($result['full_code'],0,2);
            $code_db_array['area'] = substr($result['full_code'],0,5);
            $code_db_array['city'] = substr($result['full_code'],0,8);
            $code_db_array['locality'] = substr($result['full_code'],0,11);
            $code_db_array['street'] = substr($result['full_code'],0,15);

            $result_array = [];
            $reversed_code_array = array_reverse($code_array);
            $obj_id = 0;
            foreach ($reversed_code_array as $type => $value) {
                if ($type !== 'street' && $value !== 0) {
                    $type_id = array_search($type, $types);
                    $query_name = "CALL getObjIDFromKLADRTempByCode('{$code_db_array[$type]}', $type_id);";
                    $result_obj_id = $conn->performQueryFetch($query_name);
                    $obj_id = $result_obj_id ? $result_obj_id['obj_id'] : false;
                    break;
                }
            }
            // TODO: Check if street record already exists
            if ($obj_id) {
                $query_insert_address = "CALL writeAddressRUObject('{$result['name']}',$obj_id,7);";
                if (!$conn->performQuery($query_insert_address)) {
                    echo "Не удалось: $query_insert_address".PHP_EOL;
                    die("Critical script error!");
                }
            }
            if (($i % 100) == 0) {
                echo($i), "/$rows_number added to address object\r";
            }
        }
        echo "\n";
    }

    private static function getTypes() {
        $conn = DBConnection::getInstance();
        $query = "CALL getAddressTypes();";
        $result = $conn->performQueryFetchAll($query);
        $result_array = [];
        foreach ($result as $row) {
            $result_array[$row['id']] = $row['name'];
        }
        return $result_array;
    }

    private static function saveAddressObject($data, $types) {
        $conn = DBConnection::getInstance();
        $last_item_id = -1;
        foreach ($data as $type_name => $object_name) {
            $type_id = array_search($type_name, $types);
            $check_query = "CALL checkAddressRUObject('$object_name',$last_item_id,$type_id);";
            $result_check = $conn->performQueryFetch($check_query);
            if ($result_check) {
                $last_item_id = $result_check['id'];
            } else {
                $query = "CALL writeAddressRUObject('$object_name',$last_item_id,$type_id);";
                $result = $conn->performQueryFetch($query);
                $last_item_id = $result['id'];
            }
        }
        return $last_item_id;
    }

}