<?php
class DataConverter {

    private static $instance;

    /**
     * Get Instance of DataConverter
     *
     * @return object DataConverter New instance or self
     */
    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {}

    // CSV

    /**
     * Функция получает значение маски из сырых данных в формате CSV
     *
     * @param string $csv Данные в формате CSV
     * @param string $delimiter Разделитель
     * @return array|bool Значение маски в виде массива, иначе false
     */
    private function getMaskCSV($csv, $delimiter) {
        $data = str_getcsv($csv, "\n");
        $mask = explode($delimiter, $data[0]);
        return $mask;
    }

    /**
     * Функция преобразовывает сырые данные в формате CSV в PHP массив для дальнейшей обработки
     *
     * Разделить значений для каждой строки должен иметь вид ";;"
     *
     * @param string $csv Данные в формате CSV
     * @return array|bool Данные в виде массива, иначе false
     */
    private function getCSVContent($csv) {
        $data = str_getcsv($csv, "\n");
        array_shift($data);
        return $data;
    }

    /**
     * Функция преобразовывает данные в формате CSV в именованный массив на основе маски
     *
     * Правильное оформление строки CSV для функции можно найти в документации к данной библиотеке
     *
     * @param string $csv Данные в формате CSV
     * @param string $delimiter Опциональный разделитель, по умолчанию ";;"
     * @return array|bool Возвращает массив данных, иначе false
     */
    public function CSVToArray($csv, $delimiter = ";;") {
        $mask = $this->getMaskCSV($csv, $delimiter);
        $data = $this->getCSVContent($csv);
        foreach($data as $line) {
            $row = explode($delimiter, $line);
            foreach ($row as $index=>$value) {
                $resulted_row[$mask[$index]] = $value;
            }
            $result[] = $resulted_row;
        }
        return $result;
    }

    // JSON

    /**
     * Функция получает значение маски из сырых данных в формате JSON
     *
     * @param string $json Данные в формате JSON
     * @return array|bool Значение маски в виде массива, иначе false
     */
    private function getMaskJSON($json) {
        $data = json_decode($json, true);
        $root = array_keys($data)[0];
        $mask = $data[$root][0];
        return $mask;
    }

    /**
     * Функция преобразовывает сырые данные в формате JSON в PHP массив для дальнейшей обработки
     *
     * @param string $json Данные в формате JSON
     * @return array|bool Данные в виде массива, иначе false
     */
    private function getJSONContent($json) {
        $data = json_decode($json, true);
        $root = array_keys($data)[0];
        $data_array = $data[$root];
        array_shift($data_array);
        return $data_array;
    }

    /**
     * Функция преобразовывает строку JSON в формат именнованого PHP массива
     *
     * @param string $json Данные в формате JSON
     * @return array|bool Возвращает массив данных, иначе false
     */
    public function JSONToArray($json) {
        return json_decode($json, true);
    }

    /**
     * Функция преобразовывает данные в формате JSON в именованный массив на основе маски
     *
     * Правильное оформление строки JSON для функции можно найти в документации к данной библиотеке
     *
     * @param string $json Данные в формате JSON
     * @return array|bool Возвращает массив данных, иначе false
     */
    public function listJSONToArray($json) {
        $mask = $this->getMaskJSON($json);
        $data = $this->getJSONContent($json);
        foreach ($data as $row) {
            foreach ($row as $index=>$value) {
                $resulted_row[$mask[$index]] = $value;
            }
            $result[] = $resulted_row;
        }
        return $result;
    }

    // XML

    /**
     * Функция представляет строку XML в виде DOM объекта
     *
     * @param string $xml Данные в формате XML
     * @return DOMElement Объект root-элемента
     */
    private function getXMLRootNode($xml) {
        $object = new DOMDocument();
        $object->loadXML($xml);
        $root = $object->documentElement;
        return $root;
    }

    /**
     * Рекурсивная функция для составления именованного массива из DOM объекта
     *
     * @param DOMElement $root Объект root-элемента
     * @return array|mixed Массив данных, либо объект для дальнейших итераций
     */
    private function XMLObjectToArray($root) {
        $result = [];

        if ($root->hasChildNodes()) {
            $children = $root->childNodes;
            if ($children->length == 1) {
                $child = $children->item(0);
                if ($child->nodeType == XML_TEXT_NODE) {
                    $result['_value'] = $child->nodeValue;
                    return count($result) == 1 ? $result['_value'] : $result;
                }
            }
            $groups = [];
            foreach ($children as $child) {
                if ($child->nodeType == XML_ELEMENT_NODE) {
                    if (!isset($result[$child->nodeName])) {
                        $result[$child->nodeName] = $this->XMLObjectToArray($child);
                    } else {
                        if (!isset($groups[$child->nodeName])) {
                            $result[$child->nodeName] = array($result[$child->nodeName]);
                            $groups[$child->nodeName] = 1;
                        }
                        $result[$child->nodeName][] = $this->XMLObjectToArray($child);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Функция преобразовывает данные в формате XML в именованный массив
     *
     * @param string $xml Данные в формате XML
     * @return array|bool Возвращает массив данных, иначе false
     */
    function XMLToArray($xml) {
        $root = $this->getXMLRootNode($xml);
        $result = $this->XMLObjectToArray($root);
        return $result;
    }

    // Array to something

    /**
     * Функция преобразовывает данные из массива в формат JSON
     *
     * @param array $content Исходный массив данных
     * @return string|bool Данные в JSON формате, либо false
     */
    public function arrayToJSON($content) {
        return json_encode($content,JSON_UNESCAPED_UNICODE);
    }

    private function simpleXMLObjectToXML($array, &$base,$keys) {
        foreach($array as $key => $value) {
            if(is_array($value)) {
                if(!is_numeric($key)){
                    $subnode = $base->addChild("$key");
                    $this->simpleXMLObjectToXML($value, $subnode,$keys);
                }else{
                    if ($keys)
                        $subnode = $base->addChild("item$key");
                    else
                        $subnode = $base->addChild("item");
                    $this->simpleXMLObjectToXML($value, $subnode,$keys);
                }
            }else {
                if(!is_numeric($key)){
                    $base->addChild("$key","$value");
                }else{
                    if ($keys)
                       $base->addChild("item$key","$value");
                    else
                        $base->addChild("item","$value");
                }
            }
        }
    }

    public function arrayToXML($array, $root = false,$keys=true) {
        if ($root) {
            $starter = "<$root></$root>";
        } else {
            $starter = "<root></root>";
        }
        $base = new SimpleXMLElement($starter);
        $this->simpleXMLObjectToXML($array, $base,$keys);
        return $base->asXML();
    }

    /**
     * Функция преобразует массив данных в CSV
     *
     * @param array $array Исходный массив данных
     * @return string Строка в CSV
     */
    public function arrayToCSV($array) {
        $result = '';
        foreach ($array as $row) {
            $result = $result.implode(";;", $row)."\n";
        }
        return $result;
    }

// ADDITIONAL FUNCTIONS

    /**
     * Функция преобразовывает данные из формата JSON в формат XML
     *
     * @param string $json Строка, содержащая данные в JSON формате
     * @return  string|bool Данные в XML, либо false
     */
    public function JSONToXML($json) {
        $options = array(
            "addDecl"   => true,
            "encoding"  => "UTF-8",
            "indent"    => '  ',
            "rootName"  => 'root'
        );
        $serializer = new XML_Serializer($options);
        $object = json_decode($json);

        if ($serializer->serialize($object)) {
            return $serializer->getSerializedData();
        } else {
            return false;
        }
    }

    /**
     * Функция преобразовывает данные из формата JSON в формат CSV
     *
     * Многоуровневые JSON строки не поддерживаются
     *
     * @param string $json Строка, содержащая данные в JSON формате
     * @return bool|string Данные в CSV, либо false
     */
    public function JSONToCSV($json) {
        if (!$object = json_decode($json, true)) {
            return false;
        }

        $line = "";
        foreach ($object as $row) {
            $line = $line.join(",", $row)."\n";
        }
        return $line;
    }

    /**
     * Функция преобразовывает данные из формата JSON в формат CSV
     *
     * Многоуровневые XML узлы не поддерживаются
     *
     * @param string $xml трока, содержащая данные в XML формате
     * @return bool|string Данные в CSV, либо false
     */
    public function XMLToCSV($xml) {
        if (!$object = simplexml_load_string($xml)) {
            return false;
        }

        $line = "";
        foreach ($object->children() as $item)
        {
            $line = $line.join(",", get_object_vars($item))."\n";
        }
        return $line;
    }

    public function __destruct() {}

}