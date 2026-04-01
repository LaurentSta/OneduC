@extends('formateur.dashboard')

@section('formateur')
<div class="w-full px-6 lg:px-8">
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 border border-gray-100">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
      <div>
        <h1 class="text-[20px] font-varela text-bleuone">Nuage de mots - {{ $wordCloud->title }}</h1>
        <p class="text-sm text-gray-600 mt-1">{{ $wordCloud->question }}</p>
      </div>
      <a href="{{ url()->previous() }}" class="btn-oneduc-outline !px-3 !py-2 !text-sm">Retour</a>
    </div>

    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 mb-5">
      @if($wordCloud->group)
        <span class="rounded-full bg-gray-100 px-3 py-1">Groupe : {{ $wordCloud->group->name }}</span>
      @endif
      @if($wordCloud->module)
        <span class="rounded-full bg-gray-100 px-3 py-1">Module : {{ $wordCloud->module->module_title ?: $wordCloud->module->module_name }}</span>
      @endif
      <span class="rounded-full bg-orange-50 px-3 py-1 font-mono text-orangeone">Code : {{ $wordCloud->access_code }}</span>
      <a href="{{ $joinUrl }}" target="_blank" rel="noopener" class="btn-oneduc-blue !px-3 !py-1 !text-sm">Ouvrir le lien stagiaire</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-gray-50 min-h-[420px] p-5 wc-grid" id="cloudZone"></div>
  </div>
</div>

<style>
  .wc-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-content: flex-start;
    align-items: flex-start;
    min-height: 420px;
    overflow: hidden;
  }
  .wc-word {
    display: inline-flex;
    flex: 0 0 auto;
    white-space: nowrap;
  }
</style>

<script>
  const zone = document.getElementById('cloudZone');
  const endpoint = @json(route('formateur.wordclouds.live.data', $wordCloud));
  let lastSignature = '';
  const palette = ['#0F766E', '#1D4ED8', '#B45309', '#BE123C', '#7C3AED', '#0E7490', '#15803D', '#A16207'];

  function sizeFromScore(score, maxScore, wordCount, globalScale = 1) {
    const densityFactor = Math.min(0.65, Math.max(0, (wordCount - 12) * 0.012));
    const min = Math.max(10, Math.round(18 * (1 - densityFactor)));
    const max = Math.max(22, Math.round(92 * (1 - densityFactor)));
    const ratio = maxScore > 0 ? (score / maxScore) : 0;
    return Math.max(9, Math.round((min + ((max - min) * ratio)) * globalScale));
  }

  function colorFromWord(word) {
    let hash = 0;
    for (let i = 0; i < word.length; i += 1) {
      hash = ((hash << 5) - hash) + word.charCodeAt(i);
      hash |= 0;
    }
    return palette[Math.abs(hash) % palette.length];
  }

  function renderWords(words, scale = 1) {
    const maxScore = Math.max(...words.map(w => Number(w.score)));
    zone.innerHTML = words.map((item) => {
      const fontSize = sizeFromScore(Number(item.score), maxScore, words.length, scale);
      const color = colorFromWord(item.word);
      return `<span class="wc-word inline-flex items-center px-2 py-1 rounded leading-none" style="font-size:${fontSize}px;color:${color};background:${color}1A">${item.word}</span>`;
    }).join('');
  }

  async function refreshCloud() {
    const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' } });
    const data = await response.json();
    const words = data.words || [];
    const signature = JSON.stringify(words);

    if (signature === lastSignature) return;
    lastSignature = signature;

    if (!words.length) {
      zone.innerHTML = '<p class="text-gray-500 text-sm">En attente des premières réponses...</p>';
      return;
    }

    let scale = 1;
    renderWords(words, scale);

    let guard = 0;
    while (zone.scrollHeight > zone.clientHeight && scale > 0.45 && guard < 8) {
      scale *= 0.9;
      renderWords(words, scale);
      guard += 1;
    }
  }

  refreshCloud();
  setInterval(refreshCloud, 3000);
</script>
@endsection
