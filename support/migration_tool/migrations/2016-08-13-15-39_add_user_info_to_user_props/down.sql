ALTER TABLE user_properties DROP COLUMN lastname;
ALTER TABLE user_properties DROP COLUMN middlename;
ALTER TABLE user_properties DROP COLUMN firstname;
ALTER TABLE user_properties ADD age int(11);
ALTER TABLE user_properties DROP COLUMN birth_date;
ALTER TABLE user_properties DROP COLUMN birthplace;
