<?php
class CountryConstructor
{

    private $country_name = false;

    public function __construct($country) {
        $this->country_name = $country;
    }

    /**
     * Builds a country table, based on it's name
     *
     * @return string Name of the table created
     */
    public function build() {
        $country = strtolower($this->country_name);
        if (!$country) {
            ErrorHandler::throwException(CREATE_COUNTRY_TABLE_ERROR, 'page');
        }
        $query = "CREATE TABLE address_$country(
                      id int(11) NOT NULL AUTO_INCREMENT, 
                      name varchar(45) NOT NULL, 
                      parent_id int(11), 
                      type_id int(11) NOT NULL, 
                      PRIMARY KEY (id), 
                      INDEX (name), 
                      INDEX (parent_id), 
                      INDEX (type_id)) ENGINE=InnoDB;";
        $conn = DBConnection::getInstance();
        if (!$conn->performQuery($query)) {
            ErrorHandler::throwException(CREATE_COUNTRY_TABLE_ERROR, 'page');
        }
        return 'address_'.$country;
    }
}