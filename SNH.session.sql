DROP DATABASE IF EXISTS SNH_Proj;
CREATE DATABASE SNH_Proj;
USE SNH_Proj;
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    passhash VARCHAR(255) NOT NULL,
    salt VARCHAR(32) NOT NULL,
    role ENUM('User', 'Premium', 'Admin') DEFAULT 'User',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
DROP TABLE IF EXISTS session_tokens;
CREATE TABLE session_tokens (
    selector VARCHAR(50) PRIMARY KEY,
    validator_hash VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 DAY),
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (user_id)
);
INSERT INTO users (email, passhash, salt, role)
VALUES (
        'a@gmail.com',
        SHA2(
            CONCAT('1111222233334444', 'a'),
            256
        ),
        '1111222233334444',
        'Admin'
    ),
    (
        'b@gmail.com',
        SHA2(
            CONCAT('1111222233334445', 'b'),
            256
        ),
        '1111222233334445',
        'Admin'
    );
DROP TABLE IF EXISTS quarantine;
CREATE TABLE quarantine (
    ip VARCHAR(45) NOT NULL,
    email VARCHAR (255) NOT NULL,
    attempts INT DEFAULT 1,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unlock_token VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (ip, email)
);
SET GLOBAL event_scheduler = ON;
DROP EVENT IF EXISTS clean_quarantine;
CREATE EVENT clean_quarantine ON SCHEDULE EVERY 10 MINUTE DO
DELETE FROM quarantine
WHERE last_attempt < (NOW() - INTERVAL 10 MINUTE);
DROP TABLE IF EXISTS novels;
CREATE TABLE novels (
    novel_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    content VARCHAR(255) NOT NULL,
    premium TINYINT DEFAULT 0
);