-- Migration: 052_queue_daily_counts
-- Tickets taken per day and per service (reparto), for Amministrazione › Statistiche.
-- A separate counter because queue_tickets is cleared by "Azzera la coda di oggi":
-- the count stays even when the queue is reset. queueTake() adds 1 per ticket.

CREATE TABLE IF NOT EXISTS queue_daily_counts (
    count_date  DATE NOT NULL,
    service_id  INT  NOT NULL,
    tickets     INT  NOT NULL DEFAULT 0,
    PRIMARY KEY (count_date, service_id),
    KEY idx_qdc_service (service_id, count_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Tickets already taken before this counter existed.
INSERT IGNORE INTO queue_daily_counts (count_date, service_id, tickets)
SELECT ticket_date, service_id, COUNT(*) FROM queue_tickets GROUP BY ticket_date, service_id;
