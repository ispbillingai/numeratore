<?php
/**
 * Eliminacode — operator page (phone / tablet / PC at the counter).
 * Per service: the number being served, how many wait, and the buttons
 * Avanti (next), Richiama (call again) and Chiama n° (call a given number).
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queue.php';
requireRole(QUEUE_OPERATOR_ROLES);

$pageTitle = 'Eliminacode';
include __DIR__ . '/../includes/header.php';
?>
<style>
.qo-grid { display:grid; gap:18px; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); }
.qo-card { border-radius:16px; overflow:hidden; background:var(--bg-white, #fff); box-shadow:0 4px 18px rgba(0,0,0,.08); }
.qo-head { color:#fff; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; font-weight:700; font-size:1.1rem; }
.qo-body { padding:18px; text-align:center; }
.qo-now { font-size:.85rem; text-transform:uppercase; letter-spacing:.06em; color:var(--text-secondary); }
.qo-num { font-size:4.4rem; font-weight:800; line-height:1.05; margin:4px 0 6px; font-variant-numeric:tabular-nums; }
.qo-info { color:var(--text-secondary); margin-bottom:16px; }
.qo-next { width:100%; font-size:1.5rem; padding:18px; border-radius:14px; border:0; color:#fff; font-weight:800; cursor:pointer; }
.qo-next:disabled { opacity:.5; cursor:default; }
.qo-row { display:flex; gap:10px; margin-top:12px; }
.qo-row .btn { flex:1; justify-content:center; }
.qo-row input { width:90px; text-align:center; font-size:1.1rem; }
.qo-msg { min-height:1.4em; margin-top:10px; font-weight:600; }
.qo-msg.ok { color:var(--success, #16a34a); }
.qo-msg.err { color:var(--danger, #dc2626); }
</style>

<div class="page-header">
    <h1><i class="fas fa-ticket"></i> Eliminacode</h1>
    <?php if (hasRole(['admin'])): ?>
        <a href="/admin/index.php" class="btn btn-outline"><i class="fas fa-cog"></i> Impostazioni</a>
    <?php endif; ?>
</div>

<div class="qo-grid" id="qoGrid"><p>Caricamento…</p></div>

<script>
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let built = '';

function build(services) {
    const sig = services.map(s => s.id + s.name + s.color + s.letter).join('|');
    if (sig === built) return;
    built = sig;
    document.getElementById('qoGrid').innerHTML = services.map(s => `
        <div class="qo-card" data-id="${s.id}">
            <div class="qo-head" style="background:${esc(s.color)}"><span>${esc(s.letter)} · ${esc(s.name)}</span><span class="qo-last"></span></div>
            <div class="qo-body">
                <div class="qo-now">Ora serviamo</div>
                <div class="qo-num">—</div>
                <div class="qo-info"></div>
                <button class="qo-next" style="background:${esc(s.color)}" onclick="act(${s.id}, 'next')"><i class="fas fa-forward"></i> Avanti</button>
                <div class="qo-row">
                    <button class="btn btn-outline" onclick="act(${s.id}, 'recall')"><i class="fas fa-bullhorn"></i> Richiama</button>
                </div>
                <div class="qo-row">
                    <input type="number" min="1" class="form-control qo-pick" placeholder="N°">
                    <button class="btn btn-outline" onclick="callNum(${s.id})"><i class="fas fa-hand-pointer"></i> Chiama numero</button>
                </div>
                <div class="qo-msg"></div>
            </div>
        </div>`).join('') || '<p>Nessun servizio attivo. Aggiungili in Amministrazione › Eliminacode.</p>';
}

function render(j) {
    build(j.services);
    j.services.forEach(s => {
        const card = document.querySelector('.qo-card[data-id="' + s.id + '"]');
        if (!card) return;
        card.querySelector('.qo-num').textContent = s.current || '—';
        card.querySelector('.qo-info').textContent = (s.waiting > 0 ? 'In attesa: ' + s.waiting : 'Nessuno in attesa')
            + (s.called_at ? ' · chiamato alle ' + s.called_at : '');
        card.querySelector('.qo-last').textContent = s.last_taken ? 'Ultimo emesso ' + s.last_taken : '';
        card.querySelector('.qo-next').disabled = s.waiting === 0;
    });
}

function msg(id, text, ok) {
    const el = document.querySelector('.qo-card[data-id="' + id + '"] .qo-msg');
    if (!el) return;
    el.textContent = text; el.className = 'qo-msg ' + (ok ? 'ok' : 'err');
    clearTimeout(el._t); el._t = setTimeout(() => el.textContent = '', 4000);
}

async function act(id, action, number) {
    try {
        const r = await fetch('/api/queue.php', { method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, service_id: id, number }) });
        const j = await r.json();
        if (j.services) render(j);
        msg(id, j.success ? 'Chiamato ' + j.called : (j.message || 'Errore'), j.success);
    } catch (e) { msg(id, 'Errore di connessione', false); }
}

function callNum(id) {
    const inp = document.querySelector('.qo-card[data-id="' + id + '"] .qo-pick');
    const n = parseInt(inp.value, 10);
    if (!n) { inp.focus(); return; }
    act(id, 'call', n);
    inp.value = '';
}

async function poll() {
    try {
        const r = await fetch('/api/queue.php?action=state', { cache: 'no-store' });
        const j = await r.json();
        if (j.success) render(j);
    } catch (e) {}
}
poll(); setInterval(poll, 3000);
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
