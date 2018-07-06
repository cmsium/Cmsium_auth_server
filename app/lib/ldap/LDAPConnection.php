<?php
/**
 * LDAP connector class
 */
class LDAPConnection {

    public $conn;
    public static $instance;

    /**
     * Get Instance of LDAPConnection
     *
     * @return object LDAPConnection new instance or self
     */
    public static function getInstance(){
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * LDAPConnection constructor. Connects to LDAP server
     * @throws AppException
     */
    public function __construct() {
        $host = Config::get('host', LDAP_SETTINGS_PATH);
        $port = (int) Config::get('port', LDAP_SETTINGS_PATH);
        if (!$this->conn = ldap_connect($host, $port)) {
            ErrorHandler::throwException(LDAP_CONNECTION_FAILED);
        }
        ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        if ($admin_dn = Config::get('admin_dn', LDAP_SETTINGS_PATH)) {
            $password = Config::get('password', LDAP_SETTINGS_PATH);
            $this->bind($admin_dn, $password);
        } else {
            $this->bind();
        }
    }

    /**
     * Binds to a specific directory with a given connection
     *
     * @param string|bool $rdn Bind DN
     * @param string|bool $password Password, if needed
     * @return bool Boolean result
     */
    public function bind($rdn = false, $password = false) {
        if ($rdn && $password) {
            if (@ldap_bind($this->conn, $rdn, $password)) {
                return true;
            } else {
                return false;
            }
        } else {
            // Anonymous connection
            if (@ldap_bind($this->conn)) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * Searches in current LDAP server's DIT some records by a specific filter
     *
     * @param string $filter Active Directory query
     * @param array $attributes List of attributes to appear in a result
     * @return array|bool List of resulting records or false
     */
    public function search($filter, $attributes = ['cn']) {
        $base_dn = Config::get('base_dn', LDAP_SETTINGS_PATH);
        if ($result = ldap_search($this->conn, $base_dn, $filter, $attributes)) {
            return ldap_get_entries($this->conn, $result);
        } else {
            return false;
        }
    }

    /**
     * Adds a new record to DIT
     *
     * @param string $dn DN of the new record, starting from base DN
     * @param array $data Array of attributes for a new record
     * @return bool|string New record's full DN or false
     */
    public function addRecord($dn, $data) {
        if (ldap_add($this->conn, $dn, $data)) {
            return $dn;
        } else {
            return false;
        }
    }

    public function editRecord($dn, $data) {
        if (ldap_modify($this->conn, $dn, $data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Deletes a given record
     *
     * @param string $dn DN of the record, starting from base DN
     * @return bool True, if the record is successfully deleted, else false
     */
    public function deleteRecord($dn) {
        if (ldap_delete($this->conn, $dn)) {
            return true;
        } else {
            return false;
        }
    }

    // Static functions

    /**
     * Returns an array of LDAP-ready attributes based on given preset and data
     * Presets are stored as XSLT files inside "/ldap/data_presets/" directory
     *
     * @param string $preset_name Name of preset (XSLT file name)
     * @param array $data Raw data as enum array to fill preset's fields
     * @return mixed Formed preset
     */
    public static function getDataPreset($preset_name, $data) {
        $data_converter = DataConverter::getInstance();
        $data_xml = $data_converter->arrayToXML($data);
        $result_xml = Controller::xmlStrTransform($data_xml, "ldap/data_presets/$preset_name.xsl");
        return $data_converter->XMLToArray($result_xml);

    }

    /**
     * Prepares a string password for LDAP entry insertion
     *
     * @param string $raw_password Password to prepare
     * @return string LDAP-ready password
     */
    public static function prepareMD5Password($raw_password) {
        return "{MD5}".base64_encode(pack("H*",md5($raw_password)));
    }

    /**
     * Closes connection when the object is destroyed
     */
    public function __destruct() {
        ldap_close($this->conn);
    }

}