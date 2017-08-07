<?php

require dirname(__DIR__).'/../config/init_libs.php';
foreach (glob(ROOTDIR.'/app/modules/*/interfaces/*.php') as $require_path) {
    require_once $require_path;
}
foreach (glob(ROOTDIR.'/app/modules/*/classes/*.php') as $require_path) {
    require_once $require_path;
}
    require REQUIRES;
    require ROOTDIR."/app/URLmodel.php";

function modelToXML(){
    $xml = new DOMDocument;
    $xml->formatOutput = true;
    $root = $xml->createElement("urls");
    createTree($xml,"0",$root);
    $xml->appendChild($root);
    $xml->save(ROOTDIR.'/app/URLmodel.xml');
}

function createTree($xml,$parent_name,$parent_node){
    global $URLstructure;
    foreach ($URLstructure as $key => $value) {
        if ($value['parent'] == $parent_name) {
            $url = $xml->createElement("url");
            $url->setAttribute("value", $key);
            $childs =  $xml->createElement("childs");
            $parent_node->appendChild($url);
            $properties = $xml->createElement("properties");
            if (isset($value['model']))
                $model = $xml->createElement("model", "'{$value['model']}'");
            if (isset($value['template']))
                $template = $xml->createElement("template", "'{$value['template']}'");
            if (isset($value['view']))
                $view = $xml->createElement("view", "'{$value['view']}'");
            if (isset($value['modules'])) {
                $result =[];
                foreach ($value['modules'] as $module){
                    $result[] .= "'$module'";
                }
                $imp = implode (',',$result);
                $modules = $xml->createElement("modules", "[$imp]");
            }
            else
                $modules = $xml->createElement("modules", "[]");
            if (isset($value['auth'])) {
                $auth = $xml->createElement("auth");
            }
            if (isset($value['log'])) {
                $log = $xml->createElement("log");
            }
            if (isset($value['action'])) {
                $action = $xml->createElement("action","'{$value['action']}'");
            }

            if (isset($value['callback'])) {
                $result =[];
                foreach ($value['callback'] as $callkey => $callvalue){
                    $result[] = "'$callkey'"."=>"."'$callvalue'";
                }
                $imp = implode (',',$result);
                $callback = $xml->createElement("callback", "[$imp]");
            }
            else
                $callback = $xml->createElement("callback", "[]");
            if (isset($value['file'])){
                $file = $xml->createElement("file");
                if (isset($value['file']['path'])) {
                    $path = $xml->createElement("path","'{$value['file']["path"]}'");
                    $file->appendChild($path);
                }
                if (isset($value['file']['method'])) {
                    $method = $xml->createElement("method","'{$value['file']["method"]}'");
                    $file->appendChild($method);
                }
            }
            if (isset($value['model']))
                $properties->appendChild($model);
            if (isset($value['template']))
                $properties->appendChild($template);
            if (isset($value['view']))
                $properties->appendChild($view);
            $properties->appendChild($modules);
            if (isset($value['auth'])) {
                $properties->appendChild($auth);
            }
            if (isset($value['log'])) {
                $properties->appendChild($log);
            }
            if (isset($value['action'])) {
                $properties->appendChild($action);
            }
            if (isset($value['file']))
                $properties->appendChild($file);
            $properties->appendChild($callback);
            $url->appendChild($properties);
            $url->appendChild($childs);
            createTree($xml,$key,$childs);
        }
    }
    return;
}


function XMLtoModel(){
    $xml = new DOMDocument;
    $xml->load(ROOTDIR."/app/URLmodel.xml");
    $xsl = new DOMDocument;
    $xsl->load(ROOTDIR."/support/supportengine/XMLtoModel.xsl");
    $proc = new XSLTProcessor;
    $proc->registerPHPFunctions();
    $proc->importStyleSheet($xsl);
    $str =  $proc->transformToXML($xml);
    $str = substr($str,0,-2);
    $file = fopen (ROOTDIR.'/app/URLmodel.php',"w");
    fwrite($file,"<?php \$URLstructure = [$str]; ?>");
}

function manifestToSchema(){
    $conn = DBConnection::getInstance();
    $conn->StartTransaction();
    if (!SystemActions::deletAllActions()){
        $conn->rollback();
        echo "manifest fail";
    }
    $modules = scandir(ROOTDIR."/app/modules");
    $str = "";
    foreach ($modules as $module) {
        if (file_exists(ROOTDIR . "/app/modules/$module/manifest/manifest.xml")) {
            $xml = new DOMDocument;
            $xml->load(ROOTDIR . "/app/modules/$module/manifest/manifest.xml");
            $xsl = new DOMDocument;
            $xsl->load(ROOTDIR . "/support/supportengine/manifestToSchema.xsl");
            $proc = new XSLTProcessor;
            $proc->registerPHPFunctions();
            $proc->importStyleSheet($xsl);
            $str .= $proc->transformToXML($xml);
        }
    }
    //$str = substr($str,0,-2);
    $file = fopen (ROOTDIR.'/webengine/lib/log_system/SystemActionsSchema.php',"w");
    if (!fwrite($file,"<?php define('SYSTEM_ACTIONS_SCHEMA', [$str]); ?>")){
        $conn->rollback();
        echo "manifest fail";
    }
    $conn->commit();
}
?>