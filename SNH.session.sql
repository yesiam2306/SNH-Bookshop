DROP DATABASE IF EXISTS SNH_Proj;
CREATE DATABASE SNH_Proj;
USE SNH_Proj;

DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    passhash VARCHAR(255) NOT NULL,
    salt VARCHAR(32) NOT NULL,
    role ENUM('User','Premium','Admin') DEFAULT 'User',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, passhash, salt, role) VALUES
('a', SHA2(CONCAT('1111222233334444', 'a'), 256), '1111222233334444', 'Admin'),
('z', SHA2(CONCAT('abcdabcdabcdabcd', 'z'), 256), 'abcdabcdabcdabcd', 'User');

DROP TABLE IF EXISTS session_tokens;
CREATE TABLE session_tokens (
    selector VARCHAR(50) PRIMARY KEY,
    validator_hash VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 DAY),
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (user_id)
);



SELECT * FROM session_tokens;