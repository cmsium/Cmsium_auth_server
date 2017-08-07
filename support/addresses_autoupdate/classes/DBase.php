<?php
class DBase {

    private $filename = '';
    private $dbase_id = false;
    private $encoding = 'CP866';
    public $rows = 0;

    function __construct($filename, $encoding = false) {
        $this->filename = $filename;
        if ($encoding) {
            $this->encoding = $encoding;
        }
    }

    /**
     * Creates a new .dbf file resource
     *
     * @param $fields array Table fields
     * @param $type
     * @return $this|bool False if something went wrong
     */
    public function create($fields, $type = DBASE_TYPE_DBASE) {
        $filename = $this->filename;
        $dbase_id = dbase_create($filename, $fields, $type);
        if ($dbase_id) {
            $this->dbase_id = $dbase_id;
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Opens a DBase file
     *
     * @param int $mode Mode, according to PHP DBase documentation
     * @return $this|bool False if something went wrong
     */
    public function open($mode = 2) {
        $filename = $this->filename;
        $dbase_id = dbase_open($filename, $mode);
        if ($dbase_id) {
            $this->dbase_id = $dbase_id;
            $this->rows = dbase_numrecords($dbase_id);
            return $this;
        } else {
            return false;
        }
    }

    /**
     * Sets encoding to opened file
     *
     * @param $encoding string Proper encoding, default is 'CP866'
     */
    public function setEncoding($encoding) {
        $this->encoding = $encoding;
    }

    /**
     * Gets a row from opened DBase file
     *
     * @param $number int|string Number of needed row
     * @param bool $assoc If true, returns row as associated array
     * @param string $encoding Encoding of output array
     * @return bool|array False, if something went wrong
     */
    public function getRow($number, $assoc = false, $encoding = 'UTF-8') {
        if ($number > $this->rows) {
            return false;
        }
        $dbase_id = $this->dbase_id;
        if ($assoc) {
            $result = dbase_get_record_with_names($dbase_id, $number);
        } else {
            $result = dbase_get_record($dbase_id, $number);
        }
        if ($encoding != $this->encoding) {
            foreach ($result as &$value) {
                $value = mb_convert_encoding($value, $encoding, $this->encoding);
            }
            unset($value);
        }
        return $result ?: false;
    }

    // TODO: Implement write and delete functions

    /**
     * Closes an opened DBase file
     *
     * @return bool False, if something went wrong
     */
    public function close() {
        $dbase_id = $this->dbase_id;
        if ($dbase_id) {
            $result = dbase_close($dbase_id);
            if ($result) {
                $this->dbase_id = null;
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    function __destruct() {
        $this->close();
        $this->filename = null;
    }

}