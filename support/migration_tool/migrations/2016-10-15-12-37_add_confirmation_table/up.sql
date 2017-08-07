CREATE TABLE confirmation (
 user_id varchar(32) NOT NULL,
 expires timestamp NOT NULL,
 INDEX (user_id))
 ENGINE=InnoDB;