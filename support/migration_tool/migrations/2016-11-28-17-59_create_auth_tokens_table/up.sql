CREATE TABLE auth_tokens (
  user_id varchar(32) NOT NULL,
  token varchar(32) NOT NULL,
  created_at timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL
) ENGINE=InnoDB;