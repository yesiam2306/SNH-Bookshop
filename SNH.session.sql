DROP DATABASE IF EXISTS SNH_Proj;
CREATE DATABASE SNH_Proj;
USE SNH_Proj;
SET GLOBAL event_scheduler = ON;
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    passhash VARCHAR(255) NOT NULL,
    role ENUM('User', 'Premium', 'Admin', 'Pending') DEFAULT 'User',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    token VARCHAR(255) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
DROP EVENT IF EXISTS clean_pending_users;
CREATE EVENT clean_pending_users ON SCHEDULE EVERY 10 MINUTE DO
DELETE FROM users
WHERE role = 'Pending'
    AND created_at < (NOW() - INTERVAL 10 MINUTE);
DROP EVENT IF EXISTS clean_password_resets;
CREATE EVENT clean_password_resets ON SCHEDULE EVERY 10 MINUTE DO
UPDATE users
SET token = NULL
WHERE token IS NOT NULL
    AND role <> 'Pending';
INSERT INTO users (email, passhash, role)
VALUES (
        'a@gmail.com',
        '$2y$10$HFqGYzVAgjc1Y1P9D/xA2eNQTLR6vMU8W2krJAbVE0mvaI5c4lwHq',
        'Admin'
    ),
    (
        'b@gmail.com',
        '$2y$10$vIHYdei3NLVGWtruFBga1OQMuKKfLpngohMsEQm73msDJSPx6FzpW',
        'User'
    );
DROP TABLE IF EXISTS session_tokens;
CREATE TABLE session_tokens (
    selector VARCHAR(50) PRIMARY KEY,
    validator_hash VARCHAR(255) NOT NULL,
    user_id INT NOT NULL,
    expires_at TIMESTAMP DEFAULT (CURRENT_TIMESTAMP + INTERVAL 30 DAY),
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE (user_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
DROP TABLE IF EXISTS quarantine;
CREATE TABLE quarantine (
    ip VARCHAR(45) NOT NULL,
    email VARCHAR (255) NULL,
    attempts INT DEFAULT 1,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    unlock_token VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (ip, email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
DROP EVENT IF EXISTS clean_quarantine;
CREATE EVENT clean_quarantine ON SCHEDULE EVERY 10 MINUTE DO
DELETE FROM quarantine
WHERE last_attempt < (NOW() - INTERVAL 10 MINUTE);
DROP TABLE IF EXISTS novels;
CREATE TABLE novels (
    novel_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    is_premium TINYINT DEFAULT 0,
    is_short TINYINT DEFAULT 1,
    content VARCHAR(255) NULL,
    file_original_name VARCHAR(255) NULL,
    file_stored_name VARCHAR(255) NULL,
    file_size INT NULL,
    file_hash VARCHAR(255) NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;