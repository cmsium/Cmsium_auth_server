CREATE TABLE roles (id int(11) NOT NULL AUTO_INCREMENT, name varchar(32) NOT NULL, t_name VARCHAR(32) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB;
CREATE TABLE roles_in_users (role_id int(11) NOT NULL, user_id varchar(32) NOT NULL, INDEX (role_id), INDEX (user_id)) ENGINE=InnoDB;
ALTER TABLE roles_in_users ADD INDEX FKroles_in_u347977 (role_id), ADD CONSTRAINT FKroles_in_u347977 FOREIGN KEY (role_id) REFERENCES roles (id);
