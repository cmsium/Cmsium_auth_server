<?php
class DBConnection {
    public $conn;
    public static $instance;

    /**
     * Get  Instance of DBConnector
     *
     * @return object DBConnector new instance or self
     */
    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function __construct() {
        $host = Config::get('servername');
        $port = (int)Config::get('port');
        $dbname = Config::get('dbname');
        $username = Config::get('username');
        $password = Config::get('password');
        $conn = new mysqli($host, $username, $password, $dbname, $port);
        if ($conn->connect_errno) {
            ErrorHandler::throwException(ERROR_DB_CONNECTION);
        }
        $conn->set_charset('utf8');
        $this->conn = $conn;
    }

    /**
     * Функция исполняет запрос к БД, переданный в параметрах
     *
     * @param string $query Запрос в БД
     * @return bool
     */
    public function performQuery($query) {
        $conn = $this->conn;
        $result = $conn->query($query);
        if ($result) {
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return true;
        } else {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error;
            echo "Запрос: " . $query . PHP_EOL;
            //ErrorHandler::throwException(PERFORM_QUERY_ERROR,"page");
            return false;
        }
    }

    public function performMultiQuery($query) {
        $conn = $this->conn;
        $result = $conn->multi_query($query);
        if ($result) {
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return true;
        } else {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error;
            //ErrorHandler::throwException(PERFORM_QUERY_ERROR,"page");
            return false;
        }
    }

    /**
     * Функция исполняет запрос к БД, переданный в параметрах с возвращением именованного массива значений
     *
     * @param string $query Запрос в БД
     * @return bool|array False если запрос не удался или нечего возвращать, иначе именованный массив
     */
    public function performQueryFetch($query) {
        $conn = $this->conn;
        $row = $conn->query($query);
        if ($row) {
            $result = $row->fetch_array(MYSQLI_ASSOC);
            $row->close();
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return $result ? $result : false;
        } else {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error . PHP_EOL;
            echo "Запрос: " . $query . PHP_EOL;
            return false;
        }
    }

    /**
     * Функция исполняет запрос к БД, переданный в параметрах с возвращением именованного массива всех значений
     *
     * @param string $query Запрос в БД
     * @return bool|array False если запрос не удался или нечего возвращать, иначе именованный массив
     */
    public function performQueryFetchAll($query) {
        $conn = $this->conn;
        $row = $conn->query($query);
        if ($row) {
            $result = $row->fetch_all(MYSQLI_ASSOC);
            $row->close();
            while ($conn->more_results()) {
                $conn->next_result();
            }
            return $result ? $result : false;
        } else {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error;
            echo "Запрос: " . $query . PHP_EOL;
            return false;
        }
    }

    /**
     * Performs prepared query with an array of params.
     * Type sensitive!
     *
     * @param string $query Query statement
     * @param array $params Enumerated array of binding params
     * @return bool Status
     */
    public function performPreparedQuery($query, array $params) {
        $conn = $this->conn;
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error;
            ErrorHandler::throwException(PERFORM_QUERY_ERROR);
        }
        $types = $this->getDataTypes($params);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error;
            ErrorHandler::throwException(PERFORM_QUERY_ERROR);
        }
        $stmt->close();
        return true;
    }

    /**
     * Performs prepared query with an array of params.
     * Type sensitive!
     *
     * @param string $query Query statement
     * @param array $params Enumerated array of binding params
     * @return bool Status
     */
    public function performPreparedQueryFetchAll($query, array $params) {
         $conn = $this->conn;
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error;
            ErrorHandler::throwException(PERFORM_QUERY_ERROR);
        }
        $types = $this->getDataTypes($params);
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            echo "Не удалось: (" . $conn->errno . ") " . $conn->error;
            ErrorHandler::throwException(PERFORM_QUERY_ERROR);
        }
        $result = $stmt->get_result();
        $final_result = [];
        while ($data = $result->fetch_array(MYSQLI_ASSOC)) {
            $final_result[] = $data;
        }
        $stmt->close();
        return $final_result;
    }

    /**
     * Detects prepared statement data type by php data type
     *
     * @param $params
     * @return string Data type
     */
    private function getDataTypes($params) {
        $types = '';
        foreach ($params as $value) {
            switch (gettype($value)) {
                case 'integer':
                    $types .= 'i';
                    break;
                case 'string':
                    $types .= 's';
                    break;
                case 'double':
                    $types .= 'd';
                    break;
                case 'NULL':
                    $types .= 's';
                    break;
                default:
                    ErrorHandler::throwException(UNSUPPORTED_DATA_TYPE);
            }
        }
        return $types;
    }

    /**
     * Sets autocommit db setting
     *
     * @param $mode bool Autocommit mode
     */
    public function setAutoCommit($mode) {
        $conn = $this->conn;
        if (!$conn->autocommit($mode)) {
            ErrorHandler::throwException(ERROR_DB_TRANSACTION);
        }
    }

    /**
     * Starts a new transaction
     *
     * @param bool $read_only If true, sets transaction to read only mode
     */
    public function startTransaction($read_only = false) {
        $conn = $this->conn;
        $param = $read_only ? MYSQLI_TRANS_START_READ_ONLY : MYSQLI_TRANS_START_READ_WRITE;
        if (!$conn->begin_transaction($param)) {
            ErrorHandler::throwException(ERROR_DB_TRANSACTION);
        }
    }

    /**
     * Rollbacks a transaction
     */
    public function rollback() {
        $conn = $this->conn;
        if (!$conn->rollback()) {
            ErrorHandler::throwException(ERROR_DB_TRANSACTION);
        }
    }

    /**
     * Commits a transaction
     */
    public function commit() {
        $conn = $this->conn;
        if (!$conn->commit()) {
            ErrorHandler::throwException(ERROR_DB_TRANSACTION);
        }
    }

    /**
     * Creates a new mysql schema from config
     */
    public static function createDB() {
        try {
            $host = Config::get('servername');
            $port = Config::get('port');
            $dbname = Config::get('dbname');
            $username = Config::get('username');
            $password = Config::get('password');
            $dbh = new PDO("mysql:host=$host;port=$port", $username, $password);
            $dbh->exec("CREATE SCHEMA IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8;");
            $dbh = null;
        } catch (PDOException $e) {
            die("DB ERROR: ". $e->getMessage());
        }
    }

    /**
     * Drops a mysql schema from config
     */
    public static function dropDB() {
        try {
            $host = Config::get('servername');
            $port = Config::get('port');
            $dbname = Config::get('dbname');
            $username = Config::get('username');
            $password = Config::get('password');
            $dbh = new PDO("mysql:host=$host;port=$port", $username, $password);
            $dbh->exec("DROP SCHEMA IF EXISTS `$dbname`;");
            $dbh = null;
        } catch (PDOException $e) {
            die("DB ERROR: ". $e->getMessage());
        }
    }

    /**
     * Performs a migration query
     *
     * @param $query string Query
     */
    public static function performMigrationQuery($query) {
        try {
            $host = Config::get('servername');
            $port = Config::get('port');
            $dbname = Config::get('dbname');
            $username = Config::get('username');
            $password = Config::get('password');
            $dbh = new PDO("mysql:dbname=$dbname;host=$host;port=$port;charset=UTF8", $username, $password);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->query($query);
            $dbh = null;
        } catch (PDOException $e) {
            die("DB ERROR: ". $e->getMessage());
        }
    }

    /**
     * Procedure calling unified interface
     *
     * @param $procedure string Procedure name
     * @param $props array|bool Array of arguments
     * @param string $fetch_mode string Fetch mode
     * @return array|bool Result or false
     */
    public function callProcedure($procedure, $props = false, $fetch_mode = 'none') {
        $props = $props ? implode(', ',$props) : '';
        $query = "CALL $procedure($props);";
        switch ($fetch_mode) {
            case 'none':
                return $this->performQuery($query); break;
            case 'fetch':
                return $this->performQueryFetch($query); break;
            case 'fetch_all':
                return $this->performQueryFetchAll($query); break;
            default:
                ErrorHandler::throwException(PERFORM_QUERY_ERROR);
        }
    }

    /**
     * Destroys an object and kills a connection
     */
    public function __destruct() {
        if ($this->conn) {
            $conn = $this->conn;
            if (!$conn->close()) {
                ErrorHandler::throwException(DB_CONN_CLOSE_ERROR);
            }
        }
    }

}