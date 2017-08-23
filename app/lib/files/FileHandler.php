<?php
class FileHandler{

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


    /**
     * Execute requested action with parameters
     *
     * @param array $path URL path array (controller/action/file_id)
     * @return array Result page params
     */
    public function check($path){
        try {
            if (isset($path[2])) {
                $auth = AuthHandler::getInstance();
                $auth->check();
                $action = $path[2];
                $parameter = NULL;
                if (isset($path[3]))
                    $parameter = $path[3];
                $file_instance = FileActions::getInstance();
                if (!method_exists($file_instance, $action)) {
                    ErrorHandler::throwException(UNDEFINED_METHOD, "page");
                }
                return $file_instance->$action($parameter);
            }
        } catch (Exception $e){
            return ErrorHandler::errorMassage($e);
        }
    }

}