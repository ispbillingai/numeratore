-- Migration: 049_queue
-- Eliminacode (take-a-number): services the customer picks on the totem,
-- the tickets taken each day, and the product tiles shown on the monitor.
-- Numbers restart from 1 every day per service. Settings live in settings.queue.

CREATE TABLE IF NOT EXISTS queue_services (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    letter      VARCHAR(3)   NOT NULL,
    name        VARCHAR(80)  NOT NULL,
    color       VARCHAR(7)   NOT NULL DEFAULT '#e74c3c',
    active      TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order  INT          NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS queue_tickets (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    service_id   INT          NOT NULL,
    ticket_date  DATE         NOT NULL,
    number       INT          NOT NULL,
    status       ENUM('waiting','called','served') NOT NULL DEFAULT 'waiting',
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    called_at    DATETIME     NULL DEFAULT NULL,
    call_count   INT          NOT NULL DEFAULT 0,
    called_by    INT          NULL DEFAULT NULL,
    printed      TINYINT(1)   NOT NULL DEFAULT 0,
    UNIQUE KEY uq_queue_ticket (service_id, ticket_date, number),
    KEY idx_queue_status (ticket_date, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS queue_slides (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(120) NOT NULL,
    subtitle    VARCHAR(255) NULL DEFAULT NULL,
    price       VARCHAR(40)  NULL DEFAULT NULL,
    image_path  VARCHAR(255) NULL DEFAULT NULL,
    active      TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order  INT          NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO queue_services (letter, name, color, sort_order)
SELECT 'A', 'Banco', '#e74c3c', 1 FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM queue_services);
