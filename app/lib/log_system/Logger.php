<?php
class Logger {
    public static $logs=[];


    /**
     *log system action to database
     *
     * @param string $action Action id
     * @param string $user_id User id
     * @param int $status Action status code
     * @return mixed Log status
     */
    public static function log($action, $user_id, $status){
        $conn = DBConnection::getInstance();
        $query = "CALL putLog('$action','$user_id',$status);";
        return $conn->performQuery($query);
    }

    public static function getLogsList(){
        $logs = glob(ROOTDIR.'/logs/*.csv');
        if (empty($logs)){
            return [];
        }
        $logs_names=[];
        foreach ($logs as $log){
            $logs_names[] = pathinfo($log)['filename'];
        }
        self::$logs = $logs_names;
        return $logs_names;
    }

    public static function createTempLogsTable($logs){
        $conn = DBConnection::getInstance();
        $query = "CALL createTempLogsTable();";
        if (!$conn->performQuery($query))
            return "error";
        if (empty($logs))
            return;
        $query = "";
        foreach ($logs as $log){
            $filename = ROOTDIR.'/logs/'.$log.'.csv';
            if (file_exists($filename)){
                $handle = fopen($filename, "r");
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    list($id,$action,$user_id,$created_at,$status) = $data;
                    if (!$status)
                        $status = 0;
                    $query .= "INSERT INTO system_log_temp (id,action,user_id,created_at,status)
                               VALUES ('$id','$action','$user_id','$created_at',$status);";
                }
                fclose($handle);
            }
        }
        if ($conn->performMultiQuery($query))
            return "error";
    }

    public static function clearTempLogs(){
        $conn = DBConnection::getInstance();
        $query = "CALL clearTempLogsTable();";
        return $conn->performQuery($query);
    }
}