<?php
class Router {

    private static $instance;
    private $routes;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Router constructor
     */
    public function __construct() {
        $this->parseRoutesMap();
    }

    /**
     * Parses routes map to property
     */
    private function parseRoutesMap() {
        include_once ROOTDIR.'/config/routes_map.php';
        $this->routes = $routes;
    }

    /**
     * Executes an action mapped to a specific URI
     *
     * @param $uri string Requested URI
     */
    public function executeAction($uri) {
        $parsed_uri = parse_url($uri);
        if (array_key_exists($parsed_uri['path'], $this->routes)) {
            $action = $this->routes[$parsed_uri['path']];
            $method = $action[0];
            if (isset($action['auth'])) {
                if (!$this->checkAuth($action['auth'])) {
                    echo AUTH_ERROR['text'];
                    exit;
                }
            }
        } else {
            readfile(ROOTDIR.'/app/views/404.html');
            exit;
        }
        $controller = Controller::getInstance();
        try {
            echo $controller->$method();
        } catch (AppException $exception) {
            $exception_array = ['code' => $exception->getCode(), 'text' => $exception->getMessage()];
            if ($exception->additional_info !== null) {
                $exception_array = $exception + $exception->additional_info;
            }
            ErrorHandler::renderError($exception_array);
        }
    }

    private function checkAuth($roles) {
        if (Cookie::checkToken()) {
            $token_raw = $_COOKIE['token'];
            $auth = AuthHandler::getInstance();
            if (!$user_id = $auth->check($token_raw)) {
                return false;
            }
            if ($roles != [0]) {
                if (!$auth->checkSelfRoles($roles, $user_id)) {
                    return false;
                }
            }
            Controller::$user_id = $user_id;
            return true;
        } else {
            return false;
        }
    }

}