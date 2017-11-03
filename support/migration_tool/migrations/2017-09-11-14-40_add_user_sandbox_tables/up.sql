create table draft_user_properties (
  username varchar(45) not null,
  password varchar(32) not null,
  info longtext null,
  user_id varchar(32) PRIMARY KEY,
  birthplace varchar(45) null,
  birth_date date null,
  firstname varchar(32) null,
  middlename varchar(32) null,
  lastname varchar(32) null,
  constraint username_UNIQUE
  unique (username)
);

create table draft_emails (
  email varchar(80) not null,
  user_id varchar(32) not null,
  verified bool,
  constraint email_UNIQUE
  unique (email)
);

create table draft_phones (
  phone varchar(10) not null,
  user_id varchar(32) not null,
  constraint draft_phone_UNIQUE unique (phone)
);

create table activation_codes (
  user_id varchar(32) not null,
  code varchar(32) not null,
  created_at timestamp default CURRENT_TIMESTAMP not null,
  constraint activation_codes_draft_user_properties_user_id_fk
  foreign key (user_id) references draft_user_properties (user_id)
);

CREATE PROCEDURE deleteDraftUser(IN idUser VARCHAR(32))
  BEGIN
    DELETE FROM draft_user_properties WHERE user_id = idUser;
    DELETE FROM draft_phones WHERE user_id = idUser;
    DELETE FROM draft_emails WHERE user_id = idUser;
  END;