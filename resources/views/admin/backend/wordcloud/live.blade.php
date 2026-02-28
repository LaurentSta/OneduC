@extends('admin.admin_dashboard')
@section('admin')

<div class="w-full px-6 lg:px-8">
  <div class="bg-white rounded-[20px] shadow-soft p-6 my-6 border border-gray-100">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
      <div>
        <h1 class="text-[20px] font-varela text-bleuone">Live - {{ $wordCloud->title }}</h1>
        <p class="text-sm text-gray-600">Code: <span class="font-mono">{{ $wordCloud->access_code }}</span></p>
      </div>
      <a href="{{ route('admin.nuage.show', $wordCloud) }}" class="px-3 py-2 rounded-lg border border-gray-300 text-sm">Retour session</a>
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
    height: 420px;
    overflow: hidden;
  }
  .wc-word {
    display: inline-flex;
    flex: 0 0 auto;
    justify-content: center;
    text-align: left;
    white-space: nowrap;
    word-break: normal;
    overflow-wrap: normal;
    writing-mode: horizontal-tb !important;
    text-orientation: mixed !important;
    transform: none !important;
    min-height: auto !important;
  }
</style>

<script>
  const zone = document.getElementById('cloudZone');
  const endpoint = @json(route('admin.nuage.live.data', $wordCloud));
  let lastSignature = '';
  const palette = ['#0F766E', '#1D4ED8', '#B45309', '#BE123C', '#7C3AED', '#0E7490', '#15803D', '#A16207', '#9333EA', '#0369A1', '#C2410C', '#475569'];
  const fontFamilies = [
    'Georgia, "Times New Roman", Times, serif',
    '"Palatino Linotype", Palatino, "Book Antiqua", serif',
    '"Trebuchet MS", "Segoe UI", Tahoma, Arial, sans-serif',
    'Verdana, Geneva, Tahoma, sans-serif',
    'Arial, Helvetica, sans-serif'
  ];

  function sizeFromScore(score, maxScore, wordCount, globalScale = 1) {
    // Compression progressive globale: plus il y a de mots, plus toute la typographie baisse.
    const densityFactor = Math.min(0.65, Math.max(0, (wordCount - 12) * 0.012));
    const baseMin = 18;
    const baseMax = 92;
    const min = Math.max(10, Math.round(baseMin * (1 - densityFactor)));
    const max = Math.max(22, Math.round(baseMax * (1 - densityFactor)));

    const ratio = maxScore > 0 ? (score / maxScore) : 0;
    return Math.max(9, Math.round((min + ((max - min) * ratio)) * globalScale));
  }

  function hashWord(word) {
    let hash = 0;
    for (let i = 0; i < word.length; i += 1) {
      hash = ((hash << 5) - hash) + word.charCodeAt(i);
      hash |= 0;
    }
    return Math.abs(hash);
  }

  function colorFromWord(word) {
    return palette[hashWord(word) % palette.length];
  }

  function fontFromWord(word) {
    return fontFamilies[hashWord(word) % fontFamilies.length];
  }

  function renderWords(words, scale = 1) {
    const maxScore = Math.max(...words.map(w => Number(w.score)));
    zone.innerHTML = words.map((item) => {
      const fontSize = sizeFromScore(Number(item.score), maxScore, words.length, scale);
      const color = colorFromWord(item.word);
      const fontFamily = fontFromWord(item.word);
      return `<span class="wc-word inline-flex items-center px-2 py-1 rounded leading-none" style="font-size:${fontSize}px;color:${color};background:${color}1A;font-family:${fontFamily}">${item.word}</span>`;
    }).join('');
  }

  async function refreshCloud() {
    const response = await fetch(endpoint, { headers: { 'Accept': 'application/json' }});
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
