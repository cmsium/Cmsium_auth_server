<?php
require_once dirname(__DIR__).'/../config/init_libs.php';

$shortopts  = "";
$shortopts .= "p:";  // File path
$shortopts .= "d:"; // Delimiter
$shortopts .= "t:"; // Table
$shortopts .= "c:"; // Columns
$shortopts .= "f:"; // Filter - 'column=>value'
$shortopts .= "o:"; // Output

$options = getopt($shortopts);
$file_path = isset($options['p']) ? $options['p'] : '';
$filter = isset($options['f']) ? explode('=>',$options['f']) : false;
$delimiter = isset($options['d']) ? $options['d'] : ',';
$table = isset($options['t']) ? $options['t'] : 'example';
$columns = isset($options['c']) ? $options['c'] : '';
$output = isset($options['o']) ? $options['o'] : false;

$file = fopen($file_path, "r");
$file_output = fopen($output, "w");
$mask = fgetcsv($file, 0, ";");
$filter_key = array_search($filter[0], $mask);
$columns_array = explode(',', $columns);
do {
    $raw_line = fgetcsv($file, 0, ";");
    $result_line = [];
    if ($raw_line[$filter_key] == $filter[1]) {
        $query_line = "INSERT INTO $table({$columns}) VALUES(";
        foreach ($raw_line as $key=>$value) {
            if (in_array($mask[$key], $columns_array)) {
                $result_line[] = "\"$value\"";
            }
        }
        $query_line .= implode(',', $result_line).");".PHP_EOL;
        var_dump($query_line);
        fwrite($file_output, $query_line);
    }
} while (!feof($file));
fclose($file);
fclose($file_output);