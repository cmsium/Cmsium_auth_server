ALTER TABLE user_properties ADD birthplace varchar(45);
ALTER TABLE user_properties ADD birth_date date;
ALTER TABLE user_properties DROP COLUMN age;
ALTER TABLE user_properties ADD firstname varchar(32);
ALTER TABLE user_properties ADD middlename varchar(32);
ALTER TABLE user_properties ADD lastname varchar(32);