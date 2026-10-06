<?php
/**
 * Admin: users. Amministratori and operatori (who call numbers on the operator
 * page). Users are never deleted, only disabled, so the history keeps its names.
 */
require_once __DIR__ . '/../includes/functions.php';
requireRole(['admin']);

$pdo  = getDBConnection();
$me   = (int) $_SESSION['user_id'];
$back = static function (string $msg, bool $ok = true) {
    $_SESSION['users_flash'] = [$msg, $ok];
    header('Location: /admin/users.php');
    exit;
};

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action'] ?? '';
    $id       = (int) ($_POST['id'] ?? 0);
    $fullName = mb_substr(trim((string) ($_POST['full_name'] ?? '')), 0, 100);
    $role     = isset(USER_ROLES[$_POST['role'] ?? '']) ? $_POST['role'] : 'operator';
    $password = (string) ($_POST['password'] ?? '');

    if ($action === 'add') {
        $username = mb_substr(trim((string) ($_POST['username'] ?? '')), 0, 50);
        if ($username === '' || $fullName === '' || strlen($password) < 6) {
            $back('Inserisci nome utente, nome e una password di almeno 6 caratteri.', false);
        }
        $st = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $st->execute([$username]);
        if ($st->fetchColumn()) {
            $back("Il nome utente \"$username\" esiste già.", false);
        }
        $pdo->prepare("INSERT INTO users (username, password, full_name, role, active) VALUES (?, ?, ?, ?, 1)")
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $fullName, $role]);
        logActivity('user_created', 'user', (int) $pdo->lastInsertId(), $username);
        $back('Utente creato.');
    }

    if ($action === 'save' && $id) {
        if ($fullName === '') {
            $back('Il nome non può essere vuoto.', false);
        }
        if ($id === $me) {
            $role = 'admin';   // never lock yourself out of the admin
        }
        $pdo->prepare("UPDATE users SET full_name = ?, role = ? WHERE id = ?")->execute([$fullName, $role, $id]);
        if ($password !== '') {
            if (strlen($password) < 6) {
                $back('La password deve avere almeno 6 caratteri.', false);
            }
            $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
        }
        logActivity('user_updated', 'user', $id, null);
        $back('Utente salvato.');
    }

    if ($action === 'toggle' && $id && $id !== $me) {
        $pdo->prepare("UPDATE users SET active = 1 - active WHERE id = ?")->execute([$id]);
        logActivity('user_toggled', 'user', $id, null);
        $back('Stato dell\'utente aggiornato.');
    }
}

$flash = $_SESSION['users_flash'] ?? null;
unset($_SESSION['users_flash']);
$users = $pdo->query("SELECT id, username, full_name, role, active FROM users ORDER BY active DESC, role, full_name")->fetchAll();

$pageTitle = 'Utenti';
include __DIR__ . '/../includes/header.php';
?>
<style>
.us-table { width:100%; border-collapse:collapse; }
.us-table td, .us-table th { padding:8px 6px; border-bottom:1px solid var(--border, #eee); text-align:left; vertical-align:middle; }
.us-off td { opacity:.55; }
.us-wide { overflow-x:auto; }
</style>

<div class="page-header"><h1><i class="fas fa-users"></i> Utenti</h1></div>

<?php if ($flash): ?>
    <div class="alert mb-lg" style="padding:14px;border-radius:8px;<?= $flash[1] ? 'background:rgba(39,174,96,.1);color:var(--success)' : 'background:rgba(231,76,60,.1);color:var(--danger)' ?>"><?= h($flash[0]) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><h2><i class="fas fa-list"></i> Elenco</h2></div>
    <div class="card-body us-wide">
        <p class="text-muted">Gli <strong>operatori</strong> usano solo la pagina operatore (Avanti, Richiama). Gli utenti non si cancellano: si disattivano.</p>
        <table class="us-table">
            <tr><th>Nome utente</th><th>Nome</th><th>Ruolo</th><th>Nuova password</th><th></th><th></th></tr>
            <?php foreach ($users as $u): $f = 'u' . (int) $u['id']; $known = isset(USER_ROLES[$u['role']]); ?>
                <tr class="<?= $u['active'] ? '' : 'us-off' ?>">
                    <td><strong><?= h($u['username']) ?></strong><?= $u['active'] ? '' : ' <span class="text-muted">(disattivato)</span>' ?></td>
                    <td><input form="<?= $f ?>" type="text" name="full_name" class="form-control" maxlength="100" value="<?= h($u['full_name']) ?>" required></td>
                    <td>
                        <select form="<?= $f ?>" name="role" class="form-control" <?= (int) $u['id'] === $me ? 'disabled' : '' ?>>
                            <?php foreach (USER_ROLES as $r => $label): ?>
                                <option value="<?= $r ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= h($label) ?></option>
                            <?php endforeach; ?>
                            <?php if (!$known): ?><option value="operator" selected>— scegli —</option><?php endif; ?>
                        </select>
                    </td>
                    <td><input form="<?= $f ?>" type="password" name="password" class="form-control" placeholder="lascia vuoto" autocomplete="new-password"></td>
                    <td><button form="<?= $f ?>" class="btn btn-primary btn-sm" title="Salva"><i class="fas fa-save"></i></button></td>
                    <td>
                        <?php if ((int) $u['id'] !== $me): ?>
                            <form method="POST" style="margin:0"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                                <button class="btn btn-outline btn-sm"><?= $u['active'] ? 'Disattiva' : 'Riattiva' ?></button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <?php foreach ($users as $u): ?>
            <form method="POST" id="u<?= (int) $u['id'] ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $u['id'] ?>"></form>
        <?php endforeach; ?>
    </div>
</div>

<div class="card mt-lg">
    <div class="card-header"><h2><i class="fas fa-user-plus"></i> Nuovo utente</h2></div>
    <form method="POST" class="card-body">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
            <div class="form-group"><label class="form-label">Nome utente</label><input type="text" name="username" maxlength="50" class="form-control" required autocomplete="off"></div>
            <div class="form-group"><label class="form-label">Nome e cognome</label><input type="text" name="full_name" maxlength="100" class="form-control" required></div>
        </div>
        <div class="form-row">
            <div class="form-group"><label class="form-label">Ruolo</label>
                <select name="role" class="form-control">
                    <option value="operator">Operatore</option>
                    <option value="admin">Amministratore</option>
                </select></div>
            <div class="form-group"><label class="form-label">Password (almeno 6 caratteri)</label><input type="password" name="password" minlength="6" class="form-control" required autocomplete="new-password"></div>
        </div>
        <button class="btn btn-success"><i class="fas fa-plus"></i> Crea utente</button>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
