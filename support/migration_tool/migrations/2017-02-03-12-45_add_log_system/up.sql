CREATE TABLE system_log
(
    id VARCHAR(32) PRIMARY KEY NOT NULL,
    action VARCHAR(32) NOT NULL,
    user_id VARCHAR (32),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    status INT(5)
);

CREATE TABLE system_actions
(
  action_id VARCHAR(32) PRIMARY KEY NOT NULL,
  name VARCHAR(64) NOT NULL,
  service_name VARCHAR(64) NOT NULL
);

CREATE TABLE roles_in_actions
(
    id_role INT(11) NOT NULL,
    action_id VARCHAR(32) NOT NULL
);
CREATE UNIQUE INDEX action_role_constr ON roles_in_actions (id_role, action_id);
