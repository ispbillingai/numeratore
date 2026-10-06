<?php
/**
 * Every night at midnight: all counters start again from 1.
 * Numbering is per day already (queue_tickets.ticket_date); this closes the
 * tickets left waiting or called the day before, so no screen or operator
 * carries yesterday's queue, and writes the reset in the activity log.
 *   /etc/cron.d/eliminacode: 0 0 * * * www-data /usr/bin/php /var/www/html/eliminacode/bin/midnight-reset.php
 * (copy of bin/eliminacode.cron)
 */
if (PHP_SAPI !== 'cli') exit;
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queue.php';

$closed = queueCloseDay();
logActivity('queue_midnight_reset', 'queue_service', null, ['closed' => $closed]);
echo date('Y-m-d H:i:s') . " counters reset, $closed open tickets of previous days closed\n";
