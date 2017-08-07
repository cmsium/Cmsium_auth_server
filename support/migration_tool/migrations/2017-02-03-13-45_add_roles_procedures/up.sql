CREATE PROCEDURE deleteRole(IN idRole VARCHAR(64))
  BEGIN
    START TRANSACTION;
    DELETE FROM roles_in_actions WHERE id_role = idRole;
    DELETE FROM roles_in_files WHERE role_id = idRole;
    DELETE FROM roles_in_users WHERE role_id = idRole;
    DELETE FROM roles WHERE id = idRole;
    COMMIT;
  END;

CREATE PROCEDURE getRoleTableColumns(IN tableName VARCHAR(64))
BEGIN
    SELECT column_name, column_type, is_nullable
    FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = CONCAT(tableName,'_properties');
END;

CREATE PROCEDURE readProps(IN tableName VARCHAR(64),IN idUser VARCHAR(32))
  BEGIN
    SET @sql = CONCAT('SELECT * FROM ',
                      tableName,
                      ' WHERE user_id="',
                      idUser,
                      '";');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END;

CREATE PROCEDURE deleteProps(IN tableName VARCHAR(64),IN idUser VARCHAR(32))
  BEGIN
    SET @sql = CONCAT('DELETE FROM ',
                      tableName,
                      ' WHERE user_id="',
                      idUser,
                      '";');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END;

CREATE PROCEDURE getRoles()
BEGIN
    SELECT * FROM roles;
END;

CREATE PROCEDURE deleteRoleFromActions (IN idRole INTEGER)
BEGIN
    DELETE FROM roles_in_actions WHERE id_role=idRole;
END;

CREATE PROCEDURE getRoleData (IN idRole INTEGER)
BEGIN
  SELECT * FROM roles WHERE id = idRole;
END;

CREATE PROCEDURE `getAllActions`()
BEGIN
    SELECT action_id,name FROM system_actions;
END;
CREATE PROCEDURE deleteAllActionsFromRole (IN idRole INTEGER)
  BEGIN
    DELETE FROM roles_in_actions WHERE id_role=idRole;
  END;

CREATE PROCEDURE `deleteRoleProp`(IN ROLE VARCHAR(32))
BEGIN
    SET @sql = CONCAT ('DROP TABLE IF EXISTS ',CONCAT(Role,'_properties'));
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END;