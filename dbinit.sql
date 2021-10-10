CREATE TABLE tokens( `VALUE` VARCHAR(320) NOT NULL, user_id INT NULL, CONSTRAINT pk_tokens PRIMARY KEY ( `VALUE` ASC ) ) ;

CREATE TABLE users( id INT AUTO_INCREMENT NOT NULL, name VARCHAR(32) NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(256) NOT NULL, is_active boolean NOT NULL DEFAULT FALSE, privilege INT NOT NULL DEFAULT 0
  ,created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, resetKey VARCHAR(256) NULL, resetKeyGeneraded TIMESTAMP NULL, CONSTRAINT pk_users PRIMARY KEY ( id ASC ) ) ;
ALTER TABLE users
  ADD UNIQUE KEY email (email),
  ADD UNIQUE KEY NAME (NAME);

CREATE TABLE googleapitokens( user_id INT NOT NULL, token TEXT NOT NULL, CONSTRAINT pk_GoogleApiTokens PRIMARY KEY ( user_id));
CREATE TABLE googleapicalendar( id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, calendar TEXT NOT NULL, name TEXT NOT NULL, isPrimary boolean NOT NULL, isActive boolean NOT NULL, CONSTRAINT pk_GoogleApiTokens PRIMARY KEY ( id));

CREATE TABLE appointments(id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token TEXT NOT NULL, SUBJECT TEXT NOT NULL, CONSTRAINT pk_appointments PRIMARY KEY (id));
CREATE TABLE appointmentParts(id INT AUTO_INCREMENT NOT NULL, appointment_id INT NOT NULL, duration INT, fromDate DATETIME, toDate DATETIME, CONSTRAINT pk_appointmentParts  PRIMARY KEY (id));

-- excecute separate from top

ALTER TABLE googleapitokens ADD CONSTRAINT FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;
ALTER TABLE tokens ADD CONSTRAINT FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;
ALTER TABLE googleapicalendar ADD CONSTRAINT FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;

ALTER TABLE appointments ADD CONSTRAINT FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE;
ALTER TABLE appointmentParts ADD CONSTRAINT FOREIGN KEY (appointment_id) REFERENCES appointments (id) ON DELETE CASCADE;