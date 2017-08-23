<?php
require_once ROOTDIR.'/app/lib/references/schemas/ref_schema.php';
class Reference {

    private $ref_table_name;
    private $action;
    /**
     * @var array ['object_props' => [...], 'method_props' => [...]]
     */
    private $props;
    private $schema = REFERENCE_SCHEMA;

    /**
     * Reference constructor.
     * @param $ref_table_name string Referenced table name
     * @param $action string CRUD action
     * @param array $props Properties
     */
    public function __construct($ref_table_name, $action, $props = []) {
        $this->ref_table_name = $ref_table_name;
        $this->action = $action;
        $this->props = $props;
    }

    /**
     * Invokes a referenced object and returns an output of this object's called method
     *
     * @return mixed Object's method call result
     */
    public function getData() {
        $table_name = $this->ref_table_name;
        $action = $this->action;
        $props = $this->props;
        $action_name = $this->schema[$table_name][$action];
        if (empty($action_name)) {
            ErrorHandler::throwException(UNDEFINED_METHOD, 'page');
        }
        list($action_class, $action_method) = explode('.', $action_name);
        if (method_exists($action_class, 'getInstance')) {
            $instance = $action_class::getInstance(...$props[$table_name]['object_props']);
        } else {
            $instance = new $action_class(...$props[$table_name]['object_props']);
        }
        return $instance->$action_method(...$props[$table_name]['method_props']);
    }

}