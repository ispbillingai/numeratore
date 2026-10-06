<?php
/**
 * Common functions — Eliminacode Upgrade.
 * Session, login and roles, JSON replies, activity log, app name and logo.
 *
 * Roles: 'admin' (everything) and 'operator' (calls numbers on queue/operator.php).
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/settings.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const APP_DEFAULT_NAME = 'Eliminacode Upgrade';
const USER_ROLES = ['admin' => 'Amministratore', 'operator' => 'Operatore'];

/** HTML-escape. */
function h($s): string
{
    return htmlspecialchars((string) ($s ?? ''), ENT_QUOTES, 'UTF-8');
}

/** The name shown on the pages, the totem and the ticket (Amministrazione › Eliminacode › Impostazioni). */
function appName(): string
{
    $n = trim((string) (getSetting('queue', [])['brand'] ?? ''));
    return $n !== '' ? $n : APP_DEFAULT_NAME;
}

/** The logo (assets/img/logo.png) as a web path, or null when there is none. */
function brandLogoUrl(): ?string
{
    $f = __DIR__ . '/../assets/img/logo.png';
    return is_file($f) ? '/assets/img/logo.png?v=' . filemtime($f) : null;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/** The logged-in, active user, or null. */
function getCurrentUser(): ?array
{
    static $user = false;
    if (!isLoggedIn()) {
        return null;
    }
    if ($user === false || (int) ($user['id'] ?? 0) !== (int) $_SESSION['user_id']) {
        $st = getDBConnection()->prepare("SELECT * FROM users WHERE id = ? AND active = 1");
        $st->execute([$_SESSION['user_id']]);
        $user = $st->fetch() ?: null;
    }
    return $user;
}

function hasRole($roles): bool
{
    $user = getCurrentUser();
    return $user && in_array($user['role'], (array) $roles, true);
}

function requireLogin(): void
{
    if (!getCurrentUser()) {
        header('Location: /login.php');
        exit;
    }
}

function requireRole($roles): void
{
    requireLogin();
    if (!hasRole($roles)) {
        header('Location: /unauthorized.php');
        exit;
    }
}

/** Where a user lands after login. */
function homeUrl(?string $role): string
{
    return $role === 'admin' ? '/admin/index.php' : '/queue/operator.php';
}

function jsonResponse($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function logActivity(string $action, ?string $entityType = null, ?int $entityId = null, $details = null): void
{
    try {
        getDBConnection()->prepare(
            "INSERT INTO activity_log (user_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([
            $_SESSION['user_id'] ?? null,
            $action,
            $entityType,
            $entityId,
            $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        error_log('logActivity: ' . $e->getMessage());
    }
}
