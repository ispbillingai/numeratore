# Eliminacode Upgrade

Take-a-number queue system (PHP 8 + MariaDB):

- **Totem** (`/queue/totem.php?k=…`): the customer taps a service and gets a numbered ticket,
  printed on a network ESC/POS printer or, without one, from the browser.
- **Monitor** (`/queue/monitor.php?k=…`): the number being served per service (chime + voice),
  last numbers called, rotating product tiles, weather (Open-Meteo) and news (RSS).
- **Operatore** (`/queue/operator.php`): Avanti, Richiama, Chiama numero.
- **Amministrazione** (`/admin/`): services, product tiles, screen links, weather, news, printer, users.

The totem and monitor run without a login: their links carry a secret key (Amministrazione ›
Eliminacode › Link degli schermi, where it can be regenerated).

## Install
1. Copy `config/database.example.php` to `config/database.php` and fill in the DB credentials.
2. Import `database_schema.sql` into an empty database.
3. Run `php migrate.php`.
4. Log in as `admin` / `admin123` and change the password in Amministrazione › Utenti.

Optional logo: `assets/img/logo.png` (not in git).

## Screens
- Totem: `chrome --kiosk --kiosk-printing "<totem link>"`
- Monitor: `chrome --kiosk --autoplay-policy=no-user-gesture-required "<monitor link>"`
