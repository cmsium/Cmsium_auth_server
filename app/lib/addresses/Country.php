<?php
class Country {

    private $iso;

    function __construct($iso) {
        $this->iso = $iso;
    }

    /**
     * Gets the name of the country
     *
     * @return bool False, if no name associated
     */
    public function getName() {
        $iso = $this->iso;
        $conn = DBConnection::getInstance();
        $query = "CALL getCountryByISO($iso);";
        $result = $conn->performQueryFetch($query);
        if ($result) {
            return $result['t_name'];
        } else {
            return false;
        }
    }

}