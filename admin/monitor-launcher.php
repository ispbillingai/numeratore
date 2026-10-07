<?php
/**
 * Admin: downloads "Monitor eliminacode.bat" for the Windows PC behind the TV.
 * It opens the monitor in Chrome (or Edge) full screen with the sound on from
 * the start: --autoplay-policy=no-user-gesture-required, in its own profile so
 * the flags apply even when the browser is already open. Copied into
 * shell:startup it starts the monitor when the PC is switched on.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queue.php';
requireRole(['admin']);

$https = ($_SERVER['HTTPS'] ?? '') === 'on' || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
$url   = ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'eliminacode.upgradesrls.com')
       . '/queue/monitor.php?k=' . queueSettings()['key'];

$bat = [
    '@echo off',
    'rem Monitor eliminacode: schermo intero con l\'audio gia\' attivo.',
    'rem Per avviarlo all\'accensione del PC: Win+R, scrivi shell:startup e copia li\' questo file.',
    'rem Per uscire dallo schermo intero: Alt+F4.',
    'set "URL=' . $url . '"',
    'set "B=%ProgramFiles%\Google\Chrome\Application\chrome.exe"',
    'if not exist "%B%" set "B=%ProgramFiles(x86)%\Google\Chrome\Application\chrome.exe"',
    'if not exist "%B%" set "B=%LocalAppData%\Google\Chrome\Application\chrome.exe"',
    'if not exist "%B%" set "B=%ProgramFiles(x86)%\Microsoft\Edge\Application\msedge.exe"',
    'if not exist "%B%" set "B=%ProgramFiles%\Microsoft\Edge\Application\msedge.exe"',
    'if not exist "%B%" (',
    '  echo Chrome o Edge non trovato. Installa Google Chrome e riprova.',
    '  pause',
    '  exit /b 1',
    ')',
    'start "" "%B%" --kiosk --autoplay-policy=no-user-gesture-required --user-data-dir="%LocalAppData%\EliminacodeMonitor" --no-first-run --no-default-browser-check --disable-session-crashed-bubble --disable-features=Translate "%URL%"',
];

header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="Monitor eliminacode.bat"');
header('Cache-Control: no-store');
echo implode("\r\n", $bat), "\r\n";
