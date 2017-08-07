<?php
require_once dirname(__DIR__).'/../config/init_libs.php';
foreach (glob(ROOTDIR.'/app/modules/*/classes/*.php') as $require_path) {
    require_once $require_path;
}

$shortopts  = "";
$shortopts .= "f:";  // File path
$shortopts .= "d:"; // Delimiter
$shortopts .= "t:"; // Table
$shortopts .= "c:"; // Columns
$shortopts .= "o:"; // Output

$options = getopt($shortopts);
$file_path = isset($options['f']) ? $options['f'] : '';
$delimiter = isset($options['d']) ? $options['d'] : ',';
$table = isset($options['t']) ? $options['t'] : 'example';
$columns = isset($options['c']) ? $options['c'] : '';
$output = isset($options['o']) ? $options['o'] : false;

$converter = DataConverter::getInstance();
$file = new File($file_path);
$contents = $file->getContent();
$array_csv = $converter->CSVToArray($contents, $delimiter);
$query = '';

$columns_array = explode(',', $columns);
foreach ($array_csv as $item) {
    $inner_query = "INSERT INTO $table({$columns}) VALUES(";
    $values= [];
    foreach ($columns_array as $column_name) {
        $values[] = "\"{$item[$column_name]}\"";
    }
    $inner_query .= implode(',',$values).');'.PHP_EOL;
    $query .= $inner_query;
}
if ($output) {
    $file_output = new File($output);
    $file_output->write($query);
} else {
    echo $query;
}