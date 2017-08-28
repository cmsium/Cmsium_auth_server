CREATE TABLE address_ru (id int(11) NOT NULL AUTO_INCREMENT, name varchar(256) NOT NULL, parent_id int(11), type_id int(11) NOT NULL, PRIMARY KEY (id), INDEX (name), INDEX (parent_id), INDEX (type_id)) ENGINE=InnoDB;
CREATE TABLE address_types (id int(11) NOT NULL AUTO_INCREMENT, name varchar(45) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB;
CREATE TABLE address_countries (name varchar(128) NOT NULL, t_name varchar(128) NOT NULL, alpha2 varchar(2) NOT NULL, alpha3 varchar(3) NOT NULL, iso int(3) NOT NULL AUTO_INCREMENT, PRIMARY KEY (iso), INDEX (name), INDEX (t_name), INDEX (alpha2), INDEX (alpha3), INDEX (iso)) ENGINE=InnoDB;
ALTER TABLE address_object ADD INDEX FKaddress_ob240958 (type_id), ADD CONSTRAINT FKaddress_ob240958 FOREIGN KEY (type_id) REFERENCES address_types (id);