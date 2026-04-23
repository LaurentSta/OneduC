@extends('formateur.dashboard')

@section('formateur')
@php
  $entries     = $session->entries;
  $picks       = $session->picks ?? [];
  $picksCount  = count($picks);
  $total       = count($entries);
  $exhausted   = $session->isExhausted();
  $currentPick = $session->currentPick();
  $stateUrl    = route('formateur.roue.state', $session);
  $spinUrl     = route('formateur.roue.spin',  $session);
  $resetUrl    = route('formateur.roue.reset', $session);
@endphp

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<div class="w-full px-6 lg:px-8 pb-10">

  {{-- En-tête --}}
  <div class="bg-white rounded-[20px] shadow-md px-8 pt-5 pb-6 my-6 flex flex-wrap items-center justify-between gap-4">
    <div>
      <nav class="text-sm font-varela text-gray-500 mb-2">
        <ol class="inline-flex items-center space-x-1">
          <li><a href="{{ route('formateur.outils.index') }}" class="text-orangeone hover:underline">Outils numériques</a></li>
          <li><span class="mx-2 text-gray-400">/</span></li>
          <li><a href="{{ route('formateur.roue.index') }}" class="text-orangeone hover:underline">Roue aléatoire</a></li>
          <li><span class="mx-2 text-gray-400">/</span></li>
          <li class="text-gray-400">{{ $session->group?->name }}</li>
        </ol>
      </nav>
      <p class="font-raleway text-2xl text-bleuone">Roue — {{ $session->group?->name }}</p>
      <div class="flex flex-wrap items-center gap-3 mt-2">
        <span class="rounded-full bg-violet-50 px-3 py-1 text-sm font-mono font-semibold text-violet-700">
          Code : {{ $session->access_code }}
        </span>
        <span class="text-sm text-gray-500">
          {{ $picksCount }} / {{ $total }} tirage{{ $picksCount > 1 ? 's' : '' }}
        </span>
      </div>
    </div>
    <a href="{{ route('formateur.roue.index') }}" class="btn-oneduc-outline !px-3 !py-2 !text-sm">
      ← Retour
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6">

    {{-- ── Colonne gauche : QR + contrôles + historique ──────────────── --}}
    <div class="space-y-5">

      {{-- QR code + lien --}}
      <div class="bg-white rounded-[20px] shadow-md p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Lien stagiaires</p>
        <div id="wheel-qr" class="flex justify-center mb-3"></div>
        <p class="text-center text-xs text-gray-500 break-all">{{ $joinUrl }}</p>
        <a href="{{ $joinUrl }}" target="_blank" rel="noopener"
           class="mt-3 w-full inline-flex items-center justify-center gap-2 rounded-[10px] border border-violet-200 bg-violet-50 px-4 py-2 text-xs font-bold text-violet-700 hover:bg-violet-100 transition">
          Ouvrir la vue stagiaire ↗
        </a>
      </div>

      {{-- Boutons d'action --}}
      <div class="bg-white rounded-[20px] shadow-md p-5 space-y-3">
        <button id="btn-spin"
                {{ $exhausted ? 'disabled' : '' }}
                class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-violet-600 px-4 py-3 text-sm font-bold text-white hover:bg-violet-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          @if($exhausted)
            Tous désignés !
          @else
            Tourner la roue
          @endif
        </button>

        @if($picksCount > 0)
          <form method="POST" action="{{ $resetUrl }}">
            @csrf
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Réinitialiser
            </button>
          </form>
        @endif

        @if($exhausted)
          <p class="text-center text-xs text-gray-400">
            Tous les stagiaires ont été désignés. Réinitialisez pour recommencer.
          </p>
        @endif
      </div>

      {{-- Historique des tirages --}}
      @if($picksCount > 0)
        <div class="bg-white rounded-[20px] shadow-md p-5">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">
            Tirages ({{ $picksCount }})
          </p>
          <ol class="space-y-1">
            @foreach($picks as $i => $pickedId)
              @php
                $entry = collect($entries)->firstWhere('id', $pickedId);
                $isCurrent = $pickedId === $session->current_pick_id;
              @endphp
              @if($entry)
                <li class="flex items-center gap-2 text-sm {{ $isCurrent ? 'font-bold text-violet-700' : 'text-gray-600' }}">
                  <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full {{ $isCurrent ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-500' }} text-[10px] font-bold">
                    {{ $i + 1 }}
                  </span>
                  {{ $entry['name'] }}
                  @if($isCurrent)
                    <span class="ml-auto text-[10px] font-semibold text-violet-500">← actuel</span>
                  @endif
                </li>
              @endif
            @endforeach
          </ol>
        </div>
      @endif

    </div>

    {{-- ── Roue Canvas ──────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[20px] shadow-md p-6 flex flex-col items-center justify-center gap-6">

      {{-- Résultat actuel --}}
      <div id="result-banner"
           class="w-full max-w-[480px] rounded-[14px] {{ $currentPick ? 'bg-violet-600' : 'bg-gray-50 border border-dashed border-gray-200' }} px-6 py-4 text-center transition-all">
        @if($currentPick)
          <p class="text-xs font-bold uppercase tracking-wider text-white/70 mb-1">Désigné(e)</p>
          <p class="text-3xl font-bold text-white" id="result-name">{{ $currentPick['name'] }}</p>
        @else
          <p class="text-sm text-gray-400" id="result-name">Tournez la roue pour désigner un stagiaire</p>
        @endif
      </div>

      {{-- Canvas --}}
      <div class="relative">
        {{-- Pointeur --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1 z-10 pointer-events-none">
          <svg width="24" height="32" viewBox="0 0 24 32" fill="none">
            <polygon points="12,30 0,0 24,0" fill="#7C3AED"/>
            <polygon points="12,30 0,0 24,0" fill="#7C3AED" opacity="0.3"/>
          </svg>
        </div>
        <canvas id="wheel-canvas" width="480" height="480"
                class="rounded-full shadow-xl border-4 border-violet-200"></canvas>
      </div>

    </div>

  </div>
</div>

<script>
const ENTRIES     = @json($entries);
const PICKS       = @json($picks);
const CURRENT_ID  = @json($session->current_pick_id);
const PALETTE     = ['#7C3AED','#0F766E','#1D4ED8','#B45309','#BE123C','#0E7490','#15803D','#A16207'];
const SPIN_URL    = @json($spinUrl);
const CSRF_TOKEN  = @json(csrf_token());

// ── QR Code ──────────────────────────────────────────────────────────
new QRCode(document.getElementById('wheel-qr'), {
  text: @json($joinUrl),
  width: 160, height: 160,
  colorDark: '#7C3AED', colorLight: '#ffffff',
});

// ── Canvas & Drawing ─────────────────────────────────────────────────
const canvas = document.getElementById('wheel-canvas');
const ctx    = canvas.getContext('2d');
const CX = canvas.width / 2;
const CY = canvas.height / 2;
const R  = CX - 4;

let currentAngle = 0; // rotation actuelle (radians, sens horaire)
let spinning     = false;

function drawWheel(angle) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  const n = ENTRIES.length;
  if (n === 0) return;

  const segAngle = (2 * Math.PI) / n;

  ENTRIES.forEach((entry, i) => {
    const startA = angle + i * segAngle - Math.PI / 2;
    const endA   = startA + segAngle;

    // Segment
    ctx.beginPath();
    ctx.moveTo(CX, CY);
    ctx.arc(CX, CY, R, startA, endA);
    ctx.closePath();

    // Couleur : grisé si déjà tiré
    const isPicked = PICKS.includes(entry.id);
    ctx.fillStyle = isPicked ? '#e5e7eb' : PALETTE[i % PALETTE.length];
    ctx.fill();
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Texte
    ctx.save();
    ctx.translate(CX, CY);
    ctx.rotate(startA + segAngle / 2);
    ctx.textAlign = 'right';

    const fontSize = Math.max(10, Math.min(16, Math.round(200 / n)));
    ctx.font = `bold ${fontSize}px "Varela Round", Arial, sans-serif`;
    ctx.fillStyle = isPicked ? '#9ca3af' : '#ffffff';

    // Tronquer si trop long
    const maxChars = Math.max(8, Math.round(40 / n));
    const label = entry.name.length > maxChars ? entry.name.slice(0, maxChars) + '…' : entry.name;
    ctx.fillText(label, R - 10, fontSize / 3);
    ctx.restore();
  });

  // Centre
  ctx.beginPath();
  ctx.arc(CX, CY, 24, 0, 2 * Math.PI);
  ctx.fillStyle = '#ffffff';
  ctx.fill();
  ctx.strokeStyle = '#7C3AED';
  ctx.lineWidth = 3;
  ctx.stroke();
}

// ── Animation ease-out ───────────────────────────────────────────────
function easeOut(t) { return 1 - Math.pow(1 - t, 4); }

function animateTo(targetAngle, duration, onDone) {
  const startAngle = currentAngle;
  const delta      = targetAngle - startAngle;
  const startTime  = performance.now();

  function step(now) {
    const elapsed = now - startTime;
    const t       = Math.min(elapsed / duration, 1);
    currentAngle  = startAngle + delta * easeOut(t);
    drawWheel(currentAngle);
    if (t < 1) {
      requestAnimationFrame(step);
    } else {
      currentAngle = targetAngle;
      spinning = false;
      if (onDone) onDone();
    }
  }
  requestAnimationFrame(step);
}

function spinTo(winnerIndex) {
  const n        = ENTRIES.length;
  const segAngle = (2 * Math.PI) / n;

  // Angle du centre du segment gagnant (depuis le haut = -π/2)
  // La roue tourne dans le sens +θ. Le pointeur est en haut.
  // On veut que le segment winnerIndex soit en haut (à -π/2).
  const winnerCenter = winnerIndex * segAngle + segAngle / 2;
  // Pour que ce segment soit au pointeur : currentAngle ≡ -winnerCenter - π/2 (mod 2π)
  const targetNorm   = -winnerCenter - Math.PI / 2;
  // Normaliser pour être au-delà de currentAngle + au moins 5 tours
  const spins        = 5 * 2 * Math.PI;
  const normalizedCurrent = currentAngle % (2 * Math.PI);
  let diff = (targetNorm - normalizedCurrent + 2 * Math.PI) % (2 * Math.PI);
  if (diff < 0.1) diff += 2 * Math.PI;

  const target = currentAngle + spins + diff;
  animateTo(target, 4000, null);
}

// Dessin initial
drawWheel(currentAngle);

// ── Bouton tourner ───────────────────────────────────────────────────
document.getElementById('btn-spin').addEventListener('click', async function () {
  if (spinning) return;
  spinning = true;
  this.disabled = true;

  try {
    const res = await fetch(SPIN_URL, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': CSRF_TOKEN,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    });

    if (!res.ok) {
      const err = await res.json();
      alert(err.error ?? 'Erreur lors du tirage.');
      spinning = false;
      this.disabled = false;
      return;
    }

    const data = await res.json();

    // Mettre à jour PICKS pour griser le gagnant après animation
    PICKS.push(data.winner.id);

    // Animer vers le gagnant
    spinTo(data.winner_index);

    // Mettre à jour le bandeau résultat
    setTimeout(() => {
      const banner = document.getElementById('result-banner');
      const nameEl = document.getElementById('result-name');
      banner.className = banner.className
        .replace('bg-gray-50 border border-dashed border-gray-200', '')
        .replace('bg-violet-600', '') + ' bg-violet-600';
      nameEl.textContent     = data.winner.name;
      nameEl.className       = 'text-3xl font-bold text-white';

      if (!data.is_exhausted) {
        document.getElementById('btn-spin').disabled = false;
      } else {
        document.getElementById('btn-spin').textContent = 'Tous désignés !';
      }
    }, 2500);

  } catch (e) {
    alert('Erreur réseau. Réessayez.');
    spinning = false;
    this.disabled = false;
  }
});
</script>

{{-- Alpine pour sidebar dashboard --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
