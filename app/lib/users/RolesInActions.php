<?php
class RolesInActions implements ActionsPermissions {
    public static $default_permissions = ["1"=>['81c14e033d86fbd0b06c2b76c6a840d8','e3eeba0290bf1e675fd6e2f91eb459b2','59c00e13fe05a1ab60d6c634ecdeaeb5']];
    private static $instance;


    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct(){}
    private function __clone(){}


    /**
     * Check permissions for action
     *
     * @param string $action Current action
     * @return bool Check permissions status
     */
    public function check($action){
        return self::checkActionPermissions($action);
    }


    /**
     * Get allowed to action roles list
     *
     * @param string $action Current action
     * @return array|bool Roles array
     */
    public static function getRoles ($action){
        $conn = DBConnection::getInstance();
        $query = "CALL getActionRoles('$action');";
        $result =  $conn->performQueryFetch($query);
        if (!$result)
            return false;
        return explode(',',$result['roles']);
    }

    /**
     * Check permissions for action attached to the page
     * @param string $link page URL
     * @return bool Permissions check status
     */
    public static function checkLinkPermissions($link){
        $page = @end(explode('/',$link));
        global $URLstructure;
        $page_model = $URLstructure[$page];
        if (isset($page_model['action'])){
            return self::checkActionPermissions($page_model['action']);
        } else
            return true;
    }

    /**
     * Call custom check action permissions function
     *
     * @param string $action Current action
     * @return bool Check permissions status
     */
    public static function checkActionPermissions($action){
        $roles = self::getRoles($action);
        $class = AUTH_CUSTOM_CLASS;
        $auth = $class::getInstance();
        return $auth->checkSelfRoles($roles);
    }


    /**
     * Attach allowed actions to role
     *
     * @param string $role_id Current role id
     * @param array $actions Allowed actions
     * @return bool Attaching status
     */
    public static function attachActionsToRole($role_id, array $actions){
        $conn = DBConnection::getInstance();
        foreach ($actions as $action_id){
            $query = "CALL addRoleToAction('$role_id','$action_id');";
            $conn->performQuery($query);
        }
        return true;
    }

    /**
     * Delete all permissions for current role
     *
     * @param string $role_id Current role id
     * @return mixed Deleting status
     */
    public static function deleteAllActionsFromRole($role_id){
        $conn = DBConnection::getInstance();
        $query = "CALL deleteAllActionsFromRole($role_id);";
        return $conn->performQuery($query);
    }

    /**
     * Detach allowed actions from role
     *
     * @param string $role_id Current role id
     * @param array $actions Allowed actions
     * @return bool Detaching status
     */
    public static function detachActionsFromRole($role_id,$actions){
        $conn = DBConnection::getInstance();
        foreach ($actions as $action_id){
            if (isset(self::$default_permissions[$role_id]))
                if (in_array($action_id,self::$default_permissions[$role_id]))
                    continue;
            $query = "CALL deleteRoleFromAction('$role_id','$action_id');";
            $conn->performQuery($query);
        }
        return true;
    }

}