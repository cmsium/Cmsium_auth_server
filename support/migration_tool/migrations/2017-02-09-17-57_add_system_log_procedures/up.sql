CREATE PROCEDURE `addRoleToAction`(IN idRole INT,IN idAction VARCHAR(32))
BEGIN
    INSERT INTO roles_in_actions (id_role, action_id) VALUES (idRole,idAction);
END;

CREATE PROCEDURE `deleteRoleFromAction`(IN idRole INT,IN idAction VARCHAR(32))
BEGIN
    DELETE FROM roles_in_actions WHERE id_role=idRole AND action_id=idAction;
END;

CREATE PROCEDURE `createNewAction`(IN actionName VARCHAR(64))
BEGIN
    INSERT INTO system_actions (action_id,name) VALUE (MD5(actionName),actionName);
END;

CREATE PROCEDURE `checkAction`(IN ActionName VARCHAR(64))
BEGIN
    SELECT action_id FROM system_actions WHERE name=ActionName;
END;

CREATE PROCEDURE `getActionRoles`(IN Action VARCHAR(32))
BEGIN
    SELECT GROUP_CONCAT(DISTINCT id_role SEPARATOR ',') as roles FROM roles_in_actions WHERE action_id=Action;
END;

CREATE PROCEDURE `deleteAllActions`()
BEGIN
    DELETE FROM system_actions;
END;

CREATE PROCEDURE `putLog`(IN Action VARCHAR(32),IN idUser VARCHAR(32),IN Status VARCHAR(64))
BEGIN
    INSERT INTO system_log (id,action,user_id,created_at,status) VALUES (md5(CONCAT(Action,idUser,NOW())),Action,idUser,NOW(),Status);
END;

CREATE PROCEDURE `getSystemEventsCount`()
BEGIN
    SELECT FOUND_ROWS();
END;

CREATE PROCEDURE `getLogs`(IN Start INTEGER, IN myOffset INTEGER)
  BEGIN
    SELECT * FROM system_log LIMIT Start,myOffset;
  END;

CREATE PROCEDURE `clearLogs`()
  BEGIN
    DELETE FROM system_log;
  END;

