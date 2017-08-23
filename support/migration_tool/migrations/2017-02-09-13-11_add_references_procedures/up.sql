CREATE PROCEDURE getTableReference(tableName VARCHAR(64))
  BEGIN
    SELECT table_name, column_name, module_name
    FROM system_references WHERE table_name = tableName;
  END;
CREATE PROCEDURE addTableReference(tableName VARCHAR(255), columnName VARCHAR(255), moduleName VARCHAR(255))
  BEGIN
    INSERT INTO system_references VALUES(tableName, columnName, moduleName);
  END;