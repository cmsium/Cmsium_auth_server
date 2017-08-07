INSERT INTO bus_tickets(id_user, ticket) VALUES ('eeec1e618690fba21fd416df610da961', '277059024dda7df2a79cf955b0ee7e5e');
INSERT INTO emails(email, user_id) VALUES ('ukladoff@cmsium.org', 'eeec1e618690fba21fd416df610da961');
INSERT INTO phones(phone, user_id) VALUES ('9999999999', 'eeec1e618690fba21fd416df610da961');
INSERT INTO user_properties(username, user_id, birthplace, birth_date, firstname, middlename, lastname) VALUES
  ('ukladoff', 'eeec1e618690fba21fd416df610da961', '2', '1945-05-09', 'Иннокентий', 'Артемович', 'Укладов');
INSERT INTO roles_in_users(role_id, user_id) VALUES (1, 'eeec1e618690fba21fd416df610da961');
INSERT INTO staff_properties(user_id,position) VALUES ('eeec1e618690fba21fd416df610da961','Главный заместитель хранителя конфет');

CREATE TABLE staff_properties
(
    user_id VARCHAR(32) NOT NULL,
    position VARCHAR(150)
);
CREATE INDEX user_id ON staff_properties (user_id);

INSERT INTO staff_properties (user_id, position) VALUES ('eeec1e618690fba21fd416df610da961', 'Главный заместитель хранителя конфет');