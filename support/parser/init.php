<?php
const ROOT = __DIR__;
require_once ROOT."/../../app/modules/converters/classes/DataConverter.php";
require_once ROOT."/ParserHandler.php";

spl_autoload_register(function ($class){
        require_once ROOT."/parsers/$class.php";
});
$obj = new ParserHandler();
$obj->parse();