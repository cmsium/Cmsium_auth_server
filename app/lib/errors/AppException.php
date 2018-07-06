<?php

class AppException extends Exception {

    public $additional_info;

    public function __construct($message, $code = 0, Exception $previous = null, $additional_info = null) {
        parent::__construct($message, $code, $previous);
        if ($additional_info !== null) {
            $this->additional_info = $additional_info;
        }
    }

    // Переопределим строковое представление объекта.
    public function __toString() {
        return __CLASS__.": [{$this->code}]: {$this->message}\n";
    }

}