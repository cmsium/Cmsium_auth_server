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
        if (array_key_exists($uri, $this->routes)) {
            $action = $this->routes[$uri];
        } else {
            readfile(ROOTDIR.'/app/views/404.html');
            exit;
        }
        $controller = Controller::getInstance();
        echo $controller->$action();
    }

}