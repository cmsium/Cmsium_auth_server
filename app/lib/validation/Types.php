<?php

class Types{
//Функции валидаций разных типов
//--------------------------------
    private static $instance;

    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct(){}
    protected function __clone(){}

    /**
     *Проверить на соответствие шаблону
     *
     * @param string $pattern Шаблон;
     * @param string $value Проверяемая строка;
     * @param array &$matches Массив для совпадений
     *
     * @return string|false Валидированное значение;
     */
    public static function Preg($pattern, $value, &$matches = null){
        if (preg_match($pattern, $value, $matches))
            return $value;
        return false;
    }

    public static function sanitize ($value){
        $value = trim($value);
        $value = strip_tags($value);
        $value = stripcslashes($value);
        $value = htmlspecialchars($value);
        return $value;
    }

    /**
     *Проверить на соответствие шаблону
     *
     * @param string $pattern Шаблон;
     * @param string $value Проверяемая строка;
     * @param array &$matches Массив для совпадений
     *
     * @return string|false Валидированное значение;
     */
    public static function PregALL($pattern, $value,&$matches)
    {
        if (preg_match_all($pattern, $value,$matches))
            return $value;
        return false;
    }


    /**
     *Проверяет как integer
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function Int($value, $props)
    {
        $res = is_integer($value);
        if (!$res)
            return false;
        return $value;
    }

    /**
     *Проверяет как беззнаковый int
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function UnsignedInt($value, $props)
    {
        $res = self::Int($value, $props);
        if ($res)
            if ($res >= 0)
                return $res;
        return false;
    }


    /**
     *Ппроверяет как int в заданном диапозоне
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function RangedInt($value, $props)
    {
        $value = self::Int($value, $props);
        if ($value >= $props['min']
            and $value <= $props['max']
        )
            return $value;
        return false;
    }


    /**
     *Проверяет int на совпадение из списка
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function IntFromList($value, $props)
    {
        $res = self::Int($value, $props);
        if ($res)
            return self::ValueFromList($res, $props);
        return false;
    }


    /**
     *Проверяет на boolean
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function Bool($value, $props)
    {
        if ($value === "true" or $value === 1 or $value === '1')
            return true;
        return false;
    }

    public static function tinyint($value, $props){
        $pattern = "/^[0|1]{1}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'int':
                    return (INT)$value;
                default:
                    return $value;
            }
        return false;
    }

    /**
     *Проверяет имя латинскими буквами заданной длинны
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function LatinName($value, $props){
        if ($props['max'] == null)
            $props['max'] = 32;
        $pattern = "/^[a-zA-z_]{{$props['min']},{$props['max']}}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;
    }


    /**
     * Валидиркет список SQL моделей данных ((model);(model)...)
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     * @return mixed false|Массив совпадений валидации
     */
    public static function SQLDataModelList($value, $props) {
        $groups = explode(';',$value);
        $matches = [];
        foreach ($groups as $model_value) {
            $result = self::SQLDataModel($model_value, $props);
            if (!$result) {
                return false;
            }
            $matches[] = $result;
        }
        return $matches;
    }


    /**
     * Валидирует SQL модель данных (имя поля => тип поля)
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     * @return mixed false|Массив совпадений
     */
    public static function SQLDataModel($value, $props){
        $data_type_whitelist = implode('|', $props['allowed_types']);
        $model_items = explode('|', substr($value, 1, -1));
        $pattern_col_name = '/^[\dА-Яа-я\w\s\$]{2,64}$/iu';
        $pattern_col_type = '/^(?:'.$data_type_whitelist.')(?:\([\dА-Яа-я\w\'\\",\-\s]*\))?$/iu';
        if (!self::Preg($pattern_col_name, $model_items[0]) || !self::Preg($pattern_col_type, $model_items[1])) {
            return false;
        }
        $result = [0 => $value, 1 => $model_items[0], 2 => $model_items[1]];
        foreach (array_slice($model_items, 2) as $item) {
            $regex_result = self::ValueFromRegexList($item, ['list' => $props['model_items']]);
            if ($regex_result) {
                $result[$regex_result['key'] + 3] = $item;
            } else {
                return false;
            }
        }
        $highest_key = max(array_keys($result));
        for ($i = 0; $i <= $highest_key; $i++) {
            if (!isset($result[$i])) {
                $result[$i] = '';
            }
        }
        ksort($result);
        return $result;
    }

    public static function ListOfAlphaNumerics($value, $props) {
        if ($props['max'] == null)
            $props['max'] = 32;
        $pattern = "/^[\dА-Яа-я\w\s\-\,]{{$props['min']},{$props['max']}}$/u";
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $item) {
            if (!self::Preg($pattern, $item)) {
                return false;
            }
        }
        return $value;
    }

    public static function ListOfAlphaNumericsRecursive($value, $props) {
        if ($props['max'] == null)
            $props['max'] = 32;
        $pattern = "/^[\dА-Яа-я\w\s\-\,]{{$props['min']},{$props['max']}}$/u";
        if (!is_array($value)) {
            return false;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($value));
        foreach($iterator as $key => $item) {
            if (!self::Preg($pattern, $item)) {
                return false;
            }
        }
        return $value;
    }


    /**
     *Проверяет имя файла определённых типов заданной длинны
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function fileName($value, $props)
    {
        if ($props['max'] == null)
            $props['max'] = 255;
        $types = implode("|",$props['types']);
        $pattern = "/^[А-Яа-я\w\d\s_-]{{$props['min']},{$props['max']}}\.({$types})$/u";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;
    }


    /**
     *Проверяет тип файла заданной длинны
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function fileType($value, $props)
    {
        if ($props['max'] == null)
            $props['max'] = 64;
        $pattern = "/^[\w\/-]{{$props['min']},{$props['max']}}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;
    }


    /**
     *Проверяет имя латинскими буквами или киррилицей
     *(начиная с буквы), заданной длинны
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function CirrLatName($value, $props)
    {
        if ($props['max'] == null)
            $props['max'] = 32;
        $pattern = "@^[а-яА-ЯёЁa-zA-Z\d\-\s\,\.\\\/\_]{{$props['min']},{$props['max']}}$@u";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;
    }


    /**
     *Проверяет текст заданной длинны
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function Text($value, $props)
    {
        if ($props['max'] == null)
            $props['max'] = 511;
        $value = self::sanitize($value);
        $pattern = "@^([^{$props['except']}]){{$props['min']},{$props['max']}}$@u";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;

    }


    /**
     *Проверяет как цифры и буквы заданной длинны
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function AlphaNumeric($value, $props)
    {
        if ($props['max'] == null)
            $props['max'] = 32;
        $pattern = "/^[\w\d-_]{{$props['min']},{$props['max']}}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;

    }


    /**
     * Проверяет как md5 хэш
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function Md5Type($value, $props)
    {
        $pattern = "/^[\da-f]{32}$/i";
        if (self::Preg($pattern, $value)) {
            $value = strtolower($value);
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;

                default:
                    return $value;
            }
        }
        return false;

    }

    public static function multiple($value, $props){
        foreach ($value as $item){
            if (isset($props['func'])) {
                $method = $props['func'];
                $result = self::$method($item,$props);
                if ($result === false)
                    return false;
            }
        }
        return $value;
    }

    /**
     *Проверяет IPv4 адрес
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function IPv4($value, $props)
    {
        $pattern = "/^([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})\.([\d]{1,3})(\/([\d]{1,3}))?$/";
        if (!self::Preg($pattern, $value))
            return false;
        $res = preg_split("/\./", $value);
        foreach ($res as &$groupValue) {
            $groupValue = (INT)$groupValue;
            if ($groupValue < 0 or
                $groupValue > 255
            )
                return false;
        }
        switch ($props['output']) {
            case 'string':
                return $value;
                break;
            case 'binary':
                $tc = TypesConverts::getInstance();
                return $tc->StrToBinS($value);
                break;
            case 'md5':
                return md5($value);
                break;
            case 'int':
                $tc = TypesConverts::getInstance();
                return $tc->IPv4toInt($value);
            default:
                return $value;

        }
    }


    /**
     *Проверяет IPv4integer адрес
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function IPv4Int($value, $props)
    {
        $pattern = "/^[\d]{0,10}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    $tc = TypesConverts::getInstance();
                    return $tc->IntToIPv4($value);
                    break;
                default:
                    return $value;
            }
        return false;

    }


    /**
     *Проверяет URL
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function URL($value, $props)
    {
        if (filter_var($value, FILTER_VALIDATE_URL))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;

    }


    /**
     *Проверяет e-mail
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function E_Mail($value, $props)
    {
        $pattern = "/^([\w\dа-яА-Я-\.?]{1,})\@([\w\d-\.?]{1,})$/u";
        if (self::Preg($pattern, $value)) {
            $value = strtolower($value);
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        }
        return false;

    }


    /**
     *Проверяет как послледовательность цифр
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function StrNumbers($value, $props)
    {
        if ($props['max'] == null)
            $props['max'] = 11;
        $pattern = "/^\d{{$props['min']},{$props['max']}}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;
                case 'int':
                    return (INT)$value;
                    break;
                default:
                    return $value;
            }
        return false;
    }

    /**
     *Проверяет как послледовательность float цифр
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function StrFloat($value, $props)
    {
        if ($props['max'] == null)
            $props['max'] = 11;
        $pattern = "/^[+-]?[0-9]{{$props['min']},{$props['max']}}[.]?[0-9]{1,{$props['dec_max']}}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'md5':
                    return md5($value);
                    break;
                default:
                    return $value;
            }
        return false;
    }

    /**
     * Check matching to one of the regexps from list
     * List item can have regexp syntax
     *
     * @param $value string Value to check
     * @param $props array Type parameters
     * @return array|false
     */
    public static function ValueFromRegexList($value, $props) {
        foreach($props['list'] as $key => $regex_item) {
            $pattern = "/$regex_item/iu";
            if (self::Preg($pattern, $value)) {
                return ['key' => $key, $value];
            }
        }
        return false;
    }

    /**
     *Проверяет на совпадение со строкой из списка
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function ValueFromList($value, $props)
    {
        if (!isset($props['list']))
            return false;
        if (in_array($value, $props['list']))
            return $value;
        return false;
    }


    public static function MultipleList($value, $props)
    {
        foreach ($value as $element) {
            if (!self::ValueFromList($element, $props))
                return false;
        }
        return implode(',',$value);
    }

    /**
     *Проверяет дату
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function DateType($value, $props)
    {
        $date = date_create_from_format($props['format'], $value);
        if ($date)
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'int':
                    return date_timestamp_get($date);
                    break;
                default:
                    return $value;
            }
        return false;
    }

    public static function Login($value, $props){
        if ($props['max'] == null)
            $props['max'] = 32;
        $pattern = "/^[\w\d\.@]{{$props['min']},{$props['max']}}$/";
        if (self::Preg($pattern, $value))
            switch ($props['output']) {
                case 'string':
                    return $value;
                    break;
                case 'binary':
                    $tc = TypesConverts::getInstance();
                    return $tc->StrToBinS($value);
                    break;
                case 'md5':
                    return md5($value);
                    break;

                default:
                    return $value;
            }
        return false;
    }

    public static function Roles($value, $props)
    {
        foreach ($value as $role) {
            if (!self::ValueFromList($role, $props))
                return false;
        }
        return $value;
    }

    /**
     *проверяет как пользовательский тип
     *
     * @param string $value Проверяемая строка;
     * @param array $props Параметры типа;
     *
     * @return string|false Валидированное значение;
     */
    public static function Custom($value, $props)
    {
        if (isset($props['name']))
            return $props['name']($value, $props);
        ErrorHandler::throwException(UNDEFINED_CUSTOM_NAME);
        return false;
    }
}
?>