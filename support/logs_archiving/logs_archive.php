<?php
require dirname(__DIR__).'/../config/init_libs.php';
foreach (glob(ROOTDIR.'/app/modules/*/interfaces/*.php') as $require_path) {
    require_once $require_path;
}
foreach (glob(ROOTDIR.'/app/modules/*/classes/*.php') as $require_path) {
    require_once $require_path;
}
require REQUIRES;

define ('GET_LOGS_OFFSET',50);

function getLogs ($start){
    $offset = GET_LOGS_OFFSET;
    $conn = DBConnection::getInstance();
    $query = "CALL getLogs('$start','$offset');";
    return $conn->performQueryFetchAll($query);
}

function clearLogs (){
    $conn = DBConnection::getInstance();
    $query = "CALL clearLogs();";
    return $conn->performQuery($query);
}


function putLofToCSV (){
    $start = detectLastLog();
    $end = date('Y-m-d');
    $filename = $start."_".$end.'.csv';
    if (!file_exists('logs')){
        mkdir('logs');
        chmod('logs',0775);
    }
    $file = fopen('logs/'.$filename,'a');
    chmod('logs/'.$filename,0775);
    $start = 0;
    $status = true;
    while ($data = getLogs($start)) {
        foreach ($data as $event) {
            if (!fputcsv($file, $event)){
                $status = false;
            }
        }
        $start += GET_LOGS_OFFSET;
    }
    if ($status)
        clearLogs();
}

function detectLastLog(){
    $logs = glob('logs/*.csv');
    if (empty($logs)){
        return date('Y-m-d');
    }
    $logs_ends=[];
    foreach ($logs as $log){
        $times = explode('.',$log)[0];
        $end = explode('_',$times)[1];
        $logs_ends[]=$end;
    }
    $sorfunc = function ($a,$b){
        return strtotime($b) - strtotime($a);
    };
    usort($logs_ends,$sorfunc);
    return end($logs_ends);
}

