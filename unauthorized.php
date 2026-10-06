<?php
/** Shown when a user opens a page their role cannot use. */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Accesso negato';
include __DIR__ . '/includes/header.php';
?>
<div style="text-align:center; padding:100px 20px;">
    <i class="fas fa-lock" style="font-size:4rem; color:var(--danger); margin-bottom:24px;"></i>
    <h1 style="margin-bottom:16px;">Accesso negato</h1>
    <p class="text-muted" style="margin-bottom:24px;">Non hai il permesso di aprire questa pagina.</p>
    <a href="/" class="btn btn-primary"><i class="fas fa-home"></i> Vai alla pagina iniziale</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
