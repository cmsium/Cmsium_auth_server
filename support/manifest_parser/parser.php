<?php
require '../../config/init_libs.php';
$converter = DataConverter::getInstance();
$conn = DBConnection::getInstance();

function writeToDB($action_name, $auth_string, $service_name) {
    $conn = DBConnection::getInstance();

    $id = md5($action_name.$service_name);
    // Write to system_actions
    $query = "INSERT INTO system_actions(action_id, name, service_name) VALUES ('$id', '$action_name', '$service_name');";
    $conn->performQuery($query);
    // Assign permissions
    if (!empty($auth_string)) {
        $auth_roles = explode(',', $auth_string);
        $roles = User::getAllRoles();
//        var_dump($roles);
        if ($auth_string === 'all') {
            foreach ($roles as $role) {
                $query = "INSERT INTO roles_in_actions(id_role, action_id) VALUES ('{$role['id']}', '$id');";
                $conn->performQuery($query);
            }
        } else {
            foreach ($roles as $role) {
                if (in_array($role['name'], $auth_roles)) {
                    $query = "INSERT INTO roles_in_actions(id_role, action_id) VALUES ('{$role['id']}', '$id');";
                    $conn->performQuery($query);
                }
            }
        }
    } else {
        $query = "INSERT INTO roles_in_actions(id_role, action_id) VALUES (0, '$id');";
        $conn->performQuery($query);
    }
}

function writeRecursive($method, $parent, $service_name) {
    if (array_key_exists(0, $method)) {
        foreach ($method as $item) {
            // Resolve action name
            $full_action = $parent.$item['resource'].'/';
            if (strlen($full_action) > 2) {
                $writable_action = substr($full_action, 1, -1);
            } else {
                $writable_action = substr($full_action, 0, -1);
            }
            //var_dump($writable_action);
            // Write to db
            writeToDB($writable_action, $item['auth'], $service_name);
            // Call next
            if (isset($item['method'])) writeRecursive($item['method'], $full_action, $service_name);
        }
    } else {
        // Resolve action name
        $full_action = $parent.$method['resource'].'/';
        if (strlen($full_action) > 2) {
            $writable_action = substr($full_action, 1, -1);
        } else {
            $writable_action = substr($full_action, 0, -1);
        }
        //var_dump($writable_action);
        // Write to db
        writeToDB($writable_action, $method['auth'], $service_name);
        // Call next
        if (isset($method['method'])) writeRecursive($method['method'], $full_action, $service_name);
    }
}

$query = "TRUNCATE TABLE system_actions;";
$conn->performQuery($query);
$query = "TRUNCATE TABLE roles_in_actions;";
$conn->performQuery($query);

$services = simplexml_load_file(ROOTDIR.'/config/microservices.xml');
foreach ($services as $item) {
    $url = 'http://'.$item->host.$item->manifest;
    $manifest = $converter->XMLToArray(file_get_contents($url));
    $service_name = $manifest['service_name'];

    // Write all actions to db
    $methods = $manifest['methods']['method'];
    // var_dump($methods);
    writeRecursive($methods, '', $service_name);
    echo "$service_name imported successfully!".PHP_EOL;
}