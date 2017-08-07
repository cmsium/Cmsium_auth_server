<?php
/**
 * Script to replace something in a large file using regexp line by line
 */

$file = 'path_to_file';
$output_file = 'path_to_file';
$regexp = ' ';
$replace_string = ' ';

$handle = fopen($file, 'r');
$handle_out = fopen($output_file, 'w');
if ($handle && $handle_out) {
    while (($line = fgets($handle)) !== false) {
        $replaced_line = mb_ereg_replace($regexp, $replace_string, $line);
        fwrite($handle_out, $replaced_line);
        $line_number = ftell($handle);
        if (($line_number % 1000) == 0) {
            echo($line_number), " lines done\r";
        }
    }
    fclose($handle);
    fclose($handle_out);
} else {
    echo "Error reading file!".PHP_EOL;
}