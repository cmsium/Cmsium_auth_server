<?php
class SystemActions{

    public static $action_permissions_class = ACTION_PERMISSIONS_CLASS;


    /**
     * Check permissions for current action
     *
     * @param string $action Current action
     */
    public static function checkActionPermissions($action){
        $custom_class = self::$action_permissions_class;
        if (!class_exists($custom_class)){
            ErrorHandler::throwException(CUSTOM_CLASS_ERROR);
        }
        if (!in_array("ActionsPermissions",class_implements($custom_class)))
            ErrorHandler::throwException(CUSTOM_CLASS_ERROR);
        $class_instance = $custom_class::getInstance();
        if (!$class_instance->check($action))
            ErrorHandler::throwException(PERMISSIONS_ERROR,'page');
    }


    /**
     * Check action existance in system actions directory
     *
     * @param string $action_name Action name
     * @return bool Existance status
     */
    public static function checkAction($action_name){
        $conn = DBConnection::getInstance();
        $query = "CALL checkAction($action_name);";
        $result =  $conn->performQueryFetch($query);
        if (!$result)
            return false;
        return $result['action_id'];
    }

    /**
     * Get action id of action (or create new action if it does not exist)
     *
     * @param string $action_name Current action name
     * @return bool
     */
    public static function ActionHandle($action_name){
        $action_id = self::checkAction($action_name);
        if (!$action_id){
            self::createNewAction($action_name);
            $action_id = self::checkAction($action_name);
        }
        return $action_id;
    }

    /**
     * Create new action in system actions directory
     *
     * @param string $name New action name
     * @return bool Creating status
     */
    public static function createNewAction($name){
        $conn = DBConnection::getInstance();
        $query = "CALL createNewAction($name);";
        $conn->performQuery($query);
        return true;
    }

    /**
     * Get all system actions list
     *
     * @param bool $id Return only actions id flag
     * @return array All actions data array
     */
    public static function getAllActions($id=false){
        $conn = DBConnection::getInstance();
        $query = "CALL getAllActions();";
        $result = $conn->performQueryFetchAll($query);
        if ($id){
            $actions_id = [];
            foreach ($result as $value){
                $actions_id[] = $value['action_id'];
            }
            return $actions_id;
        } else
            return $result;
    }

    /**
     * Delete all actions from system
     *
     * @return bool Deleting status
     */
    public static function deletAllActions (){
        $conn = DBConnection::getInstance();
        $query = "CALL deleteAllActions();";
        return $conn->performQuery($query);
    }

    /**
     * Count all system actions
     * @return array actions number
     */
    public static function getSystemEventsCount(){
        $conn = DBConnection::getInstance();
        $query = "CALL getSystemEventsCount();";
        return $conn->performQueryFetch($query);
    }


    /**
     * Get system events by filter
     *
     * @param array $actions Needed actions list
     * @param int $start Start focus
     * @param int $offset Offset focus
     * @return array|mixed Requested events data array
     */
    public static function getSystemEvents(array $actions,$search_columns, $start, $offset,$table = 'system_log'){
        $query_where=[];
        $column_values =[];
        $actions_query = [];
        foreach ($actions as $action_id){
            $actions_query[] = "$table.action=?";
            $column_values[]=$action_id;
        }
        foreach ($search_columns as $key => $value){
            if ($value) {
                $result = FilterTypes::$key($value,$table);
                $query_where[] = $result[0];
                $column_values = array_merge($column_values, $result[1]);
            }
        }
        $actions_query = implode(' OR ',$actions_query);
        if (!empty($query_where))
            $result_where = "AND ".implode(' AND ', $query_where);
        else
            $result_where = "";
        $conn = DBConnection::getInstance();
        $query = "SELECT SQL_CALC_FOUND_ROWS * FROM $table 
                  LEFT JOIN system_actions ON $table.action = system_actions.action_id 
                  LEFT JOIN user_properties ON $table.user_id = user_properties.user_id 
                  WHERE ($actions_query) $result_where 
                  ORDER BY $table.created_at DESC LIMIT $start,$offset;";
        $result = $conn->performPreparedQueryFetchAll($query, $column_values);
        if (!$result)
            return [];
        return $result;
    }

}