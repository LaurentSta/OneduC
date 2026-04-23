<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Roue aléatoire — {{ $session->group?->name }}</title>
  @vite(['resources/css/app.css'])
  <style>
    body { background: #f1f5f9; }
  </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-start p-4 gap-5">
  @php
    $activeEntryIds = $session->activeEntryIds();
    $currentPick = $session->currentPick();
    if ($currentPick && ! in_array((int) $currentPick['id'], $activeEntryIds, true)) {
        $currentPick = null;
    }
  @endphp

  {{-- En-tête --}}
  <div class="w-full max-w-[520px] rounded-2xl bg-white shadow-lg px-6 py-5 mt-2">
    <p class="text-xs uppercase tracking-wide text-gray-400">Code · {{ $session->access_code }}</p>
    <h1 class="text-xl font-bold text-violet-700 mt-1">Roue aléatoire</h1>
    @if($session->group)
      <p class="text-sm text-gray-500 mt-0.5">Groupe : <span class="font-semibold">{{ $session->group->name }}</span></p>
    @endif
  </div>

  {{-- Résultat actuel --}}
  <div id="result-banner"
       class="w-full max-w-[520px] rounded-2xl shadow-lg px-6 py-5 text-center transition-all
              {{ $currentPick ? 'bg-violet-600' : 'bg-white border-2 border-dashed border-gray-200' }}">
    @if($currentPick)
      <p class="text-xs font-bold uppercase tracking-wider text-white/70 mb-1">Désigné(e)</p>
      <p class="text-4xl font-bold text-white" id="result-name">{{ $currentPick['name'] }}</p>
    @else
      <p class="text-sm text-gray-400" id="result-name">
        {{ count($activeEntryIds) > 0 ? 'En attente du premier tirage…' : 'Aucun stagiaire actif pour le moment.' }}
      </p>
    @endif
  </div>

  {{-- Roue Canvas --}}
  <div class="relative">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1 z-10 pointer-events-none">
      <svg width="24" height="32" viewBox="0 0 24 32" fill="none">
        <polygon points="12,30 0,0 24,0" fill="#7C3AED"/>
      </svg>
    </div>
    <canvas id="wheel-canvas" width="400" height="400"
            class="rounded-full shadow-xl border-4 border-violet-200"></canvas>
  </div>

  {{-- Historique --}}
  <div id="picks-list" class="w-full max-w-[520px] space-y-1">
    {{-- Rempli par JS --}}
  </div>

<script>
const ALL_ENTRIES = @json($session->entries);
const PALETTE = ['#7C3AED','#0F766E','#1D4ED8','#B45309','#BE123C','#0E7490','#15803D','#A16207'];
const STATE_URL = @json($stateUrl);

const canvas = document.getElementById('wheel-canvas');
const ctx = canvas.getContext('2d');
const CX = canvas.width / 2, CY = canvas.height / 2, R = CX - 4;

let currentAngle = 0;
let lastStateKey = @json($session->stateKey());
let currentPicks = @json($session->picks ?? []);
let currentPickId = @json($currentPick['id'] ?? null);
let activeEntryIds = @json($activeEntryIds);

function getActiveEntries() {
  return ALL_ENTRIES.filter((entry) => activeEntryIds.includes(Number(entry.id)));
}

function setWaitingBanner() {
  const banner = document.getElementById('result-banner');
  const nameEl = document.getElementById('result-name');
  banner.className = 'w-full max-w-[520px] rounded-2xl shadow-lg px-6 py-5 text-center transition-all bg-white border-2 border-dashed border-gray-200';
  nameEl.textContent = activeEntryIds.length > 0
    ? 'En attente du prochain tirage…'
    : 'Aucun stagiaire actif pour le moment.';
  nameEl.className = 'text-sm text-gray-400';
}

function drawWheel(angle, picks) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  const entries = getActiveEntries();
  const pickedSet = new Set((picks || []).map((id) => Number(id)));
  const n = entries.length;
  if (n === 0) return;
  const segAngle = (2 * Math.PI) / n;

  entries.forEach((entry, i) => {
    const startA = angle + i * segAngle - Math.PI / 2;
    const endA   = startA + segAngle;
    const isPicked = pickedSet.has(Number(entry.id));

    ctx.beginPath();
    ctx.moveTo(CX, CY);
    ctx.arc(CX, CY, R, startA, endA);
    ctx.closePath();
    ctx.fillStyle = isPicked ? '#e5e7eb' : PALETTE[i % PALETTE.length];
    ctx.fill();
    ctx.strokeStyle = '#fff';
    ctx.lineWidth = 2;
    ctx.stroke();

    ctx.save();
    ctx.translate(CX, CY);
    ctx.rotate(startA + segAngle / 2);
    ctx.textAlign = 'right';
    const fontSize = Math.max(10, Math.min(15, Math.round(180 / n)));
    ctx.font = `bold ${fontSize}px "Varela Round", Arial, sans-serif`;
    ctx.fillStyle = isPicked ? '#9ca3af' : '#fff';
    const maxChars = Math.max(8, Math.round(40 / n));
    const label = entry.name.length > maxChars ? entry.name.slice(0, maxChars) + '…' : entry.name;
    ctx.fillText(label, R - 8, fontSize / 3);
    ctx.restore();
  });

  ctx.beginPath();
  ctx.arc(CX, CY, 20, 0, 2 * Math.PI);
  ctx.fillStyle = '#fff';
  ctx.fill();
  ctx.strokeStyle = '#7C3AED';
  ctx.lineWidth = 3;
  ctx.stroke();
}

function easeOut(t) { return 1 - Math.pow(1 - t, 4); }

function animateTo(targetAngle, duration, onDone) {
  const startAngle = currentAngle, delta = targetAngle - startAngle;
  const startTime = performance.now();
  function step(now) {
    const t = Math.min((now - startTime) / duration, 1);
    currentAngle = startAngle + delta * easeOut(t);
    drawWheel(currentAngle, currentPicks);
    if (t < 1) requestAnimationFrame(step);
    else { currentAngle = targetAngle; if (onDone) onDone(); }
  }
  requestAnimationFrame(step);
}

function spinTo(winnerIndex) {
  const n = getActiveEntries().length;
  if (n === 0 || winnerIndex < 0 || winnerIndex >= n) return;
  const segAngle = (2 * Math.PI) / n;
  const winnerCenter = winnerIndex * segAngle + segAngle / 2;
  const targetNorm   = -winnerCenter - Math.PI / 2;
  const spins        = 5 * 2 * Math.PI;
  const normalizedCurrent = currentAngle % (2 * Math.PI);
  let diff = (targetNorm - normalizedCurrent + 2 * Math.PI) % (2 * Math.PI);
  if (diff < 0.1) diff += 2 * Math.PI;
  animateTo(currentAngle + spins + diff, 4000, null);
}

function updateBanner(pick) {
  const banner = document.getElementById('result-banner');
  const nameEl = document.getElementById('result-name');
  if (pick) {
    banner.className = 'w-full max-w-[520px] rounded-2xl shadow-lg px-6 py-5 text-center transition-all bg-violet-600';
    nameEl.textContent = pick.name;
    nameEl.className   = 'text-4xl font-bold text-white';
    return;
  }

  setWaitingBanner();
}

function updatePicksList(picks) {
  const list = document.getElementById('picks-list');
  if (!picks.length) { list.innerHTML = ''; return; }
  list.innerHTML = picks.map((id, i) => {
    const entry = ALL_ENTRIES.find((e) => Number(e.id) === Number(id));
    if (!entry) return '';
    const isCurrent = Number(id) === Number(currentPickId);
    return `<div class="bg-white rounded-[12px] shadow-sm px-4 py-2.5 flex items-center gap-3
      ${isCurrent ? 'border-2 border-violet-400' : 'border border-gray-100'}">
      <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-[10px] font-bold
        ${isCurrent ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-500'}">
        ${i + 1}
      </span>
      <span class="text-sm font-semibold ${isCurrent ? 'text-violet-700' : 'text-gray-700'}">${entry.name}</span>
      ${isCurrent ? '<span class="ml-auto text-[10px] font-bold text-violet-500">← actuel</span>' : ''}
    </div>`;
  }).join('');
}

// Dessin initial
drawWheel(currentAngle, currentPicks);
updatePicksList(currentPicks);

// Polling
async function poll() {
  try {
    const res  = await fetch(STATE_URL, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();

    if (data.state_key === lastStateKey) return;
    lastStateKey = data.state_key;

    currentPicks  = data.picks || [];
    currentPickId = data.current_pick?.id ?? null;
    activeEntryIds = (data.active_entry_ids || []).map((id) => Number(id));

    if (data.current_index !== null && data.current_index !== undefined) {
      // Animer la roue vers le nouveau gagnant
      spinTo(data.current_index);

      setTimeout(() => {
        if (data.current_pick) updateBanner(data.current_pick);
        else updateBanner(null);
        updatePicksList(currentPicks);
      }, 2500);
    } else {
      drawWheel(currentAngle, currentPicks);
      updateBanner(data.current_pick ?? null);
      updatePicksList(currentPicks);
    }
  } catch {}
}

setInterval(poll, 2000);
</script>

</body>
</html>
