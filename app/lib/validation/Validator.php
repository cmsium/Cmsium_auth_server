<?php

class Validator {

//Функции проверки перед валидацией    
//------------------------------------
    private static $instance;
    private $errors;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct(){}
    protected function __clone(){}


    /**
     * @return mixed
     */
    public function getErrors(){
        return $this->errors;
    }
    /**
     *Валидирует массив данных из массива по маске
     *
     * @param array $mask Маска;
     * @param array $array Массив для валидации;
     *
     * @return array|false Отвалидированный массив;
     */
    public function ValidateByMask($array, $mask)
    {
        Masks::getInstance();
        $mask = Masks::getMask($mask);
        $data = array();
        foreach ($mask as $key => $value) {
            $res = $this->Validate($array, $key, array('func' => $this->GetFuncFromMask($key, $mask),
                'props' => $this->GetPropsFromMask($key, $mask),
                'required' => $this->GetReqsFromMask($key, $mask)));
            if ($res === false) {
                ErrorHandler::throwException(DATA_FORMAT_ERROR,"page");
                return false;
            }
            $data[$key] = $res;
        }
        return $data;
    }


    /**
     *Валидирует ВЕСЬ! массив данных из массива по маске
     *
     * @param array $mask Маска;
     * @param array $array Массив для валидации;
     *
     * @return array|false Отвалидированный массив;
     */
    public function ValidateAllByMask($array, $mask)
    {
        Masks::getInstance();
        $mask = Masks::getMask($mask);
        $data = [];
        $status = true;
        foreach ($mask as $key => $value) {
            $res = $this->Validate($array, $key, array('func' => $this->GetFuncFromMask($key, $mask),
                'props' => $this->GetPropsFromMask($key, $mask),
                'required' => $this->GetReqsFromMask($key, $mask)));
            if ($res === false) {
                $status = false;
                $this->errors[] = $key;
            } else {
                if ($res !== 0x539)
                    $data[$key] = $res;
            }
        }
        if (!$status)
            return false;
        return $data;
    }


    /**
     *Валидирует ВЕСЬ! массив данных из массива по динамически созданной маске
     *
     * @param array $table_model Имя таблицы
     * @param array $array Массив для валидации
     * @param array $blacklist Черный список значений для валидации
     *
     * @return array|false Отвалидированный массив
     */
    public function ValidateByDynamicMask($array,array $table_model,$blacklist,$static_mask = false)
    {
        Masks::getInstance();
        $data = [];
        $status = true;
        $mask = $this->buildMask($table_model,$blacklist);
        if ($static_mask) {
            if (is_array($static_mask))
                $mask = array_merge($mask, $static_mask);
            else
                $mask = array_merge($mask, Masks::getMask($static_mask));
        }
        foreach ($mask as $key => $value) {
            $res = $this->Validate($array, $key, array('func' => $this->GetFuncFromMask($key, $mask),
                'props' => $this->GetPropsFromMask($key, $mask),
                'required' => $this->GetReqsFromMask($key, $mask)));
            if ($res === false) {
                $status = false;
                $this->errors[] = $key;
            } else {
                if ($res !== 0x539)
                    $data[$key] = $res;
            }
        }
        if (!$status){
            return false;
        }
        return $data;
    }

    /**
     * Отвалидировать параметр
     *
     * @param array $array Массив для валидации
     * @param string $paramName Имя валидируемого параметра
     * @param array $paramProps Параметры типа
     *
     * @return mixed|NULL Отвалидированный параметр
     */
    public function Validate($array, $paramName, $paramProps){
        if (isset($array[$paramName])) {
            if (empty($array[$paramName]) && $array[$paramName] !== '0') {
                if ($paramProps['required']) {
                    return false;
                } else {
                    if (isset($paramProps['props']['unrequired_output']))
                        return $paramProps['props']['unrequired_output'];
                    else {
                        return NULL;
                    }
                }
            }
            $check = $this->Check($paramProps['func'],
                $array[$paramName],
                $paramProps['props']);
            if ($check === false) {
                return false;
            }
            return $check;
        } elseif (!$paramProps['required']){
            if (isset($paramProps['props']['unrequired_output']))
                return $paramProps['props']['unrequired_output'];
            else
                return 0x539;
        }
        else {
            if (isset($paramProps['props']['unrequired_output']))
                return $paramProps['props']['unrequired_output'];
            else
                return false;
        }
    }

    public function buildMask(array $table_model, $blacklist){
        $mask = [];
        foreach ($table_model as $value){
            if (!in_array($value['column_name'],$blacklist)) {
                $data_type = $this->getColumnData($value['column_type'])['data_type'];
                $props = $this->getColumnData($value['column_type'])['props'];
                if (isset($value['is_nullable']))
                    $is_null = $value['is_nullable'];
                else
                    $is_null = "YES";
                $mask[$value['column_name']] = ['func' => $this->getFuncNameFromType($data_type),
                    'props' => $this->getPropsFromType($data_type, $props),
                    'required' => $this->getReqsFromType($is_null)];
            }
        }
        return $mask;
    }

    public function getColumnData ($column_data){
        $column_array = explode('(',$column_data);
        $data_type = $column_array[0];
        if (isset($column_array[1])) {
            $data_props = str_replace(array("'",'"',")"),'',$column_array[1]);
            if (is_numeric($data_props))
                $props = (INT)$data_props;
            else {
                $props = explode(',',$data_props);
            }
        }
        else
            $props = NULL;
        return ['data_type'=>$data_type, 'props'=>$props];
    }

    public function getFuncNameFromType($data_type){
        switch ($data_type){
            case "varchar": $func = 'CirrLatName';break;
            case "text": $func = 'Text';break;
            case "int": $func = 'StrNumbers';break;
            case "float": $func = 'StrFloat';break;
            case "decimal": $func = 'StrFloat';break;
            case "tinyint": $func = 'tinyint';break;
            case "datetime": $func = 'DateType';break;
            case "date": $func = 'DateType';break;
            case "enum": $func = 'ValueFromList';break;
            case "set": $func = 'MultipleList';break;
            default: ErrorHandler::throwException(UNSUPPORTED_DATA_TYPE);
        }
        return $func;
    }


    public function getPropsFromType($data_type,$column_props){
        switch ($data_type){
            case "varchar": $props['min'] = 3; $props['max'] = $column_props;break;
            case "text": $props['min'] = 3; $props['max'] = $column_props;$props['except'] = '\<\>\*=\+';break;
            case "int": $props['min'] = 1; $props['max'] = $column_props; $props['output'] = 'int';break;
            case "float": $props['min'] = 1; $props['max'] = $column_props[0];$props['dec_max'] = $column_props[1];break;
            case "decimal": $props['min'] = 1; $props['max'] = $column_props[0];$props['dec_max'] = $column_props[1];break;
            case "tinyint": $props['output'] = 'int';$props['unrequired_output'] = 0;break;
            case "datetime": $props['format'] = 'Y-m-d H:i:s';break;
            case "date": $props['format'] = 'Y-m-d';break;
            case "enum": $props['list'] = $column_props;break;
            case "set": $props['list'] = $column_props;break;
            default: ErrorHandler::throwException(UNSUPPORTED_DATA_TYPE);
        }
        return $props;
    }


    public function getReqsFromType($req_flag){
        switch ($req_flag){
            case "YES": return false;break;
            case "NO": return true;break;
            default: return true;
        }
    }


    /**
     * Прогоняет через нужную валидацию
     *
     * @param string $type Тип
     * @param string $value Значение
     * @param array $props Дополнительные параметры
     *
     * @return mixed|false Валидированое значение
     */
    public  function Check($type, $value, $props){
        $types = Types::getInstance();
        return @$types::$type($value, $props);
    }


    /**
     *Получить параметры типа по маске
     *
     * @param string $type Тип;
     * @param array $mask Маска;
     *
     * @return array Параметры типа;
     */
    public function GetPropsFromMask($type, $mask)
    {
        $props = array();
        if (empty($mask[$type]) or !isset($mask[$type]['props']))
            return $props;
        foreach ($mask[$type]['props'] as $key => $value) {
            $props[$key] = $value;
        }
        return $props;
    }


    /**
     *Получить параметры обязательности по маске
     *
     * @param string $type Тип;
     * @param array $mask Маска;
     *
     * @return boolean Флаг обязательности;
     */
    public function GetReqsFromMask($type, $mask)
    {
        if (empty($mask[$type]) or !isset($mask[$type]['required'])) {
            return false;
        }
        return $mask[$type]['required'];
    }


    /**
     *Получить функцию валидации по маске
     *
     * @param string $Type Тип;
     * @param array $mask Маска;
     *
     * @return array Набор функций для валидации;
     */
    public function GetFuncFromMask($type, $mask)
    {
        $func = "";
        if (empty($mask[$type]) or !isset($mask[$type]['func'])) {
            return $func;
        }
        return $mask[$type]['func'];
    }
}
?>