CREATE PROCEDURE destroyUser(IN idUser VARCHAR(32))
  BEGIN
    DELETE FROM phones WHERE user_id = idUser;
    DELETE FROM emails WHERE user_id = idUser;
    DELETE FROM user_properties WHERE user_id = idUser;
    DELETE FROM confirmation WHERE user_id = idUser;
    DELETE FROM roles_in_users WHERE user_id = idUser;
    DELETE FROM bus_tickets WHERE id_user = idUser;
  END;
CREATE PROCEDURE createDestroyConfirmation(IN idUser VARCHAR(32))
  BEGIN
    INSERT INTO confirmation(user_id, expires) VALUES (idUser, DATE_ADD(NOW(), INTERVAL 20 MINUTE));
  END;
CREATE PROCEDURE checkUserConfirmation(IN idUser VARCHAR(32))
  BEGIN
    SELECT user_id FROM confirmation WHERE user_id = idUser;
  END;
CREATE PROCEDURE getUser(IN idUser VARCHAR(32))
  BEGIN
    SELECT bus.id_user, e.email, p.phone, props.username, props.firstname, props.middlename, props.lastname,
      props.birthplace, props.birth_date,
      GROUP_CONCAT(DISTINCT r.name ORDER BY r.name ASC SEPARATOR ', ') AS roles
    FROM bus_tickets AS bus
      INNER JOIN user_properties AS props ON bus.id_user = props.user_id
      INNER JOIN emails AS e ON bus.id_user = e.user_id
      INNER JOIN phones AS p ON bus.id_user = p.user_id
      JOIN roles_in_users AS riu ON bus.id_user = riu.user_id
      JOIN roles AS r ON riu.role_id = r.id
    WHERE bus.id_user = idUser
    GROUP BY id_user;
  END;
CREATE PROCEDURE getAllUsers(IN Start INT, IN myOffset INT)
  BEGIN
    SELECT SQL_CALC_FOUND_ROWS bus.id_user, props.username, props.firstname, props.middlename, props.lastname,
      GROUP_CONCAT(DISTINCT r.t_name ORDER BY r.t_name ASC SEPARATOR ', ') AS roles
    FROM bus_tickets AS bus
      LEFT JOIN user_properties AS props ON bus.id_user = props.user_id
      LEFT JOIN roles_in_users AS riu ON bus.id_user = riu.user_id
      LEFT JOIN roles AS r ON riu.role_id = r.id
    GROUP BY bus.id_user, props.username, props.firstname, props.middlename, props.lastname LIMIT Start,myOffset;
  END;
CREATE PROCEDURE setUserData(IN idUser VARCHAR(32))
  BEGIN
    SELECT *,GROUP_CONCAT(DISTINCT roles.id SEPARATOR ', ') AS roles_id, GROUP_CONCAT(DISTINCT roles.name SEPARATOR ', ') AS roles,GROUP_CONCAT(DISTINCT roles.t_name SEPARATOR ', ') AS t_roles
    FROM  user_properties as props
      INNER JOIN phones ON  props.user_id = phones.user_id
      INNER JOIN emails ON props.user_id = emails.user_id
      JOIN roles_in_users AS riu ON props.user_id = riu.user_id
      JOIN roles  ON riu.role_id = roles.id
    WHERE props.user_id = idUser
    GROUP BY username;
  END;
CREATE PROCEDURE checkUserRole(IN idRole INT(11), IN idUser VARCHAR(32))
  BEGIN
    SELECT role_id FROM roles_in_users WHERE role_id = idRole AND user_id = idUser;
  END;
CREATE PROCEDURE writeAuthToken(IN idUser VARCHAR(32),IN userToken VARCHAR(32))
  BEGIN
    CALL clearAuthTokenById(idUser);
    INSERT INTO auth_tokens(user_id, token) VALUES (idUser, userToken);
  END;
CREATE PROCEDURE clearAuthToken(IN authToken VARCHAR(32))
  BEGIN
    DELETE FROM auth_tokens WHERE token = authToken;
  END;
CREATE PROCEDURE clearAuthTokenById(IN idUser VARCHAR(32))
  BEGIN
    DELETE FROM auth_tokens WHERE user_id = idUser;
  END;
CREATE PROCEDURE getAuthInfo(IN idUser VARCHAR(32),IN authToken VARCHAR(32))
  BEGIN
    SELECT user_id, token, created_at FROM auth_tokens WHERE user_id = idUser AND token = authToken;
  END;