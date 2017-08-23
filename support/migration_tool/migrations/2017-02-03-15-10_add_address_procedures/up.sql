CREATE PROCEDURE writeAddressObject(tableName VARCHAR(255), IN addressName VARCHAR(45),IN idParent INT, IN idType INT)
  BEGIN
    SET @sql = CONCAT('INSERT INTO ',
                      tableName,
                      '(name, parent_id, type_id) VALUES ("',
                      addressName,
                      '",',
                      idParent,
                      ',',
                      idType,
                      ');');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
    SELECT last_insert_id() as id;
  END;
CREATE PROCEDURE getAddressTypes()
  BEGIN
    SELECT * FROM address_types;
  END;
CREATE PROCEDURE checkAddressObjectPresence(tableName VARCHAR(255),objectName VARCHAR(45), idParent INT, idType INT)
  BEGIN
    SET @sql = CONCAT('SELECT id FROM ',
                      tableName,
                      ' WHERE name = "',
                      objectName,
                      '" AND parent_id = ',
                      idParent,
                      ' AND type_id = ',
                      idType,
                      ';');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END;
CREATE PROCEDURE getAllCountries()
  BEGIN
    SELECT * FROM address_countries;
  END;
CREATE PROCEDURE getCountryByISO(countryISO INT)
  BEGIN
    SELECT * FROM address_countries WHERE iso = countryISO;
  END;
CREATE PROCEDURE checkAddressTablePresence(tableName VARCHAR(255))
  BEGIN
    SELECT table_name FROM information_schema.tables WHERE table_name = tableName;
  END;
CREATE PROCEDURE createTempTableKLADR()
  BEGIN
    DROP TEMPORARY TABLE IF EXISTS kladr_temp;
    CREATE TEMPORARY TABLE kladr_temp(
      id int(11) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      code varchar(255) NOT NULL,
      full_code varchar(255) NOT NULL,
      obj_id int(11) NOT NULL DEFAULT -1,
      type_id int(11) NOT NULL, PRIMARY KEY (id)
    ) ENGINE=InnoDB;
  END;
CREATE PROCEDURE getRowFromKLADRTemp(idRow INT(11))
  BEGIN
    SELECT * FROM kladr_temp WHERE id = idRow;
  END;
CREATE PROCEDURE getNameFromKLADRTempByCode(codeVal VARCHAR(255), idType INT(11))
  BEGIN
    SELECT name FROM kladr_temp WHERE code = codeVal AND type_id = idType;
  END;
CREATE PROCEDURE writeAddressRUObject(IN addressName VARCHAR(45),IN idParent INT, IN idType INT)
  BEGIN
    INSERT INTO address_ru(name, parent_id, type_id) VALUES (addressName,idParent,idType);
    SELECT last_insert_id() as id;
  END;
CREATE PROCEDURE checkAddressRUObject(IN addressName VARCHAR(45),IN idParent INT, IN idType INT)
  BEGIN
    SELECT id FROM address_ru WHERE name = addressName AND parent_id = idParent AND type_id = idType;
  END;
CREATE PROCEDURE addObjectIDToKLADRTemp(IN idObj INT(11),IN idKLADRRow INT(11))
  BEGIN
    UPDATE kladr_temp SET obj_id = idObj WHERE id = idKLADRRow;
  END;
CREATE PROCEDURE getObjIDFromKLADRTempByCode(codeVal VARCHAR(255), idType INT(11))
  BEGIN
    SELECT obj_id FROM kladr_temp WHERE code = codeVal AND type_id = idType;
  END;
CREATE PROCEDURE createTempTableStreetsKLADR()
  BEGIN
    DROP TEMPORARY TABLE IF EXISTS kladr_street_temp;
    CREATE TEMPORARY TABLE kladr_street_temp(
      id int(11) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      code varchar(255) NOT NULL,
      full_code varchar(255) NOT NULL,
      type_id int(11) NOT NULL, PRIMARY KEY (id)
    ) ENGINE=InnoDB;
  END;
CREATE PROCEDURE getRowFromStreetsKLADRTemp(idRow INT(11))
  BEGIN
    SELECT * FROM kladr_street_temp WHERE id = idRow;
  END;