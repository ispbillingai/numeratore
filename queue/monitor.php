<?php
/**
 * Eliminacode — staff/customer monitor (TV, 16:9).
 *
 * Opened without a login: /queue/monitor.php?k=<settings.queue.key>.
 * Left: the number being served per service (flashes with a chime and a voice
 * on every call) and the last numbers called. Right: product tiles in rotation
 * (Admin › Eliminacode) and the weather. Bottom: news headlines.
 * Sound needs one click on the screen, or Chrome started with
 * --autoplay-policy=no-user-gesture-required.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/queue.php';

$key = (string) ($_GET['k'] ?? '');
if (!queueKeyOk($key)) {
    http_response_code(403);
    exit('Link del monitor non valido. Copialo da Admin › Eliminacode.');
}
$brand = (string) getDBConnection()->query("SELECT name FROM workspaces LIMIT 1")->fetchColumn();
$logo  = brandLogoUrl();
$h     = static fn($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="it">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Monitor eliminacode</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root { --bg:#0b1424; --panel:#14213a; --panel2:#1b2b4a; --text:#fff; --muted:rgba(255,255,255,.65); --accent:#f5b301; }
* { box-sizing:border-box; }
html,body { margin:0; height:100%; background:var(--bg); color:var(--text); font-family:'DM Sans',system-ui,sans-serif; overflow:hidden; cursor:none; }
.screen { height:100vh; display:grid; grid-template-rows:11vh 1fr 8vh; gap:1.4vh; padding:1.4vh 1.4vw; }

/* Top bar */
.top { display:flex; align-items:center; gap:2vw; background:var(--panel); border-radius:18px; padding:0 2vw; }
.top img { max-height:8vh; max-width:22vw; }
.top .brand { font-size:4vh; font-weight:800; }
.top .clock { margin-left:auto; text-align:right; }
.top .time { font-size:5.4vh; font-weight:800; line-height:1; font-variant-numeric:tabular-nums; }
.top .date { font-size:2vh; color:var(--muted); text-transform:capitalize; }
.top .wnow { display:flex; align-items:center; gap:1vw; padding-left:2vw; border-left:2px solid rgba(255,255,255,.12); }
.top .wnow .ic { font-size:6vh; line-height:1; }
.top .wnow .t { font-size:4.6vh; font-weight:800; line-height:1; }
.top .wnow .c { font-size:1.9vh; color:var(--muted); }

/* Middle */
.mid { display:grid; grid-template-columns:42% 1fr; gap:1.4vw; min-height:0; }
.left { display:flex; flex-direction:column; gap:1.4vh; min-height:0; }
.serving { flex:1; display:grid; gap:1.4vh; min-height:0; }
.svc { border-radius:18px; padding:1.5vh 1.6vw; display:flex; flex-direction:column; justify-content:center; min-height:0; overflow:hidden; position:relative; }
.svc .lbl { font-size:2.4vh; font-weight:700; text-transform:uppercase; letter-spacing:.06em; opacity:.92; }
.svc .num { font-weight:800; line-height:1; font-variant-numeric:tabular-nums; }
.svc .meta { font-size:2vh; opacity:.9; }
.svc.flash { animation:flash 1s ease-in-out 4; }
@keyframes flash { 50% { filter:brightness(1.6); transform:scale(1.02); } }
.recent { background:var(--panel); border-radius:18px; padding:1.4vh 1.4vw; }
.recent h3 { margin:0 0 1vh; font-size:2vh; color:var(--muted); text-transform:uppercase; letter-spacing:.08em; }
.recent .list { display:flex; gap:.8vw; flex-wrap:wrap; }
.recent .chip { background:var(--panel2); border-radius:10px; padding:.7vh .9vw; font-size:3vh; font-weight:800; border-left:6px solid #888; }
.recent .chip small { display:block; font-size:1.5vh; font-weight:600; color:var(--muted); }

.right { display:grid; grid-template-rows:1fr 17vh; gap:1.4vh; min-height:0; }
.slides { position:relative; border-radius:18px; overflow:hidden; background:var(--panel); min-height:0; }
.slide { position:absolute; inset:0; opacity:0; transition:opacity 1s; display:flex; flex-direction:column; justify-content:flex-end; }
.slide.on { opacity:1; }
.slide .img { position:absolute; inset:0; background-size:cover; background-position:center; }
.slide .cap { position:relative; padding:3vh 2vw 2.6vh; background:linear-gradient(transparent, rgba(0,0,0,.85) 45%); }
.slide .ttl { font-size:5.4vh; font-weight:800; line-height:1.05; }
.slide .stl { font-size:2.6vh; color:rgba(255,255,255,.88); margin-top:.6vh; }
.slide .price { position:absolute; top:2.4vh; right:1.6vw; background:var(--accent); color:#111; font-weight:800; font-size:4.4vh; padding:.8vh 1.4vw; border-radius:14px; }
.slide.noimg .img { background:linear-gradient(135deg,#1e3a8a,#7c3aed); }
.slides .dots { position:absolute; bottom:1.2vh; right:1.4vw; display:flex; gap:.5vw; }
.slides .dots i { width:1vh; height:1vh; border-radius:50%; background:rgba(255,255,255,.35); }
.slides .dots i.on { background:#fff; }
.placeholder { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; color:var(--muted); font-size:3vh; text-align:center; padding:2vw; }

.forecast { display:grid; grid-template-columns:repeat(4,1fr); gap:1vw; }
.day { background:var(--panel); border-radius:18px; display:flex; flex-direction:column; align-items:center; justify-content:center; }
.day .d { font-size:2vh; color:var(--muted); text-transform:capitalize; }
.day .ic { font-size:5vh; line-height:1.2; }
.day .mm { font-size:2.4vh; font-weight:700; }
.day .mm span { color:var(--muted); font-weight:600; }

/* News ticker */
.news { display:flex; align-items:center; background:var(--panel); border-radius:18px; overflow:hidden; }
.news .tag { background:#dc2626; height:100%; display:flex; align-items:center; padding:0 1.6vw; font-weight:800; font-size:2.4vh; letter-spacing:.06em; flex:none; }
.news .track { flex:1; overflow:hidden; white-space:nowrap; position:relative; height:100%; }
.news .run { position:absolute; top:50%; transform:translateY(-50%); white-space:nowrap; font-size:3vh; font-weight:600; will-change:left; }
.news .run span::after { content:'•'; color:var(--accent); margin:0 2vw; }

/* Full-screen call */
.call { position:fixed; inset:0; display:none; align-items:center; justify-content:center; flex-direction:column; z-index:20; background:rgba(5,10,20,.92); }
.call.show { display:flex; }
.call .box { border-radius:36px; padding:5vh 6vw; text-align:center; min-width:50vw; box-shadow:0 20px 80px rgba(0,0,0,.6); animation:pop .4s ease-out; }
.call .who { font-size:4.4vh; font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
.call .big { font-size:26vh; font-weight:800; line-height:1; margin:2vh 0; }
.call .go { font-size:3.6vh; font-weight:600; }
@keyframes pop { from { transform:scale(.7); opacity:0; } }

.sound { position:fixed; bottom:11vh; right:2vw; background:#f59e0b; color:#111; padding:1.2vh 1.4vw; border-radius:12px; font-weight:700; font-size:2vh; cursor:pointer; z-index:30; display:none; }
.offline { position:fixed; top:1vh; left:50%; transform:translateX(-50%); background:#dc2626; padding:.6vh 1.2vw; border-radius:10px; font-weight:700; display:none; z-index:30; }
</style>
</head>
<body>
<div class="screen">
    <div class="top">
        <?php if ($logo): ?><img src="<?= $h($logo) ?>" alt="<?= $h($brand) ?>"><?php else: ?><div class="brand"><?= $h($brand) ?></div><?php endif; ?>
        <div class="clock"><div class="time" id="time">--:--</div><div class="date" id="date"></div></div>
        <div class="wnow" id="wnow" style="display:none"><div class="ic" id="wIc"></div><div><div class="t" id="wT"></div><div class="c" id="wC"></div></div></div>
    </div>

    <div class="mid">
        <div class="left">
            <div class="serving" id="serving"></div>
            <div class="recent"><h3>Ultimi chiamati</h3><div class="list" id="recent"></div></div>
        </div>
        <div class="right">
            <div class="slides" id="slides"><div class="placeholder">Aggiungi i prodotti da mostrare in Admin › Eliminacode</div></div>
            <div class="forecast" id="forecast"></div>
        </div>
    </div>

    <div class="news"><div class="tag">NOTIZIE</div><div class="track" id="track"><div class="run" id="run"></div></div></div>
</div>

<div class="call" id="call"><div class="box" id="callBox"><div class="who" id="callWho"></div><div class="big" id="callNum"></div><div class="go">È il tuo turno!</div></div></div>
<div class="sound" id="sound">🔊 Tocca per attivare l'audio</div>
<div class="offline" id="offline">Connessione assente</div>

<script>
const KEY = <?= json_encode($key) ?>;
const API = '/api/queue.php';
const q = s => document.getElementById(s);
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

/* ---------- Clock ---------- */
function tick() {
    const d = new Date();
    q('time').textContent = d.toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
    q('date').textContent = d.toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long' });
}
tick(); setInterval(tick, 1000);

/* ---------- Sound ---------- */
let audio = null;
function initAudio() {
    try { audio = audio || new (window.AudioContext || window.webkitAudioContext)(); audio.resume(); } catch (e) {}
    setTimeout(() => q('sound').style.display = (audio && audio.state === 'running') ? 'none' : 'block', 300);
}
document.addEventListener('click', initAudio);
initAudio();
function chime() {
    if (!audio || audio.state !== 'running') return;
    [[880, 0], [660, .45]].forEach(([f, t]) => {
        const o = audio.createOscillator(), g = audio.createGain();
        o.type = 'sine'; o.frequency.value = f;
        g.gain.setValueAtTime(0, audio.currentTime + t);
        g.gain.linearRampToValueAtTime(.5, audio.currentTime + t + .03);
        g.gain.exponentialRampToValueAtTime(.001, audio.currentTime + t + .9);
        o.connect(g).connect(audio.destination);
        o.start(audio.currentTime + t); o.stop(audio.currentTime + t + 1);
    });
}
function speak(label, service) {
    if (!('speechSynthesis' in window) || !audio || audio.state !== 'running') return;
    const m = label.match(/^([A-Za-z]*)0*(\d+)$/);
    const text = 'Numero ' + (m ? (m[1] ? m[1] + ' ' : '') + m[2] : label) + (service ? ', ' + service : '');
    const u = new SpeechSynthesisUtterance(text);
    u.lang = 'it-IT'; u.rate = .95;
    setTimeout(() => speechSynthesis.speak(u), 1100);
}

/* ---------- Queue state ---------- */
let lastStamp = null, callTimer = null;
const lastCalled = {};
function renderServing(services) {
    const box = q('serving');
    const n = Math.max(1, services.length);
    box.style.gridTemplateRows = 'repeat(' + n + ', 1fr)';
    const size = n === 1 ? 30 : n === 2 ? 17 : n === 3 ? 12 : 9;
    box.innerHTML = services.map(s => `
        <div class="svc" data-id="${s.id}" style="background:${esc(s.color)}">
            <div class="lbl">${esc(s.name)} · ora serviamo</div>
            <div class="num" style="font-size:${size}vh">${esc(s.current || '—')}</div>
            <div class="meta">${s.waiting > 0 ? 'In attesa: ' + s.waiting : 'Nessuno in attesa'}</div>
        </div>`).join('') || '<div class="placeholder">Nessun servizio attivo</div>';
}
function showCall(s) {
    q('callBox').style.background = s.color;
    q('callWho').textContent = s.name;
    q('callNum').textContent = s.current;
    q('call').classList.add('show');
    chime(); speak(s.current, s.name);
    const el = document.querySelector('.svc[data-id="' + s.id + '"]');
    if (el) el.classList.add('flash');
    clearTimeout(callTimer);
    callTimer = setTimeout(() => q('call').classList.remove('show'), 6000);
}
async function poll() {
    try {
        const r = await fetch(API + '?action=state&k=' + encodeURIComponent(KEY), { cache: 'no-store' });
        const j = await r.json();
        if (!j.success) throw new Error();
        q('offline').style.display = 'none';
        renderServing(j.services);
        const serving = j.services.map(s => s.current);
        q('recent').innerHTML = j.recent.filter(c => !serving.includes(c.label)).slice(0, 7).map(c =>
            `<div class="chip" style="border-left-color:${esc(c.color)}">${esc(c.label)}<small>${esc(c.time)}</small></div>`).join('');
        if (lastStamp !== null && j.stamp !== lastStamp) {
            // Which service changed: its id:count differs from the last poll.
            const changed = j.services.find(s => s.current && lastCalled[s.id] !== s.stamp);
            const pick = changed || j.services.find(s => s.current);
            if (pick) showCall(pick);
        }
        lastStamp = j.stamp;
        j.services.forEach(s => lastCalled[s.id] = s.stamp);
    } catch (e) {
        q('offline').style.display = 'block';
    }
}

/* ---------- Product tiles ---------- */
let slides = [], slideIdx = 0, slideSecs = 8, slideTimer = null, slidesSig = '';
function renderSlides() {
    const box = q('slides');
    if (!slides.length) { box.innerHTML = '<div class="placeholder">Aggiungi i prodotti da mostrare in Admin › Eliminacode</div>'; return; }
    box.innerHTML = slides.map((s, i) => `
        <div class="slide${s.image ? '' : ' noimg'}${i === 0 ? ' on' : ''}">
            <div class="img" ${s.image ? `style="background-image:url('${esc(s.image)}')"` : ''}></div>
            ${s.price ? `<div class="price">${esc(s.price)}</div>` : ''}
            <div class="cap"><div class="ttl">${esc(s.title)}</div>${s.subtitle ? `<div class="stl">${esc(s.subtitle)}</div>` : ''}</div>
        </div>`).join('') + (slides.length > 1 ? '<div class="dots">' + slides.map((_, i) => `<i class="${i === 0 ? 'on' : ''}"></i>`).join('') + '</div>' : '');
    slideIdx = 0;
    clearInterval(slideTimer);
    if (slides.length > 1) slideTimer = setInterval(nextSlide, slideSecs * 1000);
}
function nextSlide() {
    const els = document.querySelectorAll('.slide'), dots = document.querySelectorAll('.dots i');
    if (!els.length) return;
    els[slideIdx].classList.remove('on'); if (dots[slideIdx]) dots[slideIdx].classList.remove('on');
    slideIdx = (slideIdx + 1) % els.length;
    els[slideIdx].classList.add('on'); if (dots[slideIdx]) dots[slideIdx].classList.add('on');
}

/* ---------- Weather ---------- */
const WMO = c => c === 0 ? ['☀️', 'Sereno'] : c <= 2 ? ['🌤️', 'Poco nuvoloso'] : c === 3 ? ['☁️', 'Nuvoloso']
    : c <= 48 ? ['🌫️', 'Nebbia'] : c <= 57 ? ['🌦️', 'Pioviggine'] : c <= 67 ? ['🌧️', 'Pioggia']
    : c <= 77 ? ['🌨️', 'Neve'] : c <= 82 ? ['🌦️', 'Rovesci'] : c <= 86 ? ['🌨️', 'Neve'] : ['⛈️', 'Temporale'];
function renderWeather(w) {
    if (!w) { q('wnow').style.display = 'none'; q('forecast').innerHTML = ''; return; }
    const [ic, txt] = WMO(w.code);
    q('wIc').textContent = ic; q('wT').textContent = w.temp + '°';
    q('wC').textContent = w.city + ' · ' + txt;
    q('wnow').style.display = 'flex';
    q('forecast').innerHTML = w.days.map((d, i) => {
        const name = i === 0 ? 'Oggi' : new Date(d.date + 'T12:00').toLocaleDateString('it-IT', { weekday: 'long' });
        return `<div class="day"><div class="d">${esc(name)}</div><div class="ic">${WMO(d.code)[0]}</div><div class="mm">${d.max}° <span>${d.min}°</span></div></div>`;
    }).join('');
}

/* ---------- News ticker ---------- */
let newsSig = '', tickerX = 0, tickerW = 0;
function renderNews(list) {
    const sig = list.join('|');
    if (sig === newsSig) return;
    newsSig = sig;
    q('run').innerHTML = list.length ? list.map(t => `<span>${esc(t)}</span>`).join('') : '';
    tickerW = q('run').scrollWidth;
    tickerX = q('track').clientWidth;
}
let lastT = 0;
function animate(t) {
    const dt = lastT ? Math.min(100, t - lastT) : 0; lastT = t;
    if (tickerW) {
        tickerX -= dt * 0.11 * (window.innerWidth / 1920);
        if (tickerX < -tickerW) tickerX = q('track').clientWidth;
        q('run').style.left = tickerX + 'px';
    }
    requestAnimationFrame(animate);
}
requestAnimationFrame(animate);

async function feed() {
    try {
        const r = await fetch(API + '?action=feed&k=' + encodeURIComponent(KEY), { cache: 'no-store' });
        const j = await r.json();
        if (!j.success) return;
        renderWeather(j.weather);
        renderNews(j.news || []);
        const sig = JSON.stringify(j.slides) + j.seconds;
        if (sig !== slidesSig) { slidesSig = sig; slides = j.slides; slideSecs = j.seconds; renderSlides(); }
    } catch (e) {}
}

poll(); setInterval(poll, 2000);
feed(); setInterval(feed, 5 * 60 * 1000);
// A daily reload keeps a 24/7 screen fresh.
setTimeout(() => location.reload(), 6 * 60 * 60 * 1000);
</script>
</body>
</html>
