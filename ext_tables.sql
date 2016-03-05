CREATE TABLE tx_csloauth2_oauth_clients (
  uid int(11) NOT NULL auto_increment,
  pid int(11) DEFAULT '0' NOT NULL,

  tstamp int(11) DEFAULT '0' NOT NULL,
  crdate int(11) DEFAULT '0' NOT NULL,
  cruser_id int(11) DEFAULT '0' NOT NULL,
  deleted tinyint(4) DEFAULT '0' NOT NULL,
  hidden tinyint(4) DEFAULT '0' NOT NULL,

  name varchar(80) NOT NULL,
  client_id varchar(80) NOT NULL,
  client_secret varchar(80),
  redirect_uri varchar(2000) NOT NULL,
  grant_types varchar(80),
  scope varchar(100),
  user_id varchar(80),

  PRIMARY KEY (uid),
	KEY parent (pid),
	UNIQUE KEY clients_client_id (client_id)
) ENGINE=InnoDB;

CREATE TABLE tx_csloauth2_oauth_access_tokens (
  access_token varchar(40) NOT NULL,
  client_id varchar(80) NOT NULL,
  user_id varchar(255),
  expires timestamp NOT NULL,
  scope varchar(2000),

  CONSTRAINT access_token_pk PRIMARY KEY (access_token)
) ENGINE=InnoDB;

CREATE TABLE tx_csloauth2_oauth_authorization_codes (
  authorization_code varchar(40) NOT NULL,
  client_id varchar(80) NOT NULL,
  user_id varchar(255),
  redirect_uri varchar(2000),
  expires timestamp NOT NULL,
  scope varchar(2000),

  CONSTRAINT auth_code_pk PRIMARY KEY (authorization_code)
) ENGINE=InnoDB;

CREATE TABLE tx_csloauth2_oauth_refresh_tokens (
  refresh_token varchar(40) NOT NULL,
  client_id varchar(80) NOT NULL,
  user_id varchar(255),
  expires timestamp NOT NULL,
  scope varchar(2000),

  CONSTRAINT refresh_token_pk PRIMARY KEY (refresh_token)
) ENGINE=InnoDB;

CREATE TABLE tx_csloauth2_oauth_users (
  username varchar(255) NOT NULL,
  password varchar(2000),
  first_name varchar(255),
  last_name varchar(255),

  CONSTRAINT username_pk PRIMARY KEY (username)
) ENGINE=InnoDB;

CREATE TABLE tx_csloauth2_oauth_scopes (
  scope text,
  is_default BOOLEAN
) ENGINE=InnoDB;

CREATE TABLE tx_csloauth2_oauth_jwt (
  client_id varchar(80) NOT NULL,
  subject varchar(80),
  public_key varchar(2000),

  CONSTRAINT jwt_client_id_pk PRIMARY KEY (client_id)
) ENGINE=InnoDB;
