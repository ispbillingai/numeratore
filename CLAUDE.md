# Eliminacode (repo `numeratore`) — handoff notes

`F:\numeratore`, repo `ispbillingai/numeratore` (**public**). A **standalone** take-a-number
queue app (eliminacode). It started on 2026-10-06 as a copy of the Focacciami POS (`F:\pub`), and the
same day all the POS code (cash desk, orders, kitchen, menu, payments, Glovo, WhatsApp…) was removed
at the user's request. Integrating it into other projects is a later decision of the user: keep it standalone.

## What is in it
- `queue/totem.php`, `queue/monitor.php` (no login, `?k=` = `settings.queue.key`), `queue/operator.php`.
- `api/queue.php` — state, feed (weather/news/tiles), take, next/recall/call.
- `admin/index.php` (Eliminacode admin), `admin/users.php`, `admin/activity.php`.
- `includes/queue.php` (all queue logic, settings in `settings.queue`), `includes/functions.php`
  (session, auth, `appName()`), `includes/ThermalPrinter.php` (ESC/POS ticket over TCP 9100).
- Roles: `admin`, `operator`. Users are never deleted, only disabled.
- Italian-only UI (no i18n). `assets/css/style.css` is inherited from the POS.

## Where it runs
- Server 217.160.131.242 (IONOS Ubuntu 24.04, Apache 2.4, PHP 8.3, MariaDB 10.11), shared with
  Focacciami (`/var/www/html/focacciami`… see memory) and chiamata. SSH as root: credentials and the plink
  one-liner are in Claude's local memory `pub-server.md`, never in git.
- App folder `/var/www/html/eliminacode`, branch `main`.
- https://eliminacode.upgradesrls.com — vhosts `eliminacode.conf` (:80 → https) and
  `eliminacode-le-ssl.conf` (:443, Let's Encrypt, auto-renew). The old name numeratore.upgradesrls.com
  (renamed 2026-10-06) only 301-redirects to eliminacode (`numeratore.conf` / `numeratore-le-ssl.conf`).
- DB `eliminacode`, user `eliminacode` (password only in the server's `config/database.php`).
  The old DB `numeratore` (with the POS tables copied from pub) is kept untouched as a backup.
- Logs: `/var/log/apache2/eliminacode.upgradesrls.com-error.log`. Timezone Europe/Rome (also forced in
  `includes/functions.php`).
- Cron `/etc/cron.d/eliminacode` (copy of `bin/eliminacode.cron`): `bin/midnight-reset.php` at 00:00 closes
  yesterday's open tickets, so every counter restarts from 1. If you change the cron file, copy it again.
- Ticket printer: the server can't reach the shop LAN by itself; needs a route (OpenVPN, never touch
  WireGuard). Until then the totem prints from the browser.

## Workflow (after EVERY change, without being asked)
1. Edit locally, `git commit`, `git push origin main`.
2. Server: `cd /var/www/html/eliminacode && git pull origin main && php migrate.php`.
3. Wait ~3 s, then test: `php -l` changed files; `curl -s -o /dev/null -w '%{http_code}'
   https://eliminacode.upgradesrls.com/login.php`; render changed pages with a CLI script that sets
   `$_SESSION['user_id']`; `tail` the error log.
4. Report the commit hash and the test result.

## Rules
- Deploy only to `/var/www/html/eliminacode`; never to Focacciami, chiamata or ristorante.
- Never hand-edit tracked files on the server; `config/database.php` is gitignored and server-only.
- Public repo: no passwords, keys or tokens in git.
- New tables: `COLLATE utf8mb4_unicode_ci`.
- Never change the WireGuard tunnel on this server.
