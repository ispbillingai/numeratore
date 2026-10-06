-- Eliminacode — base schema for a fresh install.
-- Import this into an empty database, then run `php migrate.php`
-- (it creates the queue tables: migrations/049_queue.sql).
-- First login: admin / admin123 — change the password in Amministrazione › Utenti.

CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    username    VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(100) NOT NULL,
    role        VARCHAR(20)  NOT NULL DEFAULT 'operator',
    email       VARCHAR(100) NULL,
    phone       VARCHAR(20)  NULL,
    active      TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
    setting_key    VARCHAR(100) NOT NULL PRIMARY KEY,
    setting_value  MEDIUMTEXT   NULL,
    updated_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS activity_log (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NULL,
    action       VARCHAR(100) NOT NULL,
    entity_type  VARCHAR(50)  NULL,
    entity_id    INT          NULL,
    details      JSON         NULL,
    ip_address   VARCHAR(45)  NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_activity_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO users (username, password, full_name, role)
VALUES ('admin', '$2y$10$k9eeQiSsLNhYS7xclc65NOYgdws7nPqjlIOKEKW0riNdZtF2o2RJC', 'Amministratore', 'admin');
