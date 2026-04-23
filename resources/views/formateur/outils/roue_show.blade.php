@extends('formateur.dashboard')

@section('formateur')
@php
  $entries = $session->entries ?? [];
  $picks = $session->picks ?? [];
  $picksCount = count($picks);
  $totalEntries = count($entries);
  $activeEntryIds = $session->activeEntryIds();
  $activeCount = count($activeEntryIds);
  $remainingCount = count($session->availableEntries());
  $hasActiveEntries = $activeCount > 0;
  $exhausted = $hasActiveEntries && $remainingCount === 0;
  $currentPick = $session->currentPick();
  if ($currentPick && ! in_array((int) $currentPick['id'], $activeEntryIds, true)) {
      $currentPick = null;
  }
  $currentPickId = $currentPick['id'] ?? null;
  $spinUrl = route('formateur.roue.spin', $session);
  $resetUrl = route('formateur.roue.reset', $session);
  $spinLabel = ! $hasActiveEntries
      ? 'Aucun stagiaire actif'
      : ($exhausted ? 'Tous désignés !' : 'Tourner la roue');
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
          {{ $picksCount }} tirage{{ $picksCount > 1 ? 's' : '' }}
        </span>
        <span id="header-active-summary" class="text-sm text-gray-500">
          {{ $activeCount }} / {{ $totalEntries }} stagiaire{{ $totalEntries > 1 ? 's' : '' }} actif{{ $activeCount > 1 ? 's' : '' }}
        </span>
      </div>
    </div>
    <a href="{{ route('formateur.roue.index') }}" class="btn-oneduc-outline !px-3 !py-2 !text-sm">
      ← Retour
    </a>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-[340px_1fr] gap-6">

    {{-- ── Colonne gauche : QR + contrôles + participants + historique --}}
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
                {{ (! $hasActiveEntries || $exhausted) ? 'disabled' : '' }}
                class="w-full inline-flex items-center justify-center gap-2 rounded-[10px] bg-violet-600 px-4 py-3 text-sm font-bold text-white hover:bg-violet-700 transition disabled:opacity-40 disabled:cursor-not-allowed">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          <span id="btn-spin-label">{{ $spinLabel }}</span>
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

        <p id="spin-help" class="text-center text-xs text-gray-400">
          @if(! $hasActiveEntries)
            Cochez au moins un stagiaire pour activer la roue.
          @elseif($exhausted)
            Tous les stagiaires actifs ont été désignés. Réinitialisez pour recommencer.
          @endif
        </p>
      </div>

      {{-- Participants actifs --}}
      <div class="bg-white rounded-[20px] shadow-md p-5">
        <div class="flex items-center justify-between gap-2 mb-3">
          <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Stagiaires sur la roue</p>
          <span id="participants-count" class="text-[11px] font-semibold text-violet-700">
            {{ $activeCount }} / {{ $totalEntries }} actifs
          </span>
        </div>

        <div class="max-h-[280px] overflow-y-auto space-y-1 pr-1">
          @foreach($entries as $entry)
            @php $isActive = in_array((int) $entry['id'], $activeEntryIds, true); @endphp
            <label class="flex items-center gap-3 rounded-[10px] px-3 py-2 border transition {{ $isActive ? 'border-violet-200 bg-violet-50/60' : 'border-gray-100 bg-white' }}">
              <input type="checkbox"
                     class="participant-checkbox h-4 w-4 rounded border-gray-300 text-violet-600 focus:ring-violet-500"
                     value="{{ (int) $entry['id'] }}"
                     {{ $isActive ? 'checked' : '' }}>
              <span class="text-sm {{ $isActive ? 'text-violet-700 font-semibold' : 'text-gray-600' }}">
                {{ $entry['name'] }}
              </span>
            </label>
          @endforeach
        </div>

        <div class="flex flex-wrap gap-2 mt-4">
          <button type="button" id="btn-select-all"
                  class="inline-flex items-center justify-center rounded-[8px] border border-violet-200 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-100 transition">
            Tout cocher
          </button>
          <button type="button" id="btn-clear-all"
                  class="inline-flex items-center justify-center rounded-[8px] border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-100 transition">
            Tout décocher
          </button>
          <span id="participants-status" class="ml-auto text-[11px] text-gray-400"></span>
        </div>
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
                $isCurrent = (int) $pickedId === (int) $currentPickId;
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

    {{-- ── Roue Canvas --}}
    <div class="bg-white rounded-[20px] shadow-md p-6 flex flex-col items-center justify-center gap-6">

      {{-- Résultat actuel --}}
      <div id="result-banner"
           class="w-full max-w-[480px] rounded-[14px] {{ $currentPick ? 'bg-violet-600' : 'bg-gray-50 border border-dashed border-gray-200' }} px-6 py-4 text-center transition-all">
        @if($currentPick)
          <p class="text-xs font-bold uppercase tracking-wider text-white/70 mb-1">Désigné(e)</p>
          <p class="text-3xl font-bold text-white" id="result-name">{{ $currentPick['name'] }}</p>
        @else
          <p class="text-sm text-gray-400" id="result-name">
            {{ $hasActiveEntries ? 'Tournez la roue pour désigner un stagiaire' : 'Aucun stagiaire actif sur la roue' }}
          </p>
        @endif
      </div>

      {{-- Canvas --}}
      <div class="relative">
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
const ALL_ENTRIES = @json($entries);
const PICKS = @json($picks);
let activeEntryIds = @json($activeEntryIds);
const PALETTE = ['#7C3AED','#0F766E','#1D4ED8','#B45309','#BE123C','#0E7490','#15803D','#A16207'];
const SPIN_URL = @json($spinUrl);
const PARTICIPANTS_URL = @json($participantsUrl);
const CSRF_TOKEN = @json(csrf_token());

const spinButton = document.getElementById('btn-spin');
const spinButtonLabel = document.getElementById('btn-spin-label');
const spinHelp = document.getElementById('spin-help');
const participantsCount = document.getElementById('participants-count');
const participantsStatus = document.getElementById('participants-status');
const headerActiveSummary = document.getElementById('header-active-summary');
const resultBanner = document.getElementById('result-banner');
const resultName = document.getElementById('result-name');
const participantCheckboxes = Array.from(document.querySelectorAll('.participant-checkbox'));
const selectAllButton = document.getElementById('btn-select-all');
const clearAllButton = document.getElementById('btn-clear-all');

// ── QR Code ──────────────────────────────────────────────────────────
new QRCode(document.getElementById('wheel-qr'), {
  text: @json($joinUrl),
  width: 160,
  height: 160,
  colorDark: '#7C3AED',
  colorLight: '#ffffff',
});

// ── Canvas & Drawing ─────────────────────────────────────────────────
const canvas = document.getElementById('wheel-canvas');
const ctx = canvas.getContext('2d');
const CX = canvas.width / 2;
const CY = canvas.height / 2;
const R = CX - 4;

let currentAngle = 0;
let spinning = false;
let savingParticipants = false;
let saveParticipantsQueued = false;

function getActiveEntries() {
  return ALL_ENTRIES.filter((entry) => activeEntryIds.includes(Number(entry.id)));
}

function getAvailableCount() {
  const pickedSet = new Set(PICKS.map((id) => Number(id)));
  return getActiveEntries().filter((entry) => !pickedSet.has(Number(entry.id))).length;
}

function updateParticipantRows() {
  participantCheckboxes.forEach((input) => {
    const isActive = activeEntryIds.includes(Number(input.value));
    const label = input.closest('label');
    if (!label) return;
    label.classList.toggle('border-violet-200', isActive);
    label.classList.toggle('bg-violet-50/60', isActive);
    label.classList.toggle('border-gray-100', !isActive);
    label.classList.toggle('bg-white', !isActive);
    const text = label.querySelector('span');
    if (text) {
      text.classList.toggle('text-violet-700', isActive);
      text.classList.toggle('font-semibold', isActive);
      text.classList.toggle('text-gray-600', !isActive);
    }
  });
}

function updateCounters() {
  const activeCount = activeEntryIds.length;
  const total = ALL_ENTRIES.length;
  participantsCount.textContent = `${activeCount} / ${total} actifs`;
  headerActiveSummary.textContent = `${activeCount} / ${total} stagiaire${total > 1 ? 's' : ''} actif${activeCount > 1 ? 's' : ''}`;
}

function updateSpinButtonState() {
  const activeCount = activeEntryIds.length;
  const availableCount = getAvailableCount();
  const hasActive = activeCount > 0;
  const exhausted = hasActive && availableCount === 0;

  spinButton.disabled = spinning || !hasActive || exhausted;

  if (!hasActive) {
    spinButtonLabel.textContent = 'Aucun stagiaire actif';
    spinHelp.textContent = 'Cochez au moins un stagiaire pour activer la roue.';
    return;
  }

  if (exhausted) {
    spinButtonLabel.textContent = 'Tous désignés !';
    spinHelp.textContent = 'Tous les stagiaires actifs ont été désignés. Réinitialisez pour recommencer.';
    return;
  }

  spinButtonLabel.textContent = 'Tourner la roue';
  spinHelp.textContent = '';
}

function setBannerWaiting() {
  resultBanner.className = 'w-full max-w-[480px] rounded-[14px] bg-gray-50 border border-dashed border-gray-200 px-6 py-4 text-center transition-all';
  if (activeEntryIds.length > 0) {
    resultName.textContent = 'Tournez la roue pour désigner un stagiaire';
  } else {
    resultName.textContent = 'Aucun stagiaire actif sur la roue';
  }
  resultName.className = 'text-sm text-gray-400';
}

function setBannerWinner(name) {
  resultBanner.className = 'w-full max-w-[480px] rounded-[14px] bg-violet-600 px-6 py-4 text-center transition-all';
  resultName.textContent = name;
  resultName.className = 'text-3xl font-bold text-white';
}

function drawWheel(angle) {
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  const entries = getActiveEntries();
  const pickedSet = new Set(PICKS.map((id) => Number(id)));
  const n = entries.length;
  if (n === 0) {
    return;
  }

  const segAngle = (2 * Math.PI) / n;

  entries.forEach((entry, i) => {
    const startA = angle + i * segAngle - Math.PI / 2;
    const endA = startA + segAngle;
    const isPicked = pickedSet.has(Number(entry.id));

    ctx.beginPath();
    ctx.moveTo(CX, CY);
    ctx.arc(CX, CY, R, startA, endA);
    ctx.closePath();

    ctx.fillStyle = isPicked ? '#e5e7eb' : PALETTE[i % PALETTE.length];
    ctx.fill();
    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 2;
    ctx.stroke();

    ctx.save();
    ctx.translate(CX, CY);
    ctx.rotate(startA + segAngle / 2);
    ctx.textAlign = 'right';

    const fontSize = Math.max(10, Math.min(16, Math.round(200 / n)));
    ctx.font = `bold ${fontSize}px "Varela Round", Arial, sans-serif`;
    ctx.fillStyle = isPicked ? '#9ca3af' : '#ffffff';

    const maxChars = Math.max(8, Math.round(40 / n));
    const label = entry.name.length > maxChars ? entry.name.slice(0, maxChars) + '…' : entry.name;
    ctx.fillText(label, R - 10, fontSize / 3);
    ctx.restore();
  });

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
  const delta = targetAngle - startAngle;
  const startTime = performance.now();

  function step(now) {
    const elapsed = now - startTime;
    const t = Math.min(elapsed / duration, 1);
    currentAngle = startAngle + delta * easeOut(t);
    drawWheel(currentAngle);
    if (t < 1) {
      requestAnimationFrame(step);
    } else {
      currentAngle = targetAngle;
      spinning = false;
      updateSpinButtonState();
      if (onDone) onDone();
    }
  }

  requestAnimationFrame(step);
}

function spinTo(winnerIndex) {
  const entries = getActiveEntries();
  const n = entries.length;
  if (n === 0 || winnerIndex < 0 || winnerIndex >= n) {
    spinning = false;
    updateSpinButtonState();
    drawWheel(currentAngle);
    return;
  }

  const segAngle = (2 * Math.PI) / n;
  const winnerCenter = winnerIndex * segAngle + segAngle / 2;
  const targetNorm = -winnerCenter - Math.PI / 2;
  const spins = 5 * 2 * Math.PI;
  const normalizedCurrent = currentAngle % (2 * Math.PI);
  let diff = (targetNorm - normalizedCurrent + 2 * Math.PI) % (2 * Math.PI);
  if (diff < 0.1) diff += 2 * Math.PI;

  const target = currentAngle + spins + diff;
  animateTo(target, 4000, null);
}

async function persistParticipants() {
  if (savingParticipants) {
    saveParticipantsQueued = true;
    return;
  }

  savingParticipants = true;
  participantsStatus.textContent = 'Mise à jour...';

  const selectedIds = participantCheckboxes
    .filter((input) => input.checked)
    .map((input) => Number(input.value));

  try {
    const res = await fetch(PARTICIPANTS_URL, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': CSRF_TOKEN,
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ active_entry_ids: selectedIds }),
    });

    if (!res.ok) {
      throw new Error('save_failed');
    }

    const data = await res.json();
    activeEntryIds = (data.active_entry_ids ?? []).map((id) => Number(id));
    participantCheckboxes.forEach((input) => {
      input.checked = activeEntryIds.includes(Number(input.value));
    });

    if (data.current_pick && data.current_pick.name) {
      setBannerWinner(data.current_pick.name);
    } else {
      setBannerWaiting();
    }

    updateParticipantRows();
    updateCounters();
    drawWheel(currentAngle);
    updateSpinButtonState();

    participantsStatus.textContent = 'Mis à jour';
    setTimeout(() => {
      if (participantsStatus.textContent === 'Mis à jour') {
        participantsStatus.textContent = '';
      }
    }, 1200);
  } catch (error) {
    participantsStatus.textContent = 'Erreur de mise à jour';
  } finally {
    savingParticipants = false;
    if (saveParticipantsQueued) {
      saveParticipantsQueued = false;
      persistParticipants();
    }
  }
}

// Dessin initial
drawWheel(currentAngle);
updateParticipantRows();
updateCounters();
updateSpinButtonState();

spinButton.addEventListener('click', async function () {
  if (spinning) return;
  if (getAvailableCount() === 0) {
    updateSpinButtonState();
    return;
  }

  spinning = true;
  updateSpinButtonState();

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
      updateSpinButtonState();
      return;
    }

    const data = await res.json();
    if (!PICKS.includes(data.winner.id)) {
      PICKS.push(data.winner.id);
    }

    const fallbackIndex = getActiveEntries().findIndex((entry) => Number(entry.id) === Number(data.winner.id));
    const winnerIndex = Number.isInteger(data.winner_index) ? data.winner_index : fallbackIndex;
    spinTo(winnerIndex);

    setTimeout(() => {
      setBannerWinner(data.winner.name);
      drawWheel(currentAngle);
      updateSpinButtonState();
    }, 2500);
  } catch (error) {
    alert('Erreur réseau. Réessayez.');
    spinning = false;
    updateSpinButtonState();
  }
});

participantCheckboxes.forEach((input) => {
  input.addEventListener('change', persistParticipants);
});

selectAllButton.addEventListener('click', () => {
  participantCheckboxes.forEach((input) => { input.checked = true; });
  persistParticipants();
});

clearAllButton.addEventListener('click', () => {
  participantCheckboxes.forEach((input) => { input.checked = false; });
  persistParticipants();
});
</script>

{{-- Alpine pour sidebar dashboard --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@endsection
